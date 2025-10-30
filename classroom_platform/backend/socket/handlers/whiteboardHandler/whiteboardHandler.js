import { WhiteboardFileModel } from "../../../models/index.js";
import { WHITEBOARD_ACTIONS as ACTIONS } from "./whiteboardActions.js";
import { MEDIA_BASE_URL } from "../../../config/envConfig.js";
import { WhiteboardFilesService } from "../../../services/whiteboardFilesService.js";
import fsExtra from "fs-extra";
import { ROOT_DIR } from "../../../config/rootDir.js";
import path from "path";
import { redisManager } from "../../../config/redisConfig.js";

export class WhiteboardHandler {
  constructor() {
    this.io = null;
    this.socket = null;
    // Store active rooms and their states in memory
    this.rooms = new Map();
  }

  registerHandlers(io, socket) {
    this.io = io;
    this.socket = socket;

    this.socket.on(ACTIONS.JOIN_ROOM, (data) => this.#handleJoinRoom(data));

    this.socket.on(ACTIONS.LEAVE_ROOM, (data) => this.handleLeaveRoom(data));

    this.socket.on(ACTIONS.DRAW, (data) => this.#handleDraw(data));

    this.socket.on(ACTIONS.CLEAR_CANVAS, (data) =>
      this.#handleClearCanvas(data),
    );

    this.socket.on(ACTIONS.UNDO, (data) => this.#handleUndo(data));

    this.socket.on(ACTIONS.REDO, (data) => this.#handleRedo(data));

    this.socket.on(ACTIONS.FILE_UPLOADED, (data) =>
      this.#handleFileUploaded(data),
    );

    this.socket.on(ACTIONS.DELETE_FILE, (data) => this.#handleDeleteFile(data));

    this.socket.on(ACTIONS.REMOVE_FILE, (data) => this.#handleRemoveFile(data));

    this.socket.on(ACTIONS.SET_CURRENT_FILE, (data) =>
      this.#handleSetCurrentFile(data),
    );

    this.socket.on(ACTIONS.REQUEST_SYNC, (data) =>
      this.#handleRequestSync(data),
    );
  }

  async #handleJoinRoom({ roomId, user }) {
    try {
      // Initialize room state if it doesn't exist
      if (!this.rooms.has(roomId)) {
        // Load existing files from database

        let existingFiles = await WhiteboardFileModel.getFilesByRoom(roomId);

        existingFiles = existingFiles.map((file) => {
          return {
            ...file,
            url: MEDIA_BASE_URL + file.url,
          };
        });

        this.rooms.set(roomId, {
          users: new Map(),
          paths: [],
          currentFile:
            existingFiles.length > 0
              ? {
                  id: existingFiles[0].id,
                  name: existingFiles[0].name,
                  url: existingFiles[0].url,
                  type: existingFiles[0].type,
                  width: existingFiles[0].width,
                  height: existingFiles[0].height,
                  uploadedBy: existingFiles[0].uploaded_by,
                }
              : null,
          uploadedFiles: existingFiles,
        });
      }

      const room = this.rooms.get(roomId);
      room.users.set(user.id, user);

      // Notify others that a user joined
      this.socket.to(roomId).emit(ACTIONS.USER_JOINED, {
        roomId,
        user,
      });

      // Send current state to the joining user
      this.socket.emit(ACTIONS.SYNC_STATE, {
        roomId,
        paths: room.paths,
        currentFile: room.currentFile,
        uploadedFiles: room.uploadedFiles,
        users: Array.from(room.users.values()),
      });

      console.log(`👤 User ${user.id} joined room ${roomId}`);
    } catch (error) {
      console.error("Error joining room:", error);
      this.socket.emit("error", { message: "Failed to join room" });
    }
  }

  async handleLeaveRoom({ roomId, userId }) {
    const room = this.rooms.get(roomId);

    if (!room) return;

    const user = room.users.get(userId);

    if (user) {
      room.users.delete(userId);

      this.socket.to(roomId).emit(ACTIONS.USER_LEFT, {
        userId,
        roomId,
      });

      console.log(`👋 User ${user.id} left room ${roomId}`);
    }

    // Clean up empty rooms
    if (room.users.size === 0) {
      this.rooms.delete(roomId);
      await fsExtra.emptyDirSync(path.join(ROOT_DIR, "/uploads/whiteboard"));
      await WhiteboardFileModel.destroyTable();
      await redisManager.getClient().flushAll();

      console.log(`🗑️ Cleaned up empty room ${roomId}`);
    }
  }

  async #handleDraw({ roomId, path }) {
    try {
      const room = this.rooms.get(roomId);
      if (!room) return;

      // Add path to room state
      room.paths.push(path);

      // Broadcast to all other users in the room
      this.io.to(roomId).emit(ACTIONS.DRAW, {
        roomId,
        path,
      });

      console.log(`✏️ Drawing from ${path.userId} in room ${roomId}`);
    } catch (error) {
      console.error("Error handling draw:", error);
    }
  }

  async #handleClearCanvas({ roomId, userId }) {
    try {
      const room = this.rooms.get(roomId);
      if (!room) return;

      // Clear paths in memory
      room.paths = room.paths.filter((p) => p.userId !== userId);
      room.currentFile = null;

      // Broadcast to all users in the room including sender
      this.io.to(roomId).emit(ACTIONS.CLEAR_CANVAS, { roomId, userId });

      console.log(`🗑️ Canvas cleared in room ${roomId} for ${userId}`);
    } catch (error) {
      console.error("Error clearing canvas:", error);
    }
  }

  async #handleDeleteFile({ roomId, fileId }) {
    try {
      await WhiteboardFilesService.deleteFile(fileId);
      await WhiteboardFileModel.deleteFileById(fileId);

      // Broadcast to all users in the room including sender
      this.io.to(roomId).emit(ACTIONS.DELETE_FILE, { fileId });

      console.log(`🗑️ File deleted ${fileId}`);
    } catch (error) {
      console.error("Error file deleting:", error);
    }
  }

  #handleRemoveFile({ roomId }) {
    // Broadcast to all users in the room including sender
    this.io.to(roomId).emit(ACTIONS.REMOVE_FILE);
  }

  #handleSetCurrentFile({ roomId, file }) {
    // Broadcast to all users in the room including sender
    this.io.to(roomId).emit(ACTIONS.SET_CURRENT_FILE, { file });
  }

  async #handleUndo({ roomId, userId }) {
    try {
      const room = this.rooms.get(roomId);
      if (!room) return;

      // Find and remove the last path from this user
      for (let i = room.paths.length - 1; i >= 0; i--) {
        if (room.paths[i].userId === userId) {
          room.paths.splice(i, 1);
          break;
        }
      }

      // Broadcast to all users in the room including sender
      this.io.to(roomId).emit(ACTIONS.UNDO, {
        userId,
        roomId,
      });

      console.log(`↩️ Undo from ${userId} in room ${roomId}`);
    } catch (error) {
      console.error("Error handling undo:", error);
    }
  }

  async #handleRedo({ roomId, userId, path }) {
    try {
      const room = this.rooms.get(roomId);

      // Ensure that the path being redone is for the correct user
      const existingPathIndex = room.paths.findIndex(
        (p) => p.userId === userId && p.path === path,
      );

      if (!room || existingPathIndex !== -1) return;

      room.paths.push(path);

      // Broadcast to all users in the room including sender
      this.io.to(roomId).emit(ACTIONS.REDO, {
        userId,
        roomId,
        path,
      });

      console.log(`↩️ Redo from ${userId} in room ${roomId}`);
    } catch (error) {
      console.error("Error handling redo:", error);
    }
  }

  #handleFileUploaded({ roomId, file }) {
    const room = this.rooms.get(roomId);
    if (!room) return;

    room.currentFile = file;

    // Broadcast to all users in the room including sender
    this.io.to(roomId).emit(ACTIONS.FILE_UPLOADED, {
      roomId,
      file,
    });

    console.log(`📁 File ${file.name} uploaded to room ${roomId}`);
  }

  #handleRequestSync({ roomId }) {
    const room = this.rooms.get(roomId);
    if (!room) return;

    this.socket.emit(ACTIONS.SYNC_STATE, {
      roomId,
      paths: room.paths,
      currentFile: room.currentFile,
      users: Array.from(room.users.values()),
    });

    console.log(`🔄 State sync requested for room ${roomId}`);
  }
}

const whiteboardHandler = new WhiteboardHandler();

export default whiteboardHandler;

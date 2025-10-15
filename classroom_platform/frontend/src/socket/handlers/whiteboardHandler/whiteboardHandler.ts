import { Socket } from "socket.io-client";
import { useWhiteboardStore } from "@/store/whiteboard/store";
import type {
  T_DrawEvent,
  T_UserJoinEvent,
  T_FileUploadEvent,
  T_SyncStateEvent,
  T_WhiteboardUser,
  T_DrawingPath,
  T_WhiteboardFile,
} from "@/store/whiteboard/types";
import { WHITEBOARD_ACTIONS as ACTIONS } from "./whiteboardActions";

export class WhiteboardHandler {
  private socket: Socket;

  constructor(socket: Socket) {
    this.socket = socket;
  }

  registerHandlers(): void {
    // Drawing events
    this.socket.on(ACTIONS.DRAW, (data) => this.handleDraw(data));
    this.socket.on(ACTIONS.CLEAR_CANVAS, (data) =>
      this.handleClearCanvas(data),
    );
    this.socket.on(ACTIONS.UNDO, (data) => this.handleUndo(data));
    this.socket.on(ACTIONS.REDO, (data) => this.handleRedo(data));

    // User events
    this.socket.on(ACTIONS.USER_JOINED, (data) => this.handleUserJoined(data));
    this.socket.on(ACTIONS.USER_LEFT, (data) => this.handleUserLeft(data));

    // File events
    this.socket.on(ACTIONS.FILE_UPLOADED, (data) =>
      this.handleFileUploaded(data),
    );
    this.socket.on(ACTIONS.DELETE_FILE, (data) => this.handleDeleteFile(data));
    this.socket.on(ACTIONS.REMOVE_FILE, () => this.handleRemoveFile());
    this.socket.on(ACTIONS.SET_CURRENT_FILE, (data) => this.handleSetCurrentFile(data));

    // Sync events
    this.socket.on(ACTIONS.SYNC_STATE, (data) => this.handleSyncState(data));

    console.log("✅ Whiteboard handlers registered");
  }

  // Handler methods
  private handleDraw({ path }: T_DrawEvent): void {
    console.log(path);
    const currentUserId = useWhiteboardStore.getState().currentUserId;

    // Don't add our own paths (already added locally)
    if (path.userId === currentUserId) return;

    useWhiteboardStore.getState().addPath(path);
    console.log(`✏️ Received drawing from user: ${path.userId}`);
  }

  private handleClearCanvas({ userId }: { userId: number }): void {
    useWhiteboardStore.getState().clearCanvas(userId);
    console.log("🗑️ Canvas cleared by another user");
  }

  private handleUndo({ userId }: { userId: number; roomId: string }): void {
    const currentUserId = useWhiteboardStore.getState().currentUserId;

    // Only handle undo from other users
    if (userId === currentUserId) return;

    const paths = useWhiteboardStore.getState().paths;

    // Find and remove the last path from this user
    for (let i = paths.length - 1; i >= 0; i--) {
      if (paths[i].userId === userId) {
        const newPaths = [...paths];
        newPaths.splice(i, 1);
        useWhiteboardStore.setState({ paths: newPaths });
        break;
      }
    }

    console.log(`↩️ Undo from user: ${userId}`);
  }

  private handleRedo({
    userId,
    path,
  }: {
    userId: number;
    roomId: string;
    path: T_DrawingPath;
  }): void {
    const currentUserId = useWhiteboardStore.getState().currentUserId;

    // Only handle redo from other users
    if (userId === currentUserId) return;

    const paths = useWhiteboardStore.getState().paths;
    const newPaths = [...paths, path];

    useWhiteboardStore.setState({ paths: newPaths });
    console.log(`🔁 Redo from user: ${userId}`);
  }

  private handleUserJoined({ user }: T_UserJoinEvent): void {
    useWhiteboardStore.getState().addUser(user);
    console.log(`👤 User joined: (${user.id})`);
  }

  private handleUserLeft({ userId }: { userId: number; roomId: string }): void {
    console.log(`👋 User first: ${userId}`);
    useWhiteboardStore.getState().removeUser(userId);
    console.log(`👋 User left: ${userId}`);
  }

  private handleFileUploaded({ file }: T_FileUploadEvent): void {
    useWhiteboardStore.getState().addUploadedFile(file);
    console.log(`📁 File uploaded: ${file.name}`);
  }

  private handleDeleteFile({
    fileId,
  }: {
    roomId: string;
    fileId: string;
  }): void {
    useWhiteboardStore.getState().deleteFile(fileId);
    console.log(`📁 File deleted: ${fileId}`);
  }

  private handleRemoveFile(): void {
    useWhiteboardStore.getState().removeFile();
    console.log("📁 File removed currentFile set to null");
  }

  private handleSetCurrentFile({
    file
  }: {
    file: T_WhiteboardFile;
  }): void {
    useWhiteboardStore.getState().setCurrentFile(file);
    console.log(`📁 File set current file`);
  }

  private handleSyncState({
    paths,
    currentFile,
    uploadedFiles,
    users,
  }: T_SyncStateEvent): void {
    console.log(users, "sync");
    useWhiteboardStore
      .getState()
      .syncState(paths, currentFile, uploadedFiles, users);
    console.log(
      `🔄 State synced: ${paths.length} paths, ${users.length} users`,
    );
  }

  // Emit methods
  joinRoom(roomId: string, user: T_WhiteboardUser): void {
    this.socket.emit(ACTIONS.JOIN_ROOM, { roomId, user });
    useWhiteboardStore.getState().setRoomId(roomId);
    useWhiteboardStore.getState().setCurrentUserId(user.id);
    console.log(`🚪 Joining whiteboard room: ${roomId}`);
  }

  leaveRoom(roomId: string, userId: number): void {
    this.socket.emit(ACTIONS.LEAVE_ROOM, { roomId, userId });
    useWhiteboardStore.getState().reset();
    console.log(`👋 Leaving whiteboard room: ${roomId}`);
  }

  emitDrawing(event: T_DrawEvent): void {
    this.socket.emit(ACTIONS.DRAW, event);
  }

  emitClearCanvas(roomId: string, userId: number): void {
    this.socket.emit(ACTIONS.CLEAR_CANVAS, { roomId, userId });
    useWhiteboardStore.getState().clearCanvas(userId);
  }

  emitUndo(roomId: string, userId: number): void {
    this.socket.emit(ACTIONS.UNDO, { roomId, userId });
    useWhiteboardStore.getState().undo();
  }

  emitRedo(roomId: string, userId: number): void {
    const undoneRedo = useWhiteboardStore.getState().undoneRedo;
    const pathToRedo = undoneRedo[undoneRedo.length - 1];

    this.socket.emit(ACTIONS.REDO, { roomId, userId, path: pathToRedo });
    useWhiteboardStore.getState().redo();
  }

  requestSync(roomId: string): void {
    this.socket.emit(ACTIONS.REQUEST_SYNC, { roomId });
    console.log(`🔄 Requesting state sync for room: ${roomId}`);
  }

  emitFileUploaded(roomId: string, data: any): void {
    this.socket.emit(ACTIONS.FILE_UPLOADED, {
      roomId,
      file: data.data,
    });
  }

  emitDeleteFile({ roomId, fileId }: { roomId: string; fileId: string }): void {
    this.socket.emit(ACTIONS.DELETE_FILE, {
      roomId,
      fileId,
    });
  }

  emitRemoveFile({ roomId }: { roomId: string }): void {
    this.socket.emit(ACTIONS.REMOVE_FILE, {
      roomId,
    });
  }

  emitCurrentFile({ roomId, file }: { roomId: string; file: T_WhiteboardFile }): void {
    this.socket.emit(ACTIONS.SET_CURRENT_FILE, {
      roomId,
      file,
    });
  }
}

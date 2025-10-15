import { create } from "zustand";
import type { T_DrawingPath, T_WhiteboardStore } from "./types";
import { DRAWING_TOOLS, WHITEBOARD_STORE_INITIAL_STATE } from "./constants";
import { devtools } from "zustand/middleware";
import { whiteboardHandler } from "@/socket/socketServer";

export const useWhiteboardStore = create<T_WhiteboardStore>()(
  devtools(
    (set) => ({
      // Initial state
      ...WHITEBOARD_STORE_INITIAL_STATE,

      // Room actions
      setRoomId: (roomId) => set({ roomId }, false, "setRoomId"),

      setCurrentUserId: (userId) =>
        set({ currentUserId: userId }, false, "setCurrentUserId"),

      addUser: (user) =>
        set(
          (state) => {
            const users = new Map(state.users);
            users.set(user.id, user);
            return { users };
          },
          false,
          "addUser",
        ),

      removeUser: (userId) =>
        set(
          (state) => {
            const users = new Map(state.users);
            users.delete(userId);
            return { users };
          },
          false,
          "removeUser",
        ),

      updateUser: (userId, updates) =>
        set(
          (state) => {
            const users = new Map(state.users);
            const user = users.get(userId);
            if (user) {
              users.set(userId, { ...user, ...updates });
            }
            return { users };
          },
          false,
          "updateUser",
        ),

      // Drawing actions
      startDrawing: (point) =>
        set(
          {
            currentPoint: [point],
            isDrawing: true,
            undoneRedo: [], // Clear redo stack when new drawing starts
          },
          false,
          "startDrawing",
        ),

      addPoint: (point) =>
        set(
          (state) => ({
            currentPoint: state.currentPoint
              ? [...state.currentPoint, point]
              : [point],
          }),
          false,
          "addPoint",
        ),

      stopDrawing: (userId) =>
        set(
          (state) => {
            if (!state.currentPoint || state.currentPoint.length === 0) {
              return { isDrawing: false, currentPoint: null };
            }

            const newPath: T_DrawingPath = {
              id: `${userId}-${Date.now()}`,
              tool: state.drawingTool,
              color: state.toolColor,
              width: state.toolWidth,
              points: state.currentPoint,
              userId,
              timestamp: Date.now(),
            };

            if (state.roomId) {
              whiteboardHandler.emitDrawing({
                roomId: state.roomId,
                path: newPath,
              });
            }

            return {
              paths: [...state.paths, newPath],
              currentPoint: null,
              isDrawing: false,
            };
          },
          false,
          "stopDrawing",
        ),

      addPath: (path) =>
        set(
          (state) => ({
            paths: [...state.paths, path],
          }),
          false,
          "addPath",
        ),

      clearCanvas: (userId) =>
        set(
          (state) => ({
            paths: state.paths.filter((p) => p.userId !== userId),
            undoneRedo: state.undoneRedo.filter((p) => p.userId !== userId),
            currentFile: null,
          }),
          false,
          "clearCanvas",
        ),

      undo: () =>
        set(
          (state) => {
            if (state.paths.length === 0) return state;

            const paths = [...state.paths];
            const lastPath = paths.pop();

            return {
              paths,
              undoneRedo: lastPath
                ? [...state.undoneRedo, lastPath]
                : state.undoneRedo,
            };
          },
          false,
          "undoDraw",
        ),

      redo: () =>
        set(
          (state) => {
            if (state.undoneRedo.length === 0) return state;

            const redoStack = [...state.undoneRedo];
            const pathToRedo = redoStack.pop();

            return {
              paths: pathToRedo ? [...state.paths, pathToRedo] : state.paths,
              undoneRedo: redoStack,
            };
          },
          false,
          "redoDraw",
        ),

      // Tool actions
      setDrawingTool: (tool) =>
        set({ drawingTool: tool }, false, "setDrawTool"),

      setToolColor: (color) => set({ toolColor: color }, false, "setToolColor"),

      setToolWidth: (width) => set({ toolWidth: width }, false, "setToolWidth"),

      // File actions
      setCurrentFile: (file) =>
        set({ currentFile: file }, false, "setCurrentFile"),

      addUploadedFile: (file) =>
        set(
          (state) => ({
            uploadedFiles: [...state.uploadedFiles, file],
            currentFile: file,
          }),
          false,
          "addUploadedFile",
        ),

      deleteFile: (fileId) =>
        set(
          (state) => {
            const uploadedFiles = state.uploadedFiles.filter(
              (file) => file.id != fileId,
            );

            return {
              currentFile: null,
              uploadedFiles,
            };
          },
          false,
          "deleteFile",
        ),

      removeFile: () =>
        set(
          () => ({
            currentFile: null,
          }),
          false,
          "removeFile",
        ),

      // Sync actions
      syncState: (paths, file, uploadedFiles, users) =>
        set(
          (state) => {
            const usersMap = new Map(state.users);
            users.forEach((user) => usersMap.set(user.id, user));

            return {
              paths,
              currentFile: file,
              uploadedFiles,
              users: usersMap,
            };
          },
          false,
          "syncState",
        ),

      reset: () =>
        set(
          {
            roomId: null,
            users: new Map(),
            currentUserId: null,
            paths: [],
            undoneRedo: [],
            currentPoint: null,
            isDrawing: false,
            drawingTool: DRAWING_TOOLS.MARKER,
            toolColor: "#000000",
            toolWidth: 3,
            currentFile: null,
            uploadedFiles: [],
          },
          false,
          "resetDrawingState",
        ),
    }),
    {
      name: "whiteboard-store",
    },
  ),
);

// FIXME: Maybe all room set id relative actions will be deleted

import type { DRAWING_TOOLS } from "./constants";

export type T_DrawingTool = typeof DRAWING_TOOLS[keyof typeof DRAWING_TOOLS];

export type T_Point = {
  x: number;
  y: number;
};

export type T_DrawingPath = {
  id: string;
  tool: T_DrawingTool;
  color: string;
  width: number;
  points: T_Point[];
  userId: number;
  timestamp: number;
};

export type T_WhiteboardFile = {
  id: string;
  name: string;
  url: string;
  type: "image" | "pdf";
  width: number;
  height: number;
  uploadedBy: string;
  uploadedAt: number;
};

export type T_WhiteboardUser = {
  id: number;
  color: string;
  currentTool: T_DrawingTool;
  isDrawing: boolean;
};

export type T_DrawEvent = {
  roomId: string;
  path: T_DrawingPath;
};

// FIXME: Maybe will be deleted
export type T_UserJoinEvent = {
  roomId: string;
  user: T_WhiteboardUser;
};

export type T_FileUploadEvent = {
  roomId: string;
  file: T_WhiteboardFile;
};

export type T_SyncStateEvent = {
  roomId: string;
  paths: T_DrawingPath[];
  currentFile: T_WhiteboardFile | null;
  uploadedFiles: T_WhiteboardFile[];
  users: T_WhiteboardUser[];
};

export type T_WhiteboardState = {
  // Room state
  roomId: string | null;
  users: Map<number, T_WhiteboardUser>;
  currentUserId: number | null;

  // Drawing state
  paths: T_DrawingPath[];
  undoneRedo: T_DrawingPath[];
  currentPoint: T_Point[] | null;
  isDrawing: boolean;

  // Tool state
  drawingTool: T_DrawingTool;
  toolColor: string;
  toolWidth: number;

  // File state
  currentFile: T_WhiteboardFile | null;
  uploadedFiles: T_WhiteboardFile[];
};

export type T_WhiteboardActions = {
  // Actions - Room
  setRoomId: (roomId: string) => void;
  setCurrentUserId: (userId: number) => void;
  addUser: (user: T_WhiteboardUser) => void;
  removeUser: (userId: number) => void;
  updateUser: (userId: number, updates: Partial<T_WhiteboardUser>) => void;

  // Actions - Drawing
  startDrawing: (point: T_Point) => void;
  addPoint: (point: T_Point) => void;
  stopDrawing: (userId: number) => void;
  addPath: (path: T_DrawingPath) => void;
  clearCanvas: (userId: number) => void;
  undo: () => void;
  redo: () => void;

  // Actions - Tools
  setDrawingTool: (tool: T_DrawingTool) => void;
  setToolColor: (color: string) => void;
  setToolWidth: (width: number) => void;

  // Actions - Files
  setCurrentFile: (file: T_WhiteboardFile | null) => void;
  addUploadedFile: (file: T_WhiteboardFile) => void;
  deleteFile: (fileid: T_WhiteboardFile["id"]) => void;
  removeFile: () => void;

  // Actions - Sync
  syncState: (
    paths: T_DrawingPath[],
    file: T_WhiteboardFile | null,
    uploadedFiles: T_WhiteboardFile[],
    users: T_WhiteboardUser[],
  ) => void;
  reset: () => void;
};

export type T_WhiteboardStore = T_WhiteboardState & T_WhiteboardActions;
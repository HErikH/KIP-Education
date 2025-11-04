import type { T_WhiteboardState } from "./types";

export const DRAWING_TOOLS = {
  MARKER: "MARKER",
  PENCIL: "PENCIL",
  ERASER: "ERASER",
} as const;

export const WHITEBOARD_STORE_INITIAL_STATE: T_WhiteboardState = {
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
} as const;
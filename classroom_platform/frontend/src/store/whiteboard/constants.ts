export const DRAWING_TOOLS = {
  MARKER: "MARKER",
  PENCIL: "PENCIL",
  ERASER: "ERASER",
} as const;

export const WHITEBOARD_STORE_INITIAL_STATE = {
  roomId: null,
  users: new Map(),
  currentUserId: null,
  paths: [],
  undoneRedo: [],
  currentPath: null,
  isDrawing: false,
  activeTool: DRAWING_TOOLS.MARKER,
  toolColor: "#000000",
  toolWidth: 3,
  currentFile: null,
  uploadedFiles: [],
};
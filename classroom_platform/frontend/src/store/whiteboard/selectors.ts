import { useWhiteboardStore } from "./store";

export const useCanvasPath = () => useWhiteboardStore((store) => store.paths);
export const useCanvasPoint = () => useWhiteboardStore((store) => store.currentPoint);
export const useIsDrawing = () => useWhiteboardStore((store) => store.isDrawing);
export const useDrawingTool= () => useWhiteboardStore((store) => store.drawingTool);
export const useToolColor = () => useWhiteboardStore((store) => store.toolColor);
export const useToolWidth = () => useWhiteboardStore((store) => store.toolWidth);
export const useCurrentFile = () => useWhiteboardStore((store) => store.currentFile);
export const useCurrentUserId = () => useWhiteboardStore((store) => store.currentUserId);
export const useUndoneRedo = () => useWhiteboardStore((store) => store.undoneRedo);
export const useWhiteboardUsers = () => useWhiteboardStore((store) => store.users);
export const useWhiteboardCurrentFile = () => useWhiteboardStore((store) => store.currentFile);
export const useWhiteboardUploadedFiles = () => useWhiteboardStore((store) => store.uploadedFiles);
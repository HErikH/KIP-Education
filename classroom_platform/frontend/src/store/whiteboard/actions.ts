import { useWhiteboardStore } from "./store";

export const useStartDrawing = () => useWhiteboardStore((store) => store.startDrawing);
export const useAddPoint = () => useWhiteboardStore((store) => store.addPoint);
export const useStopDrawing = () => useWhiteboardStore((store) => store.stopDrawing);
export const useSetDrawingTool = () => useWhiteboardStore((store) => store.setDrawingTool);
export const useSetToolColor = () => useWhiteboardStore((store) => store.setToolColor);
export const useSetToolWidth = () => useWhiteboardStore((store) => store.setToolWidth);
export const useRedoDraw = () => useWhiteboardStore((store) => store.redo);
export const useSetCurrentFile = () => useWhiteboardStore((store) => store.setCurrentFile);
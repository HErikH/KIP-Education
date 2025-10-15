import { drawPath, generateRandomColor } from "@/helpers/functions/whiteboard";
import { whiteboardHandler } from "@/socket/socketServer";
import { useRoomId, useUserId } from "@/store/rooms/selectors";
import {
  useCanvasPath,
  useCanvasPoint,
  useCurrentFile,
  useCurrentUserId,
  useDrawingTool,
  useToolColor,
  useToolWidth,
} from "@/store/whiteboard/selectors";
import type { T_WhiteboardUser } from "@/store/whiteboard/types";
import { useCallback, useEffect, useRef, useState } from "react";

type T_Props = {
  canvasRef: React.RefObject<HTMLCanvasElement | null>;
  contextRef: React.RefObject<CanvasRenderingContext2D | null>;
  width: number;
  height: number;
};

export function useRedrawAllPaths({
  canvasRef,
  contextRef,
  width,
  height,
}: T_Props) {
  const canvas = canvasRef.current;
  const ctx = contextRef.current;

  const paths = useCanvasPath();
  const drawingTool = useDrawingTool();
  const currentFile = useCurrentFile();
  const toolColor = useToolColor();
  const toolWidth = useToolWidth();
  const canvasPoint = useCanvasPoint();
  const currentUserId = useCurrentUserId();

  const redrawAllPaths = useCallback(() => {
    if (!canvas || !ctx) return;

    // Save current state
    ctx.save();

    // Clear canvas (or redraw background if exists)
    if (currentFile) {
      const img = new Image();
      img.crossOrigin = "anonymous";
      img.src = currentFile.url;
      if (img.complete) {
        const scale = Math.min(width / img.width, height / img.height);
        const x = (width - img.width * scale) / 2;
        const y = (height - img.height * scale) / 2;
        ctx.drawImage(img, x, y, img.width * scale, img.height * scale);
      }
    } else {
      ctx.clearRect(0, 0, width, height);
    }

    // Draw all paths
    paths.forEach((path) => drawPath(path, ctx));

    // Draw current path being drawn
    if (canvasPoint && canvasPoint.length > 0 && currentUserId) {
      drawPath(
        {
          id: "temp",
          tool: drawingTool,
          color: toolColor,
          width: toolWidth,
          points: canvasPoint,
          userId: currentUserId,
          timestamp: Date.now(),
        },
        ctx,
      );
    }

    ctx.restore();
  }, [
    paths,
    canvasPoint,
    currentFile,
    drawingTool,
    toolColor,
    toolWidth,
    currentUserId,
    width,
    height,
  ]);

  return { redrawAllPaths };
}

export function handleJoinWhiteboard() {
  const whiteboardHandlerRef = useRef<typeof whiteboardHandler | null>(whiteboardHandler);
  const [isConnected, setIsConnected] = useState(true);
  const drawingTool = useDrawingTool();
  const roomId = useRoomId();
  const userId = useUserId();

  useEffect(() => {
    // Connection event handlers
    console.log("✅ Connected to server");
    setIsConnected(true);

    if (roomId && userId && whiteboardHandlerRef.current) {
      // Join the room
      const user: T_WhiteboardUser = {
        id: userId,
        color: generateRandomColor(),
        currentTool: drawingTool,
        isDrawing: false,
      };

      whiteboardHandlerRef.current.joinRoom(roomId, user);
    }
  }, [roomId, userId]);

  return { isConnected };
}

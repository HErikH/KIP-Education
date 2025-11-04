import React, { useRef, useEffect } from "react";
import type { T_Point } from "@/store/whiteboard/types";
import "./style.scss";
import {
  useCanvasPath,
  useCurrentFile,
  useCurrentUserId,
  useIsDrawing,
} from "@/store/whiteboard/selectors";
import {
  useAddPoint,
  useStartDrawing,
  useStopDrawing,
} from "@/store/whiteboard/actions";
import { useRedrawAllPaths } from "@/hooks/useWhiteboard";
import clsx from "clsx";

interface WhiteboardCanvasProps {
  width?: number;
  height?: number;
}

export const WhiteboardCanvas: React.FC<WhiteboardCanvasProps> = ({
  width = window.innerWidth,
  height = window.innerHeight - 80,
}) => {
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const contextRef = useRef<CanvasRenderingContext2D | null>(null);

  const paths = useCanvasPath();
  const isDrawing = useIsDrawing();
  const currentFile = useCurrentFile();
  const currentUserId = useCurrentUserId();

  const startDrawing = useStartDrawing();
  const addPoint = useAddPoint();
  const stopDrawing = useStopDrawing();

  const { redrawAllPaths, drawingTool } = useRedrawAllPaths({
    canvasRef,
    contextRef,
    width,
    height,
  });

  // Initialize canvas
  useEffect(() => {
    const canvas = canvasRef.current;
    if (!canvas) return;

    canvas.width = width;
    canvas.height = height;

    const context = canvas.getContext("2d");
    if (!context) return;

    context.lineCap = "round";
    context.lineJoin = "round";
    contextRef.current = context;
  }, [width, height]);

  // Draw background file (image or PDF)
  useEffect(() => {
    if (!currentFile || !contextRef.current) return;

    const img = new Image();
    img.crossOrigin = "anonymous";
    img.onload = () => {
      const ctx = contextRef.current;
      if (!ctx) return;

      // Clear and draw the image
      ctx.clearRect(0, 0, width, height);

      // Calculate scaling to fit canvas while maintaining aspect ratio
      const scale = Math.min(width / img.width, height / img.height);
      const x = (width - img.width * scale) / 2;
      const y = (height - img.height * scale) / 2;

      ctx.drawImage(img, x, y, img.width * scale, img.height * scale);

      // Redraw all paths
      redrawAllPaths();
    };

    img.src = currentFile.url;
  }, [currentFile, width, height]);

  // Redraw all paths when they change
  useEffect(() => {
    redrawAllPaths();
  }, [paths, currentFile]);

  const getCanvasPoint = (e: React.MouseEvent<HTMLCanvasElement>): T_Point => {
    const canvas = canvasRef.current;
    if (!canvas) return { x: 0, y: 0 };

    const rect = canvas.getBoundingClientRect();
    return {
      x: e.clientX - rect.left,
      y: e.clientY - rect.top,
    };
  };

  const handleMouseDown = (e: React.MouseEvent<HTMLCanvasElement>) => {
    const point = getCanvasPoint(e);
    startDrawing(point);
  };

  const handleMouseMove = (e: React.MouseEvent<HTMLCanvasElement>) => {
    if (!isDrawing) return;

    const point = getCanvasPoint(e);
    addPoint(point);
    redrawAllPaths();
  };

  const handleMouseUp = () => {
    if (isDrawing && currentUserId) {
      stopDrawing(currentUserId);
    }
  };

  const handleMouseLeave = () => {
    if (isDrawing) {
      handleMouseUp();
    }
  };

  return (
    <div className="whiteboard-canvas">
      <canvas
        ref={canvasRef}
        className={clsx(
          "whiteboard-canvas__canvas",
          drawingTool &&
            `whiteboard-canvas__canvas--${drawingTool.toLowerCase()}`,
        )}
        onMouseDown={handleMouseDown}
        onMouseMove={handleMouseMove}
        onMouseUp={handleMouseUp}
        onMouseLeave={handleMouseLeave}
      />
    </div>
  );
};

import { DRAWING_TOOLS } from "@/store/whiteboard/constants";
import type { T_DrawingPath } from "@/store/whiteboard/types";
import { FILE_TYPES } from "../constants/whiteboard";

export function drawPath(path: T_DrawingPath, ctx: CanvasRenderingContext2D) {
  if (path.points.length < 2) return;

  ctx.save();
  ctx.strokeStyle = path.color;
  ctx.lineWidth = path.width;

  // Configure tool-specific rendering
  if (path.tool === DRAWING_TOOLS.MARKER) {
    ctx.lineCap = "round";
    ctx.lineJoin = "round";
    ctx.globalAlpha = 1;
  } else if (path.tool === DRAWING_TOOLS.PENCIL) {
    ctx.lineCap = "round";
    ctx.lineJoin = "round";
    ctx.globalAlpha = 0.6;
  } else if (path.tool === DRAWING_TOOLS.ERASER) {
    ctx.globalCompositeOperation = "destination-out";
    ctx.lineCap = "round";
    ctx.lineJoin = "round";
  }

  ctx.beginPath();
  ctx.moveTo(path.points[0].x, path.points[0].y);

  // Draw smooth curves using quadratic curves
  for (let i = 1; i < path.points.length - 1; i++) {
    const xc = (path.points[i].x + path.points[i + 1].x) / 2;
    const yc = (path.points[i].y + path.points[i + 1].y) / 2;
    ctx.quadraticCurveTo(path.points[i].x, path.points[i].y, xc, yc);
  }

  // Draw last segment
  if (path.points.length > 1) {
    const lastPoint = path.points[path.points.length - 1];
    ctx.lineTo(lastPoint.x, lastPoint.y);
  }

  ctx.stroke();
  ctx.restore();
}

export function generateRandomColor() {
  const colors = [
    "#FF6B6B",
    "#4ECDC4",
    "#45B7D1",
    "#FFA07A",
    "#98D8C8",
    "#F7DC6F",
    "#BB8FCE",
    "#85C1E2",
  ];

  return colors[Math.floor(Math.random() * colors.length)];
}

export function isValidFileType(file: File) {
  return file.type.startsWith("image/") || FILE_TYPES.includes(file.type);
}
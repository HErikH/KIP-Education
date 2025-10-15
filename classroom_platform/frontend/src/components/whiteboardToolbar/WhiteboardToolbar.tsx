import { useState } from "react";
import clsx from "clsx";
import {
  FaPencilAlt,
  FaEraser,
  FaPaintBrush,
  FaUndo,
  FaRedo,
  FaTrashAlt,
} from "react-icons/fa";
import "./style.scss";
import { DRAWING_TOOLS } from "@/store/whiteboard/constants";
import {
  useCanvasPath,
  useDrawingTool,
  useToolColor,
  useToolWidth,
  useUndoneRedo,
} from "@/store/whiteboard/selectors";
import {
  useSetDrawingTool,
  useSetToolColor,
  useSetToolWidth,
} from "@/store/whiteboard/actions";

type T_Props = {
  onUndo: () => void;
  onRedo: () => void;
  onClear: () => void;
};

export function WhiteboardToolbar({ onUndo, onRedo, onClear }: T_Props) {
  const paths = useCanvasPath();
  const toolColor = useToolColor();
  const toolWidth = useToolWidth();
  const drawingTool = useDrawingTool();
  const undoneRedo = useUndoneRedo();

  const setDrawingTool = useSetDrawingTool();
  const setToolColor = useSetToolColor();
  const setToolWidth = useSetToolWidth();

  const [showColorPicker, setShowColorPicker] = useState(false);

  const colors = [
    "#000000", // Black
    "#FF0000", // Red
    "#00FF00", // Green
    "#0000FF", // Blue
    "#FFFF00", // Yellow
    "#FF00FF", // Magenta
    "#00FFFF", // Cyan
    "#FFA500", // Orange
    "#800080", // Purple
    "#FFC0CB", // Pink
  ];

  const widths = [
    { value: 2, label: "Thin" },
    { value: 4, label: "Medium" },
    { value: 8, label: "Thick" },
    { value: 16, label: "Very Thick" },
  ];

  return (
    <div className="whiteboard-toolbar">
      {/* === Drawing Tools === */}
      <div className="whiteboard-toolbar__section">
        <button
          className={clsx("whiteboard-toolbar__tool", {
            "whiteboard-toolbar__tool--active":
              drawingTool === DRAWING_TOOLS.MARKER,
          })}
          onClick={() => setDrawingTool(DRAWING_TOOLS.MARKER)}
          title="Marker"
        >
          <FaPaintBrush />
          <span>Marker</span>
        </button>

        <button
          className={clsx("whiteboard-toolbar__tool", {
            "whiteboard-toolbar__tool--active":
              drawingTool === DRAWING_TOOLS.PENCIL,
          })}
          onClick={() => setDrawingTool(DRAWING_TOOLS.PENCIL)}
          title="Pencil"
        >
          <FaPencilAlt />
          <span>Pencil</span>
        </button>

        <button
          className={clsx("whiteboard-toolbar__tool", {
            "whiteboard-toolbar__tool--active":
              drawingTool === DRAWING_TOOLS.ERASER,
          })}
          onClick={() => setDrawingTool(DRAWING_TOOLS.ERASER)}
          title="Eraser"
        >
          <FaEraser />
          <span>Eraser</span>
        </button>
      </div>

      <div className="whiteboard-toolbar__divider" />

      {/* === Color and Width === */}
      <div className="whiteboard-toolbar__section">
        <div className="whiteboard-toolbar__color-picker">
          <button
            className="whiteboard-toolbar__color-button"
            style={{ backgroundColor: toolColor }}
            onClick={() => setShowColorPicker(!showColorPicker)}
            title="Color"
          />
          {showColorPicker && (
            <div className="whiteboard-toolbar__color-palette">
              {colors.map((color) => (
                <button
                  key={color}
                  className={clsx("whiteboard-toolbar__color-option", {
                    "whiteboard-toolbar__color-option--active":
                      toolColor === color,
                  })}
                  style={{ backgroundColor: color }}
                  onClick={() => {
                    setToolColor(color);
                    setShowColorPicker(false);
                  }}
                  title={color}
                />
              ))}
            </div>
          )}
        </div>

        <select
          className="whiteboard-toolbar__width-select"
          value={toolWidth}
          onChange={(e) => setToolWidth(Number(e.target.value))}
        >
          {widths.map(({ value, label }) => (
            <option key={value} value={value}>
              {label}
            </option>
          ))}
        </select>
      </div>

      <div className="whiteboard-toolbar__divider" />

      {/* === Actions === */}
      <div className="whiteboard-toolbar__section">
        <button
          className="whiteboard-toolbar__action"
          onClick={onUndo}
          disabled={paths.length === 0}
          title="Undo"
        >
          <FaUndo />
          <span>Undo</span>
        </button>

        <button
          className="whiteboard-toolbar__action"
          onClick={onRedo}
          disabled={undoneRedo.length === 0}
          title="Redo"
        >
          <FaRedo />
          <span>Redo</span>
        </button>

        <button
          className={clsx(
            "whiteboard-toolbar__action",
            "whiteboard-toolbar__action--danger",
          )}
          onClick={onClear}
          title="Clear Canvas"
        >
          <FaTrashAlt />
          <span>Clear</span>
        </button>
      </div>
    </div>
  );
}

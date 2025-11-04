import { useRef, useState } from "react";
import { whiteboardHandler } from "@/socket/socketServer";
import { WhiteboardToolbar } from "../whiteboardToolbar/WhiteboardToolbar";
import { WhiteboardCanvas } from "../whiteboardCanvas/WhiteboardCanvas";
// import { useWhiteboardUsers } from "@/store/whiteboard/selectors";
import { useRoomId, useUserId } from "@/store/rooms/selectors";
import { handleJoinWhiteboard } from "@/hooks/useWhiteboard";
import { FaPenToSquare } from "react-icons/fa6";
import { RiCloseLine } from "react-icons/ri";
import { FaFolderOpen } from "react-icons/fa";
import { FileUpload } from "../fileUpload/FileUpload";
import { Modal } from "react-responsive-modal";
import "./style.scss";
import { createPortal } from "react-dom";

export function Whiteboard() {
  const [isVisible, setIsVisible] = useState(false);
  const whiteboardHandlerRef = useRef<typeof whiteboardHandler | null>(
    whiteboardHandler,
  );
  const { isConnected } = handleJoinWhiteboard();
  const [showFileUpload, setShowFileUpload] = useState(false);
  const roomId = useRoomId();
  const userId = useUserId();
  // const users = useWhiteboardUsers();

  const handleUndo = () => {
    if (roomId && userId && whiteboardHandlerRef.current) {
      whiteboardHandlerRef.current.emitUndo(roomId, userId);
    }
  };

  const handleRedo = () => {
    if (roomId && userId && whiteboardHandlerRef.current) {
      whiteboardHandlerRef.current.emitRedo(roomId, userId);
    }
  };

  const handleClear = () => {
    if (
      window.confirm(
        "Are you sure you want to clear the canvas? This action cannot be undone.",
      ) &&
      whiteboardHandlerRef.current &&
      roomId &&
      userId
    ) {
      whiteboardHandlerRef.current.emitClearCanvas(roomId, userId);
    }
  };

  return (
    <>
      <Modal
        open={isVisible}
        styles={{
          modal: {
            width: "100%",
            maxWidth: "90%",
            borderRadius: "0.8rem",
            padding: "0",
            paddingTop: "5rem"
          },
        }}
        center
        onClose={() => setIsVisible(false)}
      >
        <div
          className="whiteboard"
        >
          <div className="whiteboard__header">
            {/* <h1 className="whiteboard__title">Whiteboard - Room: {roomId}</h1> */}
            <div className="whiteboard__connection">
              <div
                className={`whiteboard__status ${
                  isConnected
                    ? "whiteboard__status--connected"
                    : "whiteboard__status--disconnected"
                }`}
              />
              <span>{isConnected ? "Connected" : "Disconnected"}</span>
            </div>
            <div className="whiteboard__users">
              {/* <span className="whiteboard__users-count">
                👥 {users.size} {users.size === 1 ? "user" : "users"} online
              </span> */}
              <button
                className="whiteboard__upload-btn"
                onClick={() => setShowFileUpload(!showFileUpload)}
              >
                <FaFolderOpen /> {showFileUpload ? "Hide" : "Show"} Files
              </button>
            </div>
          </div>

          <WhiteboardToolbar
            onUndo={handleUndo}
            onRedo={handleRedo}
            onClear={handleClear}
          />

          <div className="whiteboard__content">
            <WhiteboardCanvas />

            <Modal
              open={showFileUpload}
              onClose={() => setShowFileUpload(false)}
              styles={{
                modal: {
                  borderRadius: "0.8rem",
                },
              }}
              center
            >
              <FileUpload />
            </Modal>
          </div>
        </div>
      </Modal>
      {createPortal(
        <button
          className="collapse-whiteboard controllers-aside__item"
          type="button"
          onClick={() => setIsVisible(!isVisible)}
        >
          {isVisible ? <RiCloseLine /> : <FaPenToSquare />}
        </button>,
        document.getElementById("controllers-aside")!,
      )}
    </>
  );
}

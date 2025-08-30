import { useRoom } from "@/hooks/useRooms";
import { clsx } from "clsx";
import "./style.scss";

function ToolBar() {
  const {
    isConnected,
    localMediaState,
    leaveRoom,
    enableWebcam,
    disableWebcam,
    enableScreenShare,
    disableScreenShare,
  } = useRoom();

  const handleLeaveRoom = () => {
    leaveRoom();
  };

  return (
    <div className="controls-toolbar">
      <button
        className="controls-toolbar__button controls-toolbar__button--leave"
        onClick={handleLeaveRoom}
        disabled={!isConnected}
      >
        Leave
      </button>

      <button
        className={clsx("controls-toolbar__button", {
          "controls-toolbar__button--active": localMediaState.localVideo,
          "controls-toolbar__button--inactive": !localMediaState.localVideo,
        })}
        onClick={localMediaState.localVideo ? disableWebcam : enableWebcam}
        disabled={!isConnected}
      >
        {localMediaState.localVideo ? "Disable" : "Enable"} Camera
      </button>

      <button
        className={clsx("controls-toolbar__button", {
          "controls-toolbar__button--active": localMediaState.localScreen,
          "controls-toolbar__button--inactive": !localMediaState.localScreen,
        })}
        onClick={
          localMediaState.localScreen ? disableScreenShare : enableScreenShare
        }
        disabled={!isConnected}
      >
        {localMediaState.localScreen ? "Stop" : "Share"} Screen
      </button>
    </div>
  );
}

export default ToolBar;

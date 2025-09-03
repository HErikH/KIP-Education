import { useRoom } from "@/hooks/useRooms";
import { clsx } from "clsx";
import { FaPhoneSlash } from "react-icons/fa";
import { BsCameraVideoFill, BsCameraVideoOffFill } from "react-icons/bs";
import { MdFitScreen, MdStopCircle } from "react-icons/md";
import Tooltip from "rc-tooltip";
import "rc-tooltip/assets/bootstrap_white.css";
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
      <Tooltip
        align={{
          offset: [0, -15],
        }}
        placement="top"
        showArrow={false}
        overlay={
          <span className="controls-toolbar__tooltip-overlay">Leave</span>
        }
      >
        <button
          className="controls-toolbar__button controls-toolbar__button--leave"
          onClick={handleLeaveRoom}
          disabled={!isConnected}
        >
          <FaPhoneSlash className="controls-toolbar__icon" />
        </button>
      </Tooltip>

      <Tooltip
        placement="top"
        showArrow={false}
        align={{
          offset: [0, -15],
        }}
        overlay={
          <p className="controls-toolbar__tooltip-overlay">
            {(localMediaState.localVideo ? "Disable" : "Enable") + " Camera"}
          </p>
        }
      >
        <button
          className={clsx("controls-toolbar__button", {
            "controls-toolbar__button--active": localMediaState.localVideo,
            "controls-toolbar__button--inactive": !localMediaState.localVideo,
          })}
          onClick={localMediaState.localVideo ? disableWebcam : enableWebcam}
          disabled={!isConnected}
        >
          {localMediaState.localVideo ? (
            <BsCameraVideoOffFill className="controls-toolbar__icon" />
          ) : (
            <BsCameraVideoFill className="controls-toolbar__icon" />
          )}
        </button>
      </Tooltip>

      <Tooltip
        placement="top"
        showArrow={false}
        align={{
          offset: [0, -15],
        }}
        overlay={
          <p className="controls-toolbar__tooltip-overlay">
            {(localMediaState.localScreen ? "Stop" : "Share") + " Screen"}
          </p>
        }
      >
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
          {localMediaState.localScreen ? (
            <MdStopCircle className="controls-toolbar__icon" />
          ) : (
            <MdFitScreen className="controls-toolbar__icon" />
          )}
        </button>
      </Tooltip>
    </div>
  );
}

export default ToolBar;

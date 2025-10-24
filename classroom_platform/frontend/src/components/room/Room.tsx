import { VideoFrame } from "../videoFrame/VideoFrame";
import { PeerVideo } from "../peerVideo/PeerVideo";
import { useEffect, useRef } from "react";
import { useRoom } from "@/hooks/useRooms";
import ToolBar from "../ui/toolBar/ToolBar";
import { clsx } from "clsx";
import { attachStream } from "@/helpers/functions/utils";
import { Whiteboard } from "../whiteboard/Whiteboard";
import { HiMiniHandRaised } from "react-icons/hi2";
import "./style.scss";

export function Room() {
  const {
    isConnected,
    roomId,
    userId,
    peers,
    localStream,
    localScreenStream,
    isRaised,
  } = useRoom();

  const localVideoRef = useRef<HTMLVideoElement>(null);
  const localScreenRef = useRef<HTMLVideoElement>(null);

  // Set local video stream
  useEffect(() => {
    attachStream(localVideoRef, localStream);
  }, [localStream]);

  // Set local screen share stream
  useEffect(() => {
    attachStream(localScreenRef, localScreenStream);
  }, [localScreenStream]);

  return (
    <div className="video-call">
      <div className="video-call__videos">
        <div className="video-call__video-wrapper">
          <VideoFrame
            className={clsx(
              "video-call__video media-video",
              !localStream && "blur",
            )}
            ref={localVideoRef}
            muted={true}
          />
          <p>You</p>

          {userId && isRaised(userId) && (
            <HiMiniHandRaised className="video-call__hand-raise" />
          )}
        </div>

        <div
          className={clsx(
            "video-call__screen-wrapper",
            !localScreenStream && "hide",
          )}
        >
          <VideoFrame
            className="video-call__screen media-video"
            ref={localScreenRef}
            muted={true}
          />
          <p>Screen Share</p>
        </div>

        {Array.from(peers.entries()).map(([peerId, peer]) => (
          <PeerVideo key={peerId} peerId={peerId} peer={peer} isRaised={isRaised} />
        ))}
      </div>

      {/* <div className="status">
        Status: {isConnected ? `Connected to ${roomId}` : "Disconnected"}
      </div> */}

      <ToolBar />

      <Whiteboard />
    </div>
  );
}

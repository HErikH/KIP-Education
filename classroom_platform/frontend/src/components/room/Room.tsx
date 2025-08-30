import { VideoFrame } from "../videoFrame/VideoFrame";
import { PeerVideo } from "../peerVideo/PeerVideo";
import { useEffect, useRef } from "react";
import { useRoom } from "@/hooks/useRooms";
import ToolBar from "../ui/toolBar/ToolBar";
import "./style.scss";

export function Room() {
  const { isConnected, roomId, peers, localStream } = useRoom();

  const localVideoRef = useRef<HTMLVideoElement>(null);

  // Set local video stream
  useEffect(() => {
    if (localVideoRef.current && localStream) {
      localVideoRef.current.srcObject = localStream;
    }
  }, [localStream]);

  return (
    <div className="video-call">
      <div className="video-call__videos">
        <div className="video-call__local-video">
          <VideoFrame ref={localVideoRef} />
          <p>You</p>
        </div>

        {Array.from(peers.entries()).map(([peerId, peer]) => (
          <PeerVideo key={peerId} peerId={peerId} peer={peer} />
        ))}
      </div>

      <div className="status">
        Status: {isConnected ? `Connected to ${roomId}` : "Disconnected"}
      </div>

      <ToolBar />
    </div>
  );
}

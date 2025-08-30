import { useEffect, useRef } from "react";
import { VideoFrame } from "../videoFrame/VideoFrame";
import type { T_PeerData } from "@/store/rooms/types";

type T_Props = {
  peerId: T_PeerData["id"];
  peer: T_PeerData;
};

export function PeerVideo({ peerId, peer }: T_Props) {
  const videoRef = useRef<HTMLVideoElement>(null);

  useEffect(() => {
    if (videoRef.current && peer.consumers.size > 0) {
      const videoConsumer = Array.from(peer.consumers.values()).find(
        (consumer) => consumer.kind === "video",
      );

      if (videoConsumer) {
        videoRef.current.srcObject = new MediaStream([videoConsumer.track]);
      }
    }
  }, [peer.consumers]);

  return (
    <div className="peer-video">
      <VideoFrame ref={videoRef} muted={false} />
      <p>{peerId}</p>
    </div>
  );
}

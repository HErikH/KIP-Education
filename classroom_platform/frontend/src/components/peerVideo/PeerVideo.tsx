import { useEffect, useRef, useState } from "react";
import { VideoFrame } from "../videoFrame/VideoFrame";
import type { T_PeerData } from "@/store/rooms/types";
import clsx from "clsx";
import { attachStream } from "@/helpers/functions/utils";

type T_Props = {
  peerId: T_PeerData["id"];
  peer: T_PeerData;
};

export function PeerVideo({ peerId, peer }: T_Props) {
  const videoRef = useRef<HTMLVideoElement>(null);
  const screenRef = useRef<HTMLVideoElement>(null);
  const audioRef = useRef<HTMLAudioElement>(null); // ⭐ SEPARATE AUDIO ELEMENT

  const [hasVideo, setHasVideo] = useState(false);
  const [hasScreen, setHasScreen] = useState(false);

  useEffect(() => {
    if (peer.consumers.size > 0) {
      const consumers = Array.from(peer.consumers.values());

      const videoConsumer = consumers.find(
        (c) => c.kind === "video" && !c.appData?.screen,
      );
      const screenConsumer = consumers.find(
        (c) => c.kind === "video" && c.appData?.screen,
      );
      const audioConsumer = consumers.find((c) => c.kind === "audio");

      console.log(videoConsumer, "video");
      console.log(screenConsumer, "screen");
      console.log(audioConsumer, "audio");

      console.log(consumers);

      attachStream(
        videoRef,
        videoConsumer ? new MediaStream([videoConsumer.track]) : null,
      );

      attachStream(
        screenRef,
        screenConsumer ? new MediaStream([screenConsumer.track]) : null,
      );

      attachStream(
        audioRef,
        audioConsumer ? new MediaStream([audioConsumer.track]) : null,
      );

      setHasVideo(Boolean(videoConsumer));
      setHasScreen(Boolean(screenConsumer));
    } else {
      // 🔧 CLEAR VIDEO ELEMENTS WHEN NO CONSUMERS
      console.log(`No consumers for peer ${peerId}, clearing video elements`);
      attachStream(videoRef, null);
      attachStream(screenRef, null);
      attachStream(audioRef, null);
      setHasVideo(false);
      setHasScreen(false);
    }
  }, [peer.consumers, peerId]);

  return (
    <div className="peer-video">
      <VideoFrame
        className={clsx("media-video", !hasVideo && "blur")}
        ref={videoRef}
        muted={true}
      />
      <VideoFrame
        className={clsx("media-video", !hasScreen && "hide")}
        ref={screenRef}
        muted={true}
      />

      <audio
        ref={audioRef}
        autoPlay
        muted={false}
        // volume={1.0}
        style={{ display: "none" }}
      />
      <p>{peerId}</p>
    </div>
  );
}

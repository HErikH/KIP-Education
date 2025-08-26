import { LOCAL_VIDEO } from "@/helpers/constants/webRTC";
import { forwardRef } from "react";

type T_Props = {
  clientId?: string;
}

export const VideoFrame = forwardRef<HTMLVideoElement, T_Props>(({ clientId }, ref) => {
  return (
    <video
      ref={ref}
      autoPlay
      playsInline
      controls={false}
      muted={clientId === LOCAL_VIDEO} 
      id="cam-video-stream"
    ></video>
  );
});

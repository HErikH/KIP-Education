import { forwardRef } from "react";

type T_Props = {
  muted?: boolean;
  clientId?: string;
}

export const VideoFrame = forwardRef<HTMLVideoElement, T_Props>(({ muted }, ref) => {
  return (
    <video
      ref={ref}
      autoPlay
      playsInline
      controls={false}
      muted={muted || true} 
      id="cam-video-stream"
    ></video>
  );
});

import { forwardRef } from "react";

type T_Props = {
  muted?: boolean;
  clientId?: string;
  className?: string;
}

export const VideoFrame = forwardRef<HTMLVideoElement, T_Props>(({ muted, className }, ref) => {
  return (
    <video
      ref={ref}
      autoPlay
      playsInline
      controls={false}
      muted={muted} 
      className={className || "cam-video-stream"}
    ></video>
  );
});

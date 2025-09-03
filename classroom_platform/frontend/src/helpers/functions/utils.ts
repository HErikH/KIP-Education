export function createMarkup(markup: string): { __html: string } {
  // ! Add sanitize library before using markup
  return { __html: markup };
}

export function attachStream(
  ref: React.RefObject<HTMLVideoElement | HTMLAudioElement | null>,
  stream?: MediaStream | null,
) {
  if (ref?.current) {
    ref.current.srcObject = stream ? stream : null;

    if (ref.current && typeof ref.current === typeof HTMLAudioElement) {
      ref.current.muted = false;
      ref.current.autoplay = true;
      ref.current.volume = 1.0;
      ref.current.play();
    }
  }
}

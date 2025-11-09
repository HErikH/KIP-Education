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

export async function fetchUrlAsFile(fileUrl: string, fileName: string) {
  const res = await fetch(import.meta.env.VITE_MEDIA_BASE_URL + fileUrl);
  const blob = await res.blob();

  const mime =
    blob.type || (fileUrl.endsWith(".pdf") ? "application/pdf" : "image/png");
    
  return new File([blob], fileName, { type: mime });
}
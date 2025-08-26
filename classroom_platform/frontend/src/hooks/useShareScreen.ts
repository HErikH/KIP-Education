import { useEffect, useState } from "react";

export function useShareScreen(
  constraints: MediaStreamConstraints = { video: true, audio: true },
) {
  // ! Give media devices object type
  const [stream, setStream] = useState<MediaStream | null>(null);
  // const devices = await navigator.mediaDevices.enumerateDevices();
  // audio: {
  // 		deviceId: 'default', // Используем устройство по умолчанию для аудио
  // 		echoCancellation: true // Включаем подавление эха
  // 	},
  // 	video: {
  // 		width: { min: 600, ideal: 1280 }, // Минимальная и идеальная ширина видео
  // 		height: { min: 400, ideal: 720 }, // Минимальная и идеальная высота видео
  // 		frameRate: { ideal: 30 } // Идеальная частота кадров
  // 	}

  useEffect(() => {
    (async () => {
      try {
        const streamData =
          await navigator.mediaDevices.getDisplayMedia(constraints);
        setStream(streamData);
        // console.log("Получен поток:", streamData);
      } catch (error) {
        console.error("Ошибка при получении медиа-потока:", error);
      }
    })();

    return () => {
      if (stream) {
        stream.getTracks().forEach((track) => track.stop());
        // socket.emit(ACTIONS.LEAVE);
      }
    };
  }, [constraints]);

  return stream;
}

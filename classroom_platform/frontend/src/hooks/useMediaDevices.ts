import { useCallback, useEffect, useState } from "react";

export function useMediaDevices(
  constraints: MediaStreamConstraints = { video: true, audio: true },
): [() => Promise<MediaStream | undefined>] {
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

  const getMediaDevices = useCallback(async () => {
    try {
      const streamData = await navigator.mediaDevices.getUserMedia(constraints);
      // setStream(streamData);
      // console.log("Получен поток:", streamData);
      return streamData;
    } catch (error) {
      console.error("Ошибка при получении медиа-потока:", error);
    }
  }, [constraints]);

  // return () => {
  //   if (stream) {
  //     stream.getTracks().forEach((track) => track.stop());
  //     // socket.emit(ACTIONS.LEAVE);
  //   }
  // };

  return [getMediaDevices];
}

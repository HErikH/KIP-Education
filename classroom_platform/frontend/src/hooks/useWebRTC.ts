import { useCallback, useEffect } from "react";
import { LOCAL_VIDEO } from "@/helpers/constants/webRTC";
import { roomsHandler } from "@/socket/socketServer";
import type { T_RoomInfo } from "@/helpers/types/rooms";
import { useMediaDevices } from "./useMediaDevices";
import {
  useSetClient,
  useSetLocalMediaStream,
  useSetPeerMediaElement,
} from "@/store/webRTC/actions";
import {
  useClients,
  useLocalMediaStream,
  usePeerMediaElements,
} from "@/store/webRTC/selectors";

export function useWebRTC(roomId: T_RoomInfo["roomId"] | null) {
  const [getMediaDevices] = useMediaDevices({
    audio: true,
    video: {
      width: 1280,
      height: 720,
    },
  });
  const localMediaStream = useLocalMediaStream();
  const peerMediaElements = usePeerMediaElements();

  const setLocalMediaStream = useSetLocalMediaStream();
  const setPeerMediaElement = useSetPeerMediaElement();

  const clients = useClients();
  const setClient = useSetClient();

  const addNewClient = useCallback((newClient: string, cb: () => void) => {
    // if (clients.includes(newClient)) return;

    setClient(newClient);
    cb();
  }, [clients]);

  useEffect(() => {
    let stream: MediaStream | undefined = undefined;

    (async () => {
      try {
        stream = await getMediaDevices();

        if (!stream || !roomId) return;

        setLocalMediaStream(stream);

        addNewClient(LOCAL_VIDEO, () => {
          const localVideoElement = peerMediaElements[LOCAL_VIDEO];

          if (localVideoElement && stream) {
            console.log(localVideoElement, "in");
            localVideoElement.volume = 0;
            localVideoElement.srcObject = stream;

            setPeerMediaElement(LOCAL_VIDEO, localVideoElement);
          }
        });

        // roomsHandler.joinRoom({ roomId });
        // roomsHandler.joinRoom({ roomId, userId, role });
      } catch (error) {
        console.error("Error getting userMedia:", error);
      }
    })();

    return () => {
      if (stream) {
        stream.getTracks().forEach((track) => track.stop());

        setLocalMediaStream(null);
      }

      roomsHandler.leaveRoom();
    };
  }, [roomId]);

  useEffect(() => {
    const videoEl = peerMediaElements[LOCAL_VIDEO];
    const stream = localMediaStream;

    if (videoEl && stream) {
      videoEl.volume = 0;
      videoEl.srcObject = stream;
    }
  }, [peerMediaElements[LOCAL_VIDEO], localMediaStream]);

  const provideMediaRef = useCallback(
    (id: string, node: HTMLVideoElement | null) => {
      setPeerMediaElement(id, node);
    },
    [],
  );

  return {
    clients,
    provideMediaRef,
  };
}

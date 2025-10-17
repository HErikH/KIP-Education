import { useRef } from "react";
import { roomsHandler } from "@/socket/socketServer";
import {
  useIsConnected,
  useLocalMediaState,
  useLocalStream,
  usePeers,
  useRoomId,
  useLocalScreenStream,
} from "@/store/rooms/selectors";

export const useRoom = () => {
  const roomsHandlerRef = useRef<typeof roomsHandler | null>(roomsHandler);
  const isConnected = useIsConnected();
  const roomId = useRoomId();
  const peers = usePeers();
  const localMediaState = useLocalMediaState();
  const localStream = useLocalStream();
  const localScreenStream = useLocalScreenStream();

  const checkRoomStatus = async ({
    roomId,
    userId,
  }: {
    roomId: string;
    userId: number;
  }) => {
    if (roomsHandlerRef.current) {
      await roomsHandlerRef.current.checkRoomStatus(roomId, userId);
    }
  };

  const joinRoom = async ({
    roomId,
    userId,
  }: {
    roomId: string;
    userId: number;
  }) => {
    if (roomsHandlerRef.current) {
      await roomsHandlerRef.current.checkRoomStatus(roomId, userId);
      // await roomsHandlerRef.current.joinRoom(roomId, userId);
    }
  };

  const leaveRoom = async () => {
    if (roomsHandlerRef.current) {
      await roomsHandlerRef.current.leaveRoom();
    }
  };

  const enableWebcam = async () => {
    if (roomsHandlerRef.current) {
      await roomsHandlerRef.current.enableWebcam();
    }
  };

  const disableWebcam = async () => {
    if (roomsHandlerRef.current) {
      await roomsHandlerRef.current.disableWebcam();
    }
  };

  const enableScreenShare = async () => {
    if (roomsHandlerRef.current) {
      await roomsHandlerRef.current.enableScreenShare();
    }
  };

  const disableScreenShare = async () => {
    if (roomsHandlerRef.current) {
      await roomsHandlerRef.current.disableScreenShare();
    }
  };

  return {
    // State
    isConnected,
    roomId,
    peers,
    localMediaState,
    localStream,
    localScreenStream,

    // Actions
    checkRoomStatus,
    joinRoom,
    leaveRoom,
    enableWebcam,
    disableWebcam,
    enableScreenShare,
    disableScreenShare,
  };
};

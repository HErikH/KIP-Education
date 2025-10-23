import { useRef } from "react";
import { roomsHandler } from "@/socket/socketServer";
import {
  useIsConnected,
  useLocalMediaState,
  useLocalStream,
  usePeers,
  useRoomId,
  useLocalScreenStream,
  useUserId,
  useUsername,
  useRaisedHands,
} from "@/store/rooms/selectors";

export const useRoom = () => {
  const roomsHandlerRef = useRef<typeof roomsHandler | null>(roomsHandler);
  const isConnected = useIsConnected();
  const roomId = useRoomId();
  const userId = useUserId();
  const username = useUsername();
  const raisedHands = useRaisedHands();
  const peers = usePeers();
  const localMediaState = useLocalMediaState();
  const localStream = useLocalStream();
  const localScreenStream = useLocalScreenStream();

  // Actions that get roomId, userId etc... before join room so there is why they get them as attributes
  const checkRoomStatus = async (
    {
      // roomId,
      // userId,
    }: {
      roomId: string;
      userId: number;
    },
  ) => {
    if (roomsHandlerRef.current) {
      // await roomsHandlerRef.current.checkRoomStatus(roomId, userId);
    }
  };

  const joinRoom = async ({
    roomId,
    userId,
    username,
  }: {
    roomId: string;
    userId: number;
    username: string;
  }) => {
    if (roomsHandlerRef.current) {
      await roomsHandlerRef.current.checkRoomStatus(roomId, userId, username);
      // await roomsHandlerRef.current.joinRoom(roomId, userId);
    }
  };

  // Actions that get roomId, userId etc... after join room so there is why they get them from store
  const leaveRoom = async () => {
    if (roomsHandlerRef.current) {
      await roomsHandlerRef.current.leaveRoom();
    }
  };

  const raiseHand = async () => {
    if (roomsHandlerRef.current && roomId && userId && username) {
      await roomsHandlerRef.current.raiseHand(roomId, userId, username);
    }
  };

  const lowerHand = async () => {
    if (roomsHandlerRef.current && roomId && userId && username) {
      await roomsHandlerRef.current.lowerHand(roomId, userId, username);
    }
  };

  const isRaised = (userId: number) => {
    return raisedHands?.find((user) => user.userId === userId && user.raised);
  }

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
    userId,
    roomId,
    peers,
    raisedHands,
    localMediaState,
    localStream,
    localScreenStream,

    // Actions
    checkRoomStatus,
    raiseHand,
    lowerHand,
    isRaised,
    joinRoom,
    leaveRoom,
    enableWebcam,
    disableWebcam,
    enableScreenShare,
    disableScreenShare,
  };
};

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
  useUserRole,
} from "@/store/rooms/selectors";
import type { T_RoomInfo } from "@/helpers/types/rooms";

// FIXME: Maybe will be refactored and deleted from export changing store states
//  and will only remain actions

export const useRoom = () => {
  const roomsHandlerRef = useRef<typeof roomsHandler | null>(roomsHandler);
  const isConnected = useIsConnected();
  const roomId = useRoomId();
  const userId = useUserId();
  const username = useUsername();
  const userRole = useUserRole();
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
    role,
  }: {
    roomId: T_RoomInfo["room_id"];
    userId: T_RoomInfo["user_id"];
    username: T_RoomInfo["username"];
    role: T_RoomInfo["role"];
  }) => {
    if (roomsHandlerRef.current) {
      await roomsHandlerRef.current.checkRoomStatus(
        roomId,
        userId,
        username,
        role,
      );
      // await roomsHandlerRef.current.joinRoom(roomId, userId);
    }
  };

  // Actions that get roomId, userId etc... after join room so there is why they get them from store
  const leaveRoom = async () => {
    if (roomsHandlerRef.current) {
      await roomsHandlerRef.current.leaveRoom();
    }
  };

  const raiseHand = async (
    user_id = userId,
    user_name = username,
  ) => {
    if (roomsHandlerRef.current && roomId && user_id && user_name) {
      await roomsHandlerRef.current.raiseHand(roomId, user_id, user_name);
    }
  };

  const lowerHand = async (
    user_id = userId,
    user_name = username,
  ) => {
    if (roomsHandlerRef.current && roomId && user_id && user_name) {
      await roomsHandlerRef.current.lowerHand(roomId, user_id, user_name);
    }
  };

  const isRaised = (userId: T_RoomInfo["user_id"]) => {
    return raisedHands?.find((user) => user.userId === userId && user.raised);
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
    userId,
    roomId,
    userRole,
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

import { useRoomsStore } from "./store";

export const useRoomsData = () => useRoomsStore((store) => store.rooms);
export const useLessonsData = () => useRoomsStore((store) => store.lessons);
export const useIsConnected = () => useRoomsStore((store) => store.isConnected);
export const useUsersInRoomCount = () => useRoomsStore((store) => store.usersInRoomCount);
export const useMaxUsersInRoom = () => useRoomsStore((store) => store.maxUsersInRoom);
export const useRoomId = () => useRoomsStore((store) => store.roomId);
export const useUserId = () => useRoomsStore((store) => store.userId);
export const useUsername = () => useRoomsStore((store) => store.username);
export const useUserRole = () => useRoomsStore((store) => store.role);
export const useRaisedHands = () => useRoomsStore((store) => store.raisedHands);
export const usePeers= () => useRoomsStore((store) => store.peers);
export const useLocalMediaState = () => useRoomsStore((store) => store.localMediaState);
export const useLocalStream = () => useRoomsStore((store) => store.localStream);
export const useLocalScreenStream = () => useRoomsStore((store) => store.localScreenStream);
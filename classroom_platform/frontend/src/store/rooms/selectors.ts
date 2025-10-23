import { useRoomsStore } from "./store";

export const useRoomsData = () => useRoomsStore((store) => store.rooms);
export const useIsConnected = () => useRoomsStore((store) => store.isConnected);
export const useRoomId = () => useRoomsStore((store) => store.roomId);
export const useUserId = () => useRoomsStore((store) => store.userId);
export const useUsername = () => useRoomsStore((store) => store.username);
export const useRaisedHands = () => useRoomsStore((store) => store.raisedHands);
export const usePeers= () => useRoomsStore((store) => store.peers);
export const useLocalMediaState = () => useRoomsStore((store) => store.localMediaState);
export const useLocalStream = () => useRoomsStore((store) => store.localStream);
export const useLocalScreenStream = () => useRoomsStore((store) => store.localScreenStream);
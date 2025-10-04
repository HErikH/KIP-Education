import { useRoomsStore } from "./store";

// export const useSetRooms = () => useRoomsStore((store) => store.setRooms);
// export const useSetRoomInfo = () => useRoomsStore((store) => store.setRoomInfo);
export const useFetchRooms = () => useRoomsStore((store) => store.fetchRooms);
export const useSetSelectedRoomId = () => useRoomsStore((store) => store.setRoomId);
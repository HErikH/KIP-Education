import { useRoomsStore } from "./store";

// export const useRoomId = () => useRoomsStore((store) => store.roomId);
// export const useRoomRole = () => useRoomsStore((store) => store.role);
export const useRoomsData = () => useRoomsStore((store) => store.rooms);
export const useSelectedRoomId = () => useRoomsStore((store) => store.selectedRoomId);
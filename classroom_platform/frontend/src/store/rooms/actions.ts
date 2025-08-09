import { useRoomsStore } from "./store";

export const useSetRoomInfo = () => useRoomsStore((store) => store.setRoomInfo);
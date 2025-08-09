import type { T_RoomInfo } from "@/helpers/types/rooms";

export type T_RoomsState = {
  rooms: T_RoomInfo[];
};

export type T_RoomsActions = {
  setRoomInfo: (info: T_RoomInfo) => void;
  clearRooms: () => void;
};

export type T_RoomsStore = T_RoomsState & T_RoomsActions;

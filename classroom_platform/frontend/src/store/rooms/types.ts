import type { T_RoomInfo } from "@/helpers/types/rooms";

export type T_RoomsState = {
  selectedRoomId: T_RoomInfo["roomId"] | null;
  rooms: T_RoomInfo[];
};

export type T_RoomsActions = {
  setRooms: (info: T_RoomInfo[]) => void;
  setRoomInfo: (info: T_RoomInfo) => void;
  clearRooms: () => void;
  setSelectedRoomId: (roomId: T_RoomsState["selectedRoomId"]) => void;
};

export type T_RoomsStore = T_RoomsState & T_RoomsActions;

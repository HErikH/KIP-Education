import { create } from "zustand";
import type { T_RoomsStore } from "./types";
import { ROOMS_STORE_INITIAL_STATE } from "./constants";
import { immer } from "zustand/middleware/immer";
import { devtools } from "zustand/middleware";

export const useRoomsStore = create(
  devtools(
    immer<T_RoomsStore>((set) => ({
      ...ROOMS_STORE_INITIAL_STATE,
      
      setRoomInfo: ({ roomId, userId, role }) =>
        set((state) => {
          state.rooms.push({ roomId, userId, role });
        }),

      setRooms: (rooms) =>
        set((state) => {
          state.rooms = rooms;
        }),

      clearRooms: () => set(() => ({ rooms: [] })),

      setSelectedRoomId: (roomId) =>
        set((state) => {
          state.selectedRoomId = roomId;
        }),
    })),
  ),
);

import { MOCK_DATA } from "@/data/mockData";
import type { T_RoomsState } from "./types";

export const ROOMS_STORE_INITIAL_STATE: T_RoomsState = {
  rooms: MOCK_DATA,
} as const;

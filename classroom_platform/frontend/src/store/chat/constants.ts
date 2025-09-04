import type { T_ChatState } from "./types";

export const CHAT_STORE_INITIAL_STATE: T_ChatState = {
  messages: [],
  isConnected: false,
  isLoading: false,
  error: null,
  currentRoomId: null,
  socketId: undefined
} as const;

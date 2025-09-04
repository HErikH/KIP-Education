import { create } from "zustand";
import type { T_ChatStore } from "./types";
import { CHAT_STORE_INITIAL_STATE } from "./constants";
import { devtools } from "zustand/middleware";

export const useChatStore = create<T_ChatStore>()(
  devtools(
    (set) => ({
      ...CHAT_STORE_INITIAL_STATE,

      setChatMessage: (message) =>
        set(
          (state) => ({
            messages: [...state.messages, message],
          }),
          false,
          "setChatMessage",
        ),

      setChatHistory: (data) =>
        set(
          () => ({
            messages: data.messages,
            currentRoomId: data.roomId,
            isLoading: false,
          }),
          false,
          "setChatHistory",
        ),

      setChatError: (data) =>
        set(
          () => ({
            error: data.error,
            isLoading: false,
          }),
          false,
          "setChatError",
        ),

      setIsConnected: (isConnected) =>
        set(
          () => ({
            isConnected,
          }),
          false,
          "setChatIsConnected",
        ),

      setIsLoading: (isLoading) =>
        set(
          () => ({
            isLoading,
          }),
          false,
          "setChatIsLoading",
        ),

      setSocketId: (socketId) =>
        set(
          () => ({
            socketId,
          }),
          false,
          "setChatSocketId",
        ),
    }),
    {
      name: "chat-store",
    },
  ),
);

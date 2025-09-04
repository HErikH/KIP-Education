import { useChatStore } from "./store";

export const useChatMessages = () => useChatStore((store) => store.messages);
export const useChatIsConnected = () => useChatStore((store) => store.isConnected);
export const useChatIsLoading = () => useChatStore((store) => store.isLoading);
export const useChatError = () => useChatStore((store) => store.error);
export const useChatSocketId = () => useChatStore((store) => store.socketId);
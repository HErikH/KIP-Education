import { useChatStore } from "./store";

export const setChatError = () => useChatStore((store) => store.setChatError);
export const setChatLoading = () => useChatStore((store) => store.setIsLoading);
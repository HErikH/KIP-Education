import { WEB_RTC_STORE_PEER_MEDIA_ELEMENTS } from "./constants";
import { useWebRTCStore } from "./store";

export const useLocalMediaStream = () => useWebRTCStore((store) => store.localMediaStream);
export const usePeerMediaElements = () => WEB_RTC_STORE_PEER_MEDIA_ELEMENTS.current;
export const usePeerConnections = () => useWebRTCStore((store) => store.peerConnections);
export const useClients = () => useWebRTCStore((store) => store.clients);
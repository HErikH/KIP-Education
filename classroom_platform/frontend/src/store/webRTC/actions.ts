import { useWebRTCStore } from "./store";

export const useSetLocalMediaStream = () => useWebRTCStore((store) => store.setLocalMediaStream);
export const useSetPeerMediaElement = () => useWebRTCStore((store) => store.setPeerMediaElement);
export const useRemovePeerMediaElement = () => useWebRTCStore((store) => store.removePeerMediaElement);
export const useSetPeerConnection = () => useWebRTCStore((store) => store.setPeerConnection);
export const useRemovePeerConnection = () => useWebRTCStore((store) => store.removePeerConnection);
export const useSetClient = () => useWebRTCStore((store) => store.setClient);
export const useRemoveClient = () => useWebRTCStore((store) => store.removeClient);
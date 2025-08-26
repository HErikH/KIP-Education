import type { MutableRefObject } from "react";

export type T_WebRTCState = {
  localMediaStream: MediaStream | null;
  peerMediaElements: MutableRefObject<Record<string, HTMLVideoElement | null>>;
  peerConnections: Record<string, RTCPeerConnection | null>
  clients: string[];
};

export type T_WebRTCActions = {
  setLocalMediaStream: (stream: T_WebRTCState['localMediaStream']) => void;
  setPeerMediaElement: (peerId: keyof T_WebRTCState['peerMediaElements']["current"], element: T_WebRTCState["peerMediaElements"]["current"][0]) => void;
  removePeerMediaElement: (peerId: string) => void;
  setPeerConnection: (peerId: keyof T_WebRTCState['peerConnections'], element: T_WebRTCState["peerConnections"][0]) => void;
  removePeerConnection: (peerId: keyof T_WebRTCState['peerConnections']) => void;
  setClient: (client: T_WebRTCState["clients"][0]) => void
  removeClient: (client: T_WebRTCState["clients"][0]) => void
};

export type T_WebRtcStore = T_WebRTCState & T_WebRTCActions;

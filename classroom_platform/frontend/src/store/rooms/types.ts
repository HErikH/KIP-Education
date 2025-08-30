import type { T_RoomInfo } from "@/helpers/types/rooms";
import type {
  Transport,
  Producer,
  Consumer,
  RtpCapabilities,
} from "mediasoup-client/types";

export type T_RoomsState = {
  rooms: T_RoomInfo[];
  isConnected: boolean;
  roomId: string | null;
  peers: Map<string, T_PeerData>;
  localMediaState: T_MediaState;
  sendTransport: Transport | null;
  recvTransport: Transport | null;
  producers: Map<string, Producer>;
  rtpCapabilities: RtpCapabilities | null;
  localStream: MediaStream | null;
  localScreenStream: MediaStream | null;
};

export type T_MediaState = {
  localVideo: boolean;
  localAudio: boolean;
  localScreen: boolean;
};

export type T_PeerData = {
  id: string;
  consumers: Map<string, Consumer>;
  videoElement?: HTMLVideoElement;
  audioElement?: HTMLAudioElement;
  screenElement?: HTMLVideoElement;
};

export type T_TransportOptions = {
  id: string;
  iceParameters: any;
  iceCandidates: any[];
  dtlsParameters: any;
};

export type T_RoomsActions = {
  setConnected: (connected: boolean) => void;
  setRoomId: (roomId: string | null) => void;
  addPeer: (peerId: string) => void;
  removePeer: (peerId: string) => void;
  updatePeerConsumer: (
    peerId: string,
    consumer: Consumer,
    kind: string,
  ) => void;
  updateLocalMediaState: (state: Partial<T_MediaState>) => void;
  setSendTransport: (transport: Transport | null) => void;
  setRecvTransport: (transport: Transport | null) => void;
  addProducer: (id: string, producer: Producer) => void;
  removeProducer: (id: string) => void;
  setRtpCapabilities: (capabilities: RtpCapabilities) => void;
  setLocalStream: (stream: MediaStream | null) => void;
  setLocalScreenStream: (stream: MediaStream | null) => void;
  reset: () => void;
};

export type T_RoomsStore = T_RoomsState & T_RoomsActions;

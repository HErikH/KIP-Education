import type { T_RaiseHand, T_RoomInfo } from "@/helpers/types/rooms";
import type {
  Transport,
  Producer,
  Consumer,
  RtpCapabilities,
  AppData,
} from "mediasoup-client/types";

export type T_RoomsState = {
  rooms: T_RoomInfo[];
  raisedHands: T_RaiseHand[];
  usersInRoomCount: number | null;
  maxUsersInRoom: number | null;
  isConnected: boolean;
  roomId: T_RoomInfo["room_id"] | null;
  userId: T_RoomInfo["user_id"] | null;
  username: T_RoomInfo["username"] | null;
  role: T_RoomInfo["role"] | null;
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
  fetchRooms: (userId: number) => void; 
  setConnected: (connected: boolean) => void;
  setUsersInRoomCount: (roomId: T_RoomsState["usersInRoomCount"] | null) => void;
  setMaxUsersInRoom: (maxUsersInRoom: T_RoomsState["maxUsersInRoom"] | null) => void;
  setRoomId: (roomId: T_RoomInfo["room_id"] | null) => void;
  setUserId: (userId: T_RoomInfo["user_id"] | null) => void;
  setUsername: (username: T_RoomInfo["username"] | null) => void;
  setUserRole: (role: T_RoomInfo["role"] | null) => void;
  setRaiseHand: (userId: number, raised: boolean) => void;
  setRaisedHands: (raisedHands: T_RaiseHand[]) => void;
  addPeer: (peerId: string) => void;
  removePeer: (peerId: string) => void;
  updatePeerConsumer: (
    peerId: string,
    consumer: Consumer,
    kind: string,
    appData?: AppData,
  ) => void;
  removePeerConsumer: (peerId: string, consumerId: string) => void
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

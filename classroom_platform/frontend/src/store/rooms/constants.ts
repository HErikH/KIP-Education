import { MOCK_DATA, MOCK_DATA_LESSONS } from "@/data/mockData";
import type { T_RoomsState } from "./types";

export const ROOMS_STORE_INITIAL_STATE: T_RoomsState = {
  rooms: MOCK_DATA,
  lessons: MOCK_DATA_LESSONS,
  raisedHands: [],
  usersInRoomCount: null,
  maxUsersInRoom: null,
  isConnected: false,
  roomId: null,
  userId: null,
  username: null,
  role: null,
  peers: new Map(),
  localMediaState: {
    localVideo: false,
    localAudio: false,
    localScreen: false,
  },
  sendTransport: null,
  recvTransport: null,
  producers: new Map(),
  rtpCapabilities: null,
  localStream: null,
  localScreenStream: null,
} as const;

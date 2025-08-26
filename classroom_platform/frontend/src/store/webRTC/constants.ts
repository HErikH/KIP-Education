import { LOCAL_VIDEO } from "@/helpers/constants/webRTC";
import type { T_WebRTCState } from "./types";
import { useRef } from "react";

export const WEB_RTC_STORE_PEER_MEDIA_ELEMENTS: T_WebRTCState["peerMediaElements"] = {
  current: {
    [LOCAL_VIDEO]: null,
  },
};

export const WEB_RTC_STORE_INITIAL_STATE: T_WebRTCState = {
  localMediaStream: null,
  peerMediaElements: {
    current: {
      [LOCAL_VIDEO]: null,
    },
  },
  peerConnections: {},
  clients: [],
} as const;

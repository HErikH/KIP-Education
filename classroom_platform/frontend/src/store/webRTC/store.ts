import { create } from "zustand";
import type { T_WebRtcStore } from "./types";
import { WEB_RTC_STORE_PEER_MEDIA_ELEMENTS, WEB_RTC_STORE_INITIAL_STATE } from "./constants";
import { immer } from "zustand/middleware/immer";
import { devtools } from "zustand/middleware";
import { LOCAL_VIDEO } from "@/helpers/constants/webRTC";

export const useWebRTCStore = create(
  devtools(
    immer<T_WebRtcStore>((set, get) => ({
      ...WEB_RTC_STORE_INITIAL_STATE,

      setLocalMediaStream: (stream) =>
        set((state) => {
          state.localMediaStream = stream;
        }),

      setPeerMediaElement: (peerId, element) => {
        WEB_RTC_STORE_PEER_MEDIA_ELEMENTS.current[peerId] = element;
      },

      removePeerMediaElement: (peerId) => {
        delete WEB_RTC_STORE_PEER_MEDIA_ELEMENTS.current[peerId];
      },

      setPeerConnection: (peerId, connection) =>
        set((state) => {
          state.peerConnections[peerId] = connection;
        }),

      removePeerConnection: (peerId) =>
        set((state) => {
          delete state.peerConnections[peerId];
        }),

      setClient: (client) =>
        set((state) => {
          state.clients.push(client);
        }),

      removeClient: (peerId) =>
        set((state) => {
          state.clients.filter((client) => client !== peerId);
        }),
    })),
  ),
);

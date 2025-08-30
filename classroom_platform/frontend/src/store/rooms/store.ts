import { create } from "zustand";
import type { T_RoomsStore } from "./types";
import { ROOMS_STORE_INITIAL_STATE } from "./constants";
import { devtools } from "zustand/middleware";

export const useRoomsStore = create<T_RoomsStore>()(
  devtools(
    (set) => ({
      ...ROOMS_STORE_INITIAL_STATE,

      setConnected: (connected) =>
        set({ isConnected: connected }, false, "setConnected"),

      setRoomId: (roomId) => set({ roomId }, false, "setRoomId"),

      addPeer: (peerId) =>
        set(
          (state) => {
            const newPeers = new Map(state.peers);
            if (!newPeers.has(peerId)) {
              newPeers.set(peerId, {
                id: peerId,
                consumers: new Map(),
              });
            }
            return { peers: newPeers };
          },
          false,
          "addPeer",
        ),

      removePeer: (peerId) =>
        set(
          (state) => {
            const newPeers = new Map(state.peers);
            const peer = newPeers.get(peerId);
            if (peer) {
              // Close all consumers
              peer.consumers.forEach((consumer) => consumer.close());
              newPeers.delete(peerId);
            }
            return { peers: newPeers };
          },
          false,
          "removePeer",
        ),

      updatePeerConsumer: (peerId, consumer) =>
        set(
          (state) => {
            const newPeers = new Map(state.peers);
            const peer = newPeers.get(peerId);
            if (peer) {
              const newConsumers = new Map(peer.consumers);
              newConsumers.set(consumer.id, consumer);
              newPeers.set(peerId, { ...peer, consumers: newConsumers });
            }
            return { peers: newPeers };
          },
          false,
          "updatePeerConsumer",
        ),

      updateLocalMediaState: (state) =>
        set(
          (prevState) => ({
            localMediaState: { ...prevState.localMediaState, ...state },
          }),
          false,
          "updateLocalMediaState",
        ),

      setSendTransport: (transport) =>
        set({ sendTransport: transport }, false, "setSendTransport"),

      setRecvTransport: (transport) =>
        set({ recvTransport: transport }, false, "setRecvTransport"),

      addProducer: (id, producer) =>
        set(
          (state) => {
            const newProducers = new Map(state.producers);
            newProducers.set(id, producer);
            return { producers: newProducers };
          },
          false,
          "addProducer",
        ),

      removeProducer: (id) =>
        set(
          (state) => {
            const newProducers = new Map(state.producers);
            const producer = newProducers.get(id);
            if (producer) {
              producer.close();
              newProducers.delete(id);
            }
            return { producers: newProducers };
          },
          false,
          "removeProducer",
        ),

      setRtpCapabilities: (capabilities) =>
        set({ rtpCapabilities: capabilities }, false, "setRtpCapabilities"),

      setLocalStream: (stream) =>
        set({ localStream: stream }, false, "setLocalStream"),

      setLocalScreenStream: (stream) =>
        set({ localScreenStream: stream }, false, "setLocalScreenStream"),

      reset: () =>
        set(
          (state) => {
            // Clean up existing resources
            state.producers.forEach((producer) => producer.close());
            state.peers.forEach((peer) => {
              peer.consumers.forEach((consumer) => consumer.close());
            });
            state.sendTransport?.close();
            state.recvTransport?.close();

            if (state.localStream) {
              state.localStream.getTracks().forEach((track) => track.stop());
            }
            if (state.localScreenStream) {
              state.localScreenStream
                .getTracks()
                .forEach((track) => track.stop());
            }

            return { ...ROOMS_STORE_INITIAL_STATE };
          },
          false,
          "reset",
        ),
    }),
    {
      name: "rooms-store",
    },
  ),
);

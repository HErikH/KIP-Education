import { create } from "zustand";
import type { T_PeerData, T_RoomsStore } from "./types";
import { ROOMS_STORE_INITIAL_STATE } from "./constants";
import { devtools } from "zustand/middleware";
import axios, { type AxiosResponse } from "axios";
import type { T_RoomInfo } from "@/helpers/types/rooms";
import type { T_LessonsGroup } from "@/helpers/types/lessons";

export const useRoomsStore = create<T_RoomsStore>()(
  devtools(
    (set) => ({
      ...ROOMS_STORE_INITIAL_STATE,

      fetchRooms: async (userId) => {
        try {
          const result: AxiosResponse<T_RoomInfo[]> = await axios.get(
            `${import.meta.env.VITE_BACK_END_PORT}/rooms/get/${userId}`,
          );

          set({ rooms: result.data }, false, "fetchRooms");
        } catch (error) {
          console.error("Error fetching rooms:", error);
        }
      },

      fetchLessons: async () => {
        try {
          const result: AxiosResponse<T_LessonsGroup[]> = await axios.get(
            `${import.meta.env.VITE_BACK_END_PORT}/lessons/get/`,
          );

          set({ lessons: result.data }, false, "fetchLessons");
        } catch (error) {
          console.error("Error fetching lessons:", error);
        }
      },

      setConnected: (connected) =>
        set({ isConnected: connected }, false, "setConnected"),

      setUsersInRoomCount: (usersInRoomCount) =>
        set({ usersInRoomCount }, false, "setUsersInRoomCount"),

      setMaxUsersInRoom: (maxUsersInRoom) =>
        set({ maxUsersInRoom }, false, "setMaxUsersInRoom"),

      setRoomId: (roomId) => set({ roomId }, false, "setRoomId"),

      setUserId: (userId) => set({ userId }, false, "setUserId"),

      setUsername: (username) => set({ username }, false, "setUsername"),

      setUserRole: (role) => set({ role }, false, "setUserRole"),

      setRaiseHand: (userId, raised) =>
        set(
          (state) => ({
            raisedHands: state.raisedHands.map((item) => {
              return item.userId === userId ? { ...item, raised } : item;
            }),
          }),
          false,
          "setRaiseHand",
        ),

      setRaisedHands: (raisedHands) =>
        set(
          (state) => {
            return { raisedHands: [...state.raisedHands, ...raisedHands] };
          },
          false,
          "setRaisedHands",
        ),

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

      updatePeerConsumer: (peerId, consumer, _, appData) =>
        set(
          (state) => {
            const newPeers = new Map(state.peers);
            const peer = newPeers.get(peerId);

            if (peer) {
              const newConsumers: T_PeerData["consumers"] = new Map(
                peer.consumers,
              );

              if (appData) {
                consumer.appData = { ...consumer.appData, ...appData };
              }

              newConsumers.set(consumer.id, consumer);
              newPeers.set(peerId, { ...peer, consumers: newConsumers });
            }

            return { peers: newPeers };
          },
          false,
          "updatePeerConsumer",
        ),

      removePeerConsumer: (peerId, consumerId) =>
        set(
          (state) => {
            const newPeers = new Map(state.peers);
            const peer = newPeers.get(peerId);

            if (peer) {
              const newConsumers = new Map(peer.consumers);

              newConsumers.delete(consumerId);
              newPeers.set(peerId, { ...peer, consumers: newConsumers });
            }

            return { peers: newPeers };
          },
          false,
          "removePeerConsumer",
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

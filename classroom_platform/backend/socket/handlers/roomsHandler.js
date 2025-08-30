import { ACTIONS } from "../socketActions.js";
import { version, validate } from "uuid";
import { MediaSoupManager } from "../../mediasoup/mediaSoupManager.js";

const mediaSoupManager = new MediaSoupManager();
export class RoomsHandler {
  constructor(io, socket) {
    this.io = io;
    this.socket = socket;
    this.roomId = null;
    this.peerId = socket.id;
  }

  static async initMediaSoup() {
    await mediaSoupManager.init();
  }

  registerHandlers() {
    this.socket.on(ACTIONS.JOIN_ROOM, (data) => this.#handleJoinRoom(data));
    this.socket.on(ACTIONS.LEAVE_ROOM, (data) => this.#handleLeaveRoom(data));
    this.socket.on(ACTIONS.DISCONNECTING, (data) =>
      this.#handleLeaveRoom(data),
    );

    // MediaSoup handlers
    this.socket.on(ACTIONS.GET_RTP_CAPABILITIES, (data, callback) =>
      this.#handleGetRtpCapabilities(data, callback),
    );
    this.socket.on(ACTIONS.CREATE_TRANSPORT, (data, callback) =>
      this.#handleCreateTransport(data, callback),
    );
    this.socket.on(ACTIONS.CONNECT_TRANSPORT, (data, callback) =>
      this.#handleConnectTransport(data, callback),
    );
    this.socket.on(ACTIONS.PRODUCE, (data, callback) =>
      this.#handleProduce(data, callback),
    );
    this.socket.on(ACTIONS.CONSUME, (data, callback) =>
      this.#handleConsume(data, callback),
    );
    this.socket.on(ACTIONS.CONSUMER_RESUME, (data, callback) =>
      this.#handleConsumerResume(data, callback),
    );
  }

  // * Listeners
  async #handleJoinRoom({ roomId }) {
    try {
      console.log(`👤 Peer ${this.peerId} joining room ${roomId}`);

      this.roomId = roomId;
      this.socket.join(roomId);

      // Create or get room
      const room = mediaSoupManager.createRoom(roomId);

      // Add peer to room
      const peer = room.addPeer(this.peerId);

      // Get existing peers in the room
      const existingPeers = room
        .getPeerIds()
        .filter((id) => id !== this.peerId);

      // Notify existing peers about new peer
      await this.socket.to(roomId).emit(ACTIONS.ADD_PEER, {
        peerId: this.peerId,
      });

      // Send existing peers to new peer
      await this.socket.emit(ACTIONS.ADD_PEER, {
        peers: existingPeers,
      });

      // 🔧 NEW: Notify new peer about ALL existing producers
      // 🔧 DELAY sending existing producers to allow transport setup
      if (existingPeers.length > 0) {
        setTimeout(() => {
          console.log(
            `⏰ Sending ${existingPeers.length} existing producers to new peer ${this.peerId}`,
          );

          for (const existingPeerId of existingPeers) {
            const existingPeer = room.getPeer(existingPeerId);

            // Check if peer still exists (in case they left)
            if (existingPeer) {
              for (const producer of existingPeer.producers.values()) {
                console.log(
                  `📡 Notifying peer ${this.peerId} about producer ${producer.id} from ${existingPeerId}`,
                );

                this.socket.emit(ACTIONS.NEW_PRODUCER, {
                  peerId: existingPeerId,
                  producerId: producer.id,
                  kind: producer.kind,
                });
              }
            }
          }
        }, 1000); // 1 second delay - adjust as needed
      }

      console.log(`✅ Peer ${this.peerId} joined room ${roomId}`);
    } catch (error) {
      console.error("❌ Error joining room:", error);
      this.socket.emit("error", { message: "Failed to join room" });
    }
  }

  #handleLeaveRoom() {
    if (!this.roomId) return;

    try {
      console.log(`👋 Peer ${this.peerId} leaving room ${this.roomId}`);

      const room = mediaSoupManager.getRoom(this.roomId);
      if (room) {
        // Remove peer from room
        room.removePeer(this.peerId);

        // Notify other peers
        this.socket.to(this.roomId).emit(ACTIONS.REMOVE_PEER, {
          peerId: this.peerId,
        });

        // Clean up empty room
        if (room.isEmpty()) {
          mediaSoupManager.deleteRoom(this.roomId);
        }
      }

      this.socket.leave(this.roomId);
      this.roomId = null;
    } catch (error) {
      console.error("❌ Error leaving room:", error);
    }
  }

  #handleGetRtpCapabilities(data, callback) {
    try {
      const rtpCapabilities = mediaSoupManager.router.rtpCapabilities;

      callback({ rtpCapabilities });
    } catch (error) {
      console.error("❌ Error getting RTP capabilities:", error);
      callback({ error: error.message });
    }
  }

  async #handleCreateTransport({ direction }, callback) {
    try {
      const room = mediaSoupManager.getRoom(this.roomId);

      if (!room) {
        throw new Error("Room not found");
      }

      const peer = room.getPeer(this.peerId);
      if (!peer) {
        throw new Error("Peer not found");
      }

      const transport = await peer.createWebRtcTransport();

      callback({
        params: {
          id: transport.id,
          iceParameters: transport.iceParameters,
          iceCandidates: transport.iceCandidates,
          dtlsParameters: transport.dtlsParameters,
        },
      });

      console.log(`🚛 Created ${direction} transport for peer ${this.peerId}`);
    } catch (error) {
      console.error("❌ Error creating transport:", error);
      callback({ error: error.message });
    }
  }

  async #handleConnectTransport({ transportId, dtlsParameters }, callback) {
    try {
      const room = mediaSoupManager.getRoom(this.roomId);
      const peer = room.getPeer(this.peerId);

      await peer.connectTransport(transportId, dtlsParameters);
      callback({});

      console.log(
        `🔗 Connected transport ${transportId} for peer ${this.peerId}`,
      );
    } catch (error) {
      console.error("❌ Error connecting transport:", error);
      callback({ error: error.message });
    }
  }

  async #handleProduce({ transportId, kind, rtpParameters }, callback) {
    try {
      const room = mediaSoupManager.getRoom(this.roomId);
      const peer = room.getPeer(this.peerId);

      const producer = await peer.produce(transportId, rtpParameters, kind);
      callback({ id: producer.id });

      // Notify other peers about new producer
      this.socket.to(this.roomId).emit(ACTIONS.NEW_PRODUCER, {
        peerId: this.peerId,
        producerId: producer.id,
        kind,
      });

      console.log(`📡 Created producer ${producer.id} for peer ${this.peerId}`);
    } catch (error) {
      console.error("❌ Error producing:", error);
      callback({ error: error.message });
    }
  }

  async #handleConsume({ transportId, producerId, rtpCapabilities }, callback) {
    try {
      const room = mediaSoupManager.getRoom(this.roomId);
      const peer = room.getPeer(this.peerId);

      const consumer = await peer.consume(
        transportId,
        producerId,
        rtpCapabilities,
      );

      callback({
        params: {
          producerId,
          id: consumer.id,
          kind: consumer.kind,
          rtpParameters: consumer.rtpParameters,
        },
      });

      console.log(`📺 Created consumer ${consumer.id} for peer ${this.peerId}`);
    } catch (error) {
      console.error("❌ Error consuming:", error);
      callback({ error: error.message });
    }
  }

  async #handleConsumerResume({ consumerId }, callback) {
    try {
      const room = mediaSoupManager.getRoom(this.roomId);
      const peer = room.getPeer(this.peerId);
      const consumer = peer.consumers.get(consumerId);

      if (consumer) {
        await consumer.resume();
        callback({});
        console.log(
          `▶️ Resumed consumer ${consumerId} for peer ${this.peerId}`,
        );
      }
    } catch (error) {
      console.error("❌ Error resuming consumer:", error);
      callback({ error: error.message });
    }
  }
}

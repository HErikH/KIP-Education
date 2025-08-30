import type { Socket } from "socket.io-client";
import { ROOMS_HANDLER_ACTIONS as ACTIONS } from "./roomsActions";
import { useRoomsStore } from "@/store";
import { Device } from "mediasoup-client";
import { mediaService } from "@/services/mediaService";

export class RoomsHandler {
  private socket: Socket;
  private device: Device;

  constructor(socket: Socket) {
    this.socket = socket;
    this.device = new Device();
  }

  registerHandlers() {
    this.socket.on(ACTIONS.ADD_PEER, (data) => this.#handleAddPeer(data));
    this.socket.on(ACTIONS.REMOVE_PEER, (data) => this.#handleRemovePeer(data));
    this.socket.on(ACTIONS.NEW_PRODUCER, (data) =>
      this.#handleNewProducer(data),
    );
    this.socket.on(ACTIONS.DISCONNECTING, () => this.#handleDisconnect());
  }

  // * Listeners
  #handleAddPeer({
    peerId,
    peers,
  }: {
    peerId?: string;
    peers?: string[];
  }): void {
    if (peerId) {
      useRoomsStore.getState().addPeer(peerId);
      console.log(`👤 Peer ${peerId} added`);
    }

    if (peers) {
      peers.forEach((id) => {
        useRoomsStore.getState().addPeer(id);
      });
      console.log(`👥 Added ${peers.length} existing peers`);
    }
  }

  #handleRemovePeer({ peerId }: { peerId: string }): void {
    useRoomsStore.getState().removePeer(peerId);
    console.log(`👋 Peer ${peerId} removed`);
  }

  async #handleNewProducer({
    peerId,
    producerId,
    kind,
  }: {
    peerId: string;
    producerId: string;
    kind: string;
  }): Promise<void> {
    try {
      const { recvTransport, rtpCapabilities } = useRoomsStore.getState();

      if (!recvTransport || !rtpCapabilities) {
        throw new Error("Transport or RTP capabilities not available");
      }

      // Create consumer
      const { params } = await this.emitWithCallback(ACTIONS.CONSUME, {
        transportId: recvTransport.id,
        producerId,
        rtpCapabilities,
      });

      const consumer = await recvTransport.consume(params);

      console.log(consumer, "Consumer handler")

      // Resume consumer
      await this.emitWithCallback(ACTIONS.CONSUMER_RESUME, {
        consumerId: consumer.id,
      });

      // Update store
      useRoomsStore.getState().updatePeerConsumer(peerId, consumer, kind);

      console.log(`📺 Created consumer for peer ${peerId}, kind: ${kind}`);
    } catch (error) {
      console.error("❌ Error handling new producer:", error);
    }
  }

  #handleDisconnect(): void {
    console.log("🔌 Socket disconnected");
    useRoomsStore.getState().setConnected(false);
  }

  // * Emitters
  emitWithCallback(event: string, data: any): Promise<any> {
    return new Promise((resolve, reject) => {
      if (!this.socket) {
        reject(new Error("Socket not connected"));
        return;
      }

      this.socket.emit(event, data, (response: any) => {
        if (response.error) {
          reject(new Error(response.error));
        } else {
          resolve(response);
        }
      });
    });
  }

  async joinRoom(roomId: string): Promise<void> {
    try {
      if (!this.socket) throw new Error("Socket not connected");

      console.log(`👤 Joining room: ${roomId}`);

      // Get RTP capabilities
      const { rtpCapabilities } = await this.emitWithCallback(
        ACTIONS.GET_RTP_CAPABILITIES,
        {},
      );

      // Load device with RTP capabilities
      await this.device.load({ routerRtpCapabilities: rtpCapabilities });

      // Update store
      useRoomsStore.getState().setRtpCapabilities(rtpCapabilities);
      useRoomsStore.getState().setRoomId(roomId);

      // Join room
      this.socket.emit(ACTIONS.JOIN_ROOM, { roomId });

      // Create transports 
      await this.#createSendTransport();
      await this.#createRecvTransport();

      useRoomsStore.getState().setConnected(true);
      console.log("✅ Successfully joined room");
    } catch (error) {
      console.error("❌ Error joining room:", error);
      throw error;
    }
  }

  async leaveRoom(): Promise<void> {
    try {
      const { roomId } = useRoomsStore.getState();
      if (roomId) {
        this.socket.emit(ACTIONS.LEAVE_ROOM, { roomId });
      }

      // Reset store and clean up resources
      useRoomsStore.getState().reset();
      console.log("👋 Left room successfully");
    } catch (error) {
      console.error("❌ Error leaving room:", error);
    }
  }

  async enableWebcam(): Promise<void> {
    try {
      const { sendTransport } = useRoomsStore.getState();
      if (!sendTransport) throw new Error("Send transport not available");

      // Get user media
      const stream = await mediaService.getUserMedia({
        video: { width: 640, height: 480, frameRate: 30 },
        audio: true,
      });

      useRoomsStore.getState().setLocalStream(stream);

      // Produce video
      const videoTrack = stream.getVideoTracks()[0];
      if (videoTrack) {
        const videoProducer = await sendTransport.produce({
          track: videoTrack,
        });

        useRoomsStore.getState().addProducer(videoProducer.id, videoProducer);
        useRoomsStore.getState().updateLocalMediaState({ localVideo: true });
      }

      // Produce audio
      const audioTrack = stream.getAudioTracks()[0];
      if (audioTrack) {
        const audioProducer = await sendTransport.produce({
          track: audioTrack,
        });

        useRoomsStore.getState().addProducer(audioProducer.id, audioProducer);
        useRoomsStore.getState().updateLocalMediaState({ localAudio: true });
      }

      console.log("🎥 Webcam enabled successfully");
    } catch (error) {
      console.error("❌ Error enabling webcam:", error);
      throw error;
    }
  }

  async disableWebcam(): Promise<void> {
    try {
      const { producers, localStream } = useRoomsStore.getState();

      // Close video/audio producers
      producers.forEach((producer) => {
        if (producer.kind === "video" || producer.kind === "audio") {
          useRoomsStore.getState().removeProducer(producer.id);
        }
      });

      // Stop local stream
      mediaService.stopMediaStream(localStream);
      useRoomsStore.getState().setLocalStream(null);
      useRoomsStore.getState().updateLocalMediaState({
        localVideo: false,
        localAudio: false,
      });

      console.log("🎥 Webcam disabled successfully");
    } catch (error) {
      console.error("❌ Error disabling webcam:", error);
    }
  }

  async enableScreenShare(): Promise<void> {
    try {
      const { sendTransport } = useRoomsStore.getState();
      if (!sendTransport) throw new Error("Send transport not available");

      // Get display media
      const stream = await mediaService.getDisplayMedia();
      useRoomsStore.getState().setLocalScreenStream(stream);

      // Produce screen share
      const videoTrack = stream.getVideoTracks()[0];
      if (videoTrack) {
        const screenProducer = await sendTransport.produce({
          track: videoTrack,
        });

        useRoomsStore.getState().addProducer(screenProducer.id, screenProducer);
        useRoomsStore.getState().updateLocalMediaState({ localScreen: true });

        // Handle screen share end
        videoTrack.onended = () => {
          this.disableScreenShare();
        };
      }

      console.log("🖥️ Screen share enabled successfully");
    } catch (error) {
      console.error("❌ Error enabling screen share:", error);
      throw error;
    }
  }

  async disableScreenShare(): Promise<void> {
    try {
      const { producers, localScreenStream } = useRoomsStore.getState();

      // Find and close screen producer
      producers.forEach((producer) => {
        if (producer.kind === "video" && producer.appData.screen) {
          useRoomsStore.getState().removeProducer(producer.id);
        }
      });

      // Stop screen stream
      mediaService.stopMediaStream(localScreenStream);
      useRoomsStore.getState().setLocalScreenStream(null);
      useRoomsStore.getState().updateLocalMediaState({ localScreen: false });

      console.log("🖥️ Screen share disabled successfully");
    } catch (error) {
      console.error("❌ Error disabling screen share:", error);
    }
  }

  // Private methods
  async #createSendTransport(): Promise<void> {
    try {
      const { params } = await this.emitWithCallback(ACTIONS.CREATE_TRANSPORT, {
        direction: "send",
      });

      const sendTransport = this.device.createSendTransport(params);

      sendTransport.on(
        "connect",
        async ({ dtlsParameters }, callback, errback) => {
          try {
            await this.emitWithCallback(ACTIONS.CONNECT_TRANSPORT, {
              transportId: sendTransport.id,
              dtlsParameters,
            });
            callback();
          } catch (error: any) {
            errback(error);
          }
        },
      );

      sendTransport.on("produce", async (parameters, callback, errback) => {
        try {
          const { id } = await this.emitWithCallback(ACTIONS.PRODUCE, {
            transportId: sendTransport.id,
            kind: parameters.kind,
            rtpParameters: parameters.rtpParameters,
          });
          callback({ id });
        } catch (error: any) {
          errback(error);
        }
      });

      useRoomsStore.getState().setSendTransport(sendTransport);
      console.log("📤 Send transport created");
    } catch (error) {
      console.error("❌ Error creating send transport:", error);
      throw error;
    }
  }

  async #createRecvTransport(): Promise<void> {
    try {
      const { params } = await this.emitWithCallback(ACTIONS.CREATE_TRANSPORT, {
        direction: "recv",
      });

      const recvTransport = this.device.createRecvTransport(params);

      recvTransport.on(
        "connect",
        async ({ dtlsParameters }, callback, errback) => {
          try {
            await this.emitWithCallback(ACTIONS.CONNECT_TRANSPORT, {
              transportId: recvTransport.id,
              dtlsParameters,
            });
            callback();
          } catch (error: any) {
            errback(error);
          }
        },
      );

      useRoomsStore.getState().setRecvTransport(recvTransport);
      console.log("📥 Receive transport created");
    } catch (error) {
      console.error("❌ Error creating receive transport:", error);
      throw error;
    }
  }
}

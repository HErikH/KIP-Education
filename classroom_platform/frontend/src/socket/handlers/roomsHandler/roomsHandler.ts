import type { Socket } from "socket.io-client";
import { ROOMS_HANDLER_ACTIONS as ACTIONS } from "./roomsActions";
import { useRoomsStore } from "@/store";
import { Device } from "mediasoup-client";
import { mediaService } from "@/services/mediaService";
import type { AppData } from "mediasoup-client/types";
import type { T_RaiseHand, T_RoomInfo } from "@/helpers/types/rooms";

export class RoomsHandler {
  private socket: Socket;
  private device: Device;

  constructor(socket: Socket) {
    this.socket = socket;
    this.device = new Device();
  }

  registerHandlers() {
    this.socket.on(ACTIONS.CHECK_ROOM_STATUS, (data) =>
      this.handleRoomStatus(data),
    );
    this.socket.on(ACTIONS.USER_COUNT, (data) => this.handleUserCount(data));
    this.socket.on(ACTIONS.HANDS_STATE, (data) => this.handleHandState(data));
    this.socket.on(ACTIONS.RAISE_HAND, (data) => this.handleRaiseHand(data));
    this.socket.on(ACTIONS.LOWER_HAND, (data) => this.handleLowerHand(data));
    this.socket.on(ACTIONS.ADD_PEER, (data) => this.handleAddPeer(data));
    this.socket.on(ACTIONS.REMOVE_PEER, (data) => this.handleRemovePeer(data));
    this.socket.on(ACTIONS.NEW_PRODUCER, (data) =>
      this.handleNewProducer(data),
    );
    this.socket.on(ACTIONS.DISCONNECT, () => this.handleDisconnect());
    this.socket.on(ACTIONS.PRODUCER_CLOSED, (data) =>
      this.handleProducerClosed(data),
    );
  }

  // * Listeners
  private async handleRoomStatus({
    roomId,
    userId,
    alreadyInRoom,
    username,
    role,
  }: {
    roomId: T_RoomInfo["room_id"];
    userId: T_RoomInfo["user_id"];
    alreadyInRoom: boolean;
    username: T_RoomInfo["username"];
    role: T_RoomInfo["role"];
  }): Promise<void> {
    if (alreadyInRoom) {
      console.log("⚠️ Already in the room!");
    } else {
      console.log(roomId, userId);
      this.joinRoom(roomId, userId, username, role);
    }
  }

  // TODO: Maybe it will be separate for each room id not global one 
  // currently this only will work inside the room but not outside when we see all rooms list
  private async handleUserCount({
    usersInRoomCount,
    maxUsersInRoom
  }: {
    usersInRoomCount: number;
    maxUsersInRoom: number;
  }): Promise<void> {
    useRoomsStore.getState().setUsersInRoomCount(usersInRoomCount);
    useRoomsStore.getState().setMaxUsersInRoom(maxUsersInRoom);
  }

  private async handleRaiseHand({
    userId,
  }: {
    roomId: T_RoomInfo["room_id"];
    userId: T_RoomInfo["user_id"];
    username: T_RoomInfo["username"];
  }): Promise<void> {
    useRoomsStore.getState().setRaiseHand(userId, true);
  }

  private async handleLowerHand({
    userId,
  }: {
    roomId: T_RoomInfo["room_id"];
    userId: T_RoomInfo["user_id"];
    username: T_RoomInfo["username"];
  }): Promise<void> {
    useRoomsStore.getState().setRaiseHand(userId, false);
  }

  private async handleHandState({
    usersInRoom,
  }: {
    usersInRoom: T_RaiseHand[];
  }): Promise<void> {
    useRoomsStore.getState().setRaisedHands(usersInRoom);
  }

  private handleAddPeer({
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

  private handleRemovePeer({ peerId }: { peerId: string }): void {
    useRoomsStore.getState().removePeer(peerId);
    console.log(`👋 Peer ${peerId} removed`);
  }

  private async handleNewProducer({
    peerId,
    producerId,
    kind,
    appData = undefined,
  }: {
    peerId: string;
    producerId: string;
    kind: string;
    appData?: AppData;
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

      // Resume consumer
      await this.emitWithCallback(ACTIONS.CONSUMER_RESUME, {
        consumerId: consumer.id,
      });

      // Update store
      useRoomsStore
        .getState()
        .updatePeerConsumer(peerId, consumer, kind, appData);

      console.log(`📺 Created consumer for peer ${peerId}, kind: ${kind}`);
    } catch (error) {
      console.error("❌ Error handling new producer:", error);
    }
  }

  private handleDisconnect(): void {
    console.log("🔌 Socket disconnected");
    useRoomsStore.getState().setConnected(false);
  }

  private handleProducerClosed({
    peerId,
    kind,
    appData,
  }: {
    peerId: string;
    kind: string;
    appData?: AppData | null;
  }) {
    // 🔧 CLEAN UP existing consumer for the same kind
    const { peers } = useRoomsStore.getState();
    const peer = peers.get(peerId);

    if (peer) {
      // Find and close existing consumer of the same kind
      const existingConsumer = Array.from(peer.consumers.values()).find(
        (consumer) => {
          return (
            consumer.kind === kind &&
            // Covers both undefined and null because ( == )
            (appData == null || consumer.appData?.screen === appData?.screen)
          );
        },
      );

      if (existingConsumer) {
        console.log(
          `🧹 Closing existing ${kind} consumer ${existingConsumer.id} for peer ${peerId}`,
        );
        existingConsumer.close();

        // Update store to remove the old consumer
        useRoomsStore
          .getState()
          .removePeerConsumer(peerId, existingConsumer.id);
      }
    }
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

  async checkRoomStatus(
    roomId: T_RoomInfo["room_id"],
    userId: T_RoomInfo["user_id"],
    username: T_RoomInfo["username"],
    role: T_RoomInfo["role"],
  ): Promise<void> {
    this.socket.emit(ACTIONS.CHECK_ROOM_STATUS, {
      roomId,
      userId,
      username,
      role,
    });
  }

  async raiseHand(
    roomId: T_RoomInfo["room_id"],
    userId: T_RoomInfo["user_id"],
    username: T_RoomInfo["username"],
  ): Promise<void> {
    this.socket.emit(ACTIONS.RAISE_HAND, { roomId, userId, username });
  }

  async lowerHand(
    roomId: T_RoomInfo["room_id"],
    userId: T_RoomInfo["user_id"],
    username: T_RoomInfo["username"],
  ): Promise<void> {
    this.socket.emit(ACTIONS.LOWER_HAND, { roomId, userId, username });
  }

  async joinRoom(
    roomId: T_RoomInfo["room_id"],
    userId: T_RoomInfo["user_id"],
    username: T_RoomInfo["username"],
    role: T_RoomInfo["role"],
  ): Promise<void> {
    try {
      if (!this.socket) throw new Error("Socket not connected");

      console.log(`👤 Joining room: ${roomId}`);

      this.device = new Device();

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
      useRoomsStore.getState().setUserId(userId);
      useRoomsStore.getState().setUsername(username);
      useRoomsStore.getState().setUserRole(role);

      // Join room
      this.socket.emit(ACTIONS.JOIN_ROOM, { roomId, userId, username, role });

      // Create transports
      await this.createSendTransport();
      await this.createRecvTransport();

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
        video: { width: 1280, height: 720, frameRate: 30 },
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
        if (
          (producer.kind === "video" && !producer.appData?.screen) ||
          producer.kind === "audio"
        ) {
          this.socket.emit(ACTIONS.PRODUCER_CLOSED, {
            producerId: producer.id,
            kind: producer.kind,
            appData: producer.appData,
          });

          producer.close();
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
          appData: { screen: true },
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
          this.socket.emit(ACTIONS.PRODUCER_CLOSED, {
            producerId: producer.id,
            kind: producer.kind,
            appData: producer.appData,
          });

          producer.close();
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

  private async createSendTransport(): Promise<void> {
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
            appData: parameters.appData,
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

  private async createRecvTransport(): Promise<void> {
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

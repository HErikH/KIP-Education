import mediasoup from "mediasoup";
import { mediaSoupConfig as config } from "../config/mediaSoupConfig.js";
import { Room } from "./room.js";

export class MediaSoupManager {
  constructor() {
    this.worker = null;
    this.router = null;
    this.rooms = new Map(); // roomId -> Room instance
  }

  async init() {
    try {
      // Create mediasoup Worker
      this.worker = await mediasoup.createWorker({ ...config.worker });

      this.worker.on("died", () => {
        console.error(
          "MediaSoup worker died, exiting in 2 seconds... [pid:%d]",
          this.worker.pid,
        );

        setTimeout(() => process.exit(1), 2000);
      });

      // Create Router
      this.router = await this.worker.createRouter({
        mediaCodecs: config.router.mediaCodecs,
      });

      console.log("✅ MediaSoup initialized successfully");
    } catch (error) {
      console.error("❌ Failed to initialize MediaSoup:", error);

      throw error;
    }
  }

  getRoom(roomId) {
    return this.rooms.get(roomId);
  }

  createRoom(roomId) {
    if (this.rooms.has(roomId)) {
      return this.rooms.get(roomId);
    }

    const room = new Room(roomId, this.router);
    this.rooms.set(roomId, room);

    console.log(`📦 Created new room: ${roomId}`);

    return room;
  }

  deleteRoom(roomId) {
    const room = this.rooms.get(roomId);
    if (room) {
      room.close();
      this.rooms.delete(roomId);

      console.log(`🗑️ Deleted room: ${roomId}`);
    }
  }

  getRoomIds() {
    return Array.from(this.rooms.keys());
  }
}

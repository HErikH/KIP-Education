import type { Producer } from "mediasoup-client/types";

class MediaService {
  private static instance: MediaService;

  private constructor() {}

  static getInstance(): MediaService {
    if (!MediaService.instance) {
      MediaService.instance = new MediaService();
    }

    return MediaService.instance;
  }

  async getUserMedia(
    constraints: MediaStreamConstraints,
  ): Promise<MediaStream> {
    try {
      const stream = await navigator.mediaDevices.getUserMedia(constraints);

      return stream;
    } catch (error) {
      console.error("❌ Error getting user media:", error);

      throw new Error("Failed to access camera/microphone");
    }
  }

  async getDisplayMedia(): Promise<MediaStream> {
    try {
      const stream = await navigator.mediaDevices.getDisplayMedia({
        video: {
          width: { ideal: 1280 },
          height: { ideal: 720 },
          frameRate: { ideal: 30 },
        },
        audio: true,
      });

      return stream;
    } catch (error) {
      console.error("❌ Error getting display media:", error);

      throw new Error("Failed to access screen share");
    }
  }

  stopMediaStream(stream: MediaStream | null): void {
    if (stream) {
      stream.getTracks().forEach((track) => {
        track.stop();
      });
    }
  }

  replaceVideoTrack(
    producer: Producer,
    track: MediaStreamTrack,
  ): Promise<void> {
    return producer.replaceTrack({ track });
  }
}

export const mediaService = MediaService.getInstance();

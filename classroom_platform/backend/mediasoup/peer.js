import { mediaSoupConfig as config } from "../config/mediaSoupConfig.js";

export class Peer {
  constructor(socketId, router) {
    this.id = socketId;
    this.router = router;
    this.transports = new Map(); // transportId -> Transport
    this.producers = new Map(); // producerId -> Producer
    this.consumers = new Map(); // consumerId -> Consumer
  }

  async createWebRtcTransport() {
    try {
      const transport = await this.router.createWebRtcTransport({
        ...config.webRtcTransport,
      });

      transport.on("dtlsstatechange", (dtlsState) => {
        if (dtlsState === "closed") {
          transport.close();
        }
      });

      transport.on("close", () => {
        console.log("🚫 Transport closed");
      });

      this.transports.set(transport.id, transport);

      return transport;
    } catch (error) {
      console.error("❌ Error creating WebRTC transport:", error);

      throw error;
    }
  }

  async connectTransport(transportId, dtlsParameters) {
    const transport = this.transports.get(transportId);

    if (!transport) {
      throw new Error("Transport not found");
    }

    await transport.connect({ dtlsParameters });
  }

  async produce(transportId, rtpParameters, kind, appData) {
    const transport = this.transports.get(transportId);

    if (!transport) {
      throw new Error("Transport not found");
    }

    const producer = await transport.produce({
      kind,
      rtpParameters,
      appData: appData || undefined,
    });

    producer.on("transportclose", () => {
      console.log("🚫 Producer transport closed");

      producer.close();
    });

    producer.on("close", () => {
      console.log(`🚫 Producer ${producer.id} closed, removing from map`);
      this.producers.delete(producer.id);
    });

    this.producers.set(producer.id, producer);

    return producer;
  }

  async consume(transportId, producerId, rtpCapabilities) {
    const transport = this.transports.get(transportId);

    if (!transport) {
      throw new Error("Transport not found");
    }

    if (
      !this.router.canConsume({
        producerId,
        rtpCapabilities,
      })
    ) {
      throw new Error("Cannot consume");
    }

    const consumer = await transport.consume({
      producerId,
      rtpCapabilities,
    });

    consumer.on("transportclose", () => {
      console.log("🚫 Consumer transport closed");

      consumer.close();
    });

    consumer.on("producerclose", () => {
      console.log("🚫 Consumer producer closed");

      consumer.close();
      this.consumers.delete(consumer.id);
    });

    this.consumers.set(consumer.id, consumer);

    return consumer;
  }

  close() {
    // Close all transports
    for (const transport of this.transports.values()) {
      transport.close();
    }

    this.transports.clear();
    this.producers.clear();
    this.consumers.clear();
  }
}

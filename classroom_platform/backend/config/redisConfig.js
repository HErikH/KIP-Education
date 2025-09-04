import { createClient } from "redis";
import { REDIS_HOST, REDIS_PORT, REDIS_PASSWORD } from "./envConfig.js";

class RedisManager {
  constructor() {
    this.client = null;
    this.isConnected = false;
  }

  async connect() {
    try {
      this.client = createClient({
        // Basic Redis configuration
        host: REDIS_HOST,
        port: REDIS_PORT,
        password: REDIS_PASSWORD,

        // Retry strategy
        retry_strategy: (options) => {
          if (options.error && options.error.code === "ECONNREFUSED") {
            console.error("Redis server connection refused");
            return new Error("Redis server connection refused");
          }

          if (options.total_retry_time > 1000 * 60 * 60) {
            console.error("Redis retry time exhausted");
            return new Error("Retry time exhausted");
          }

          if (options.attempt > 10) {
            console.error("Redis connection attempts exhausted");
            return undefined;
          }

          return Math.min(options.attempt * 100, 3000);
        },
      });

      this.client.on("error", (err) => {
        console.error("Redis Client Error:", err);
        this.isConnected = false;
      });

      this.client.on("connect", () => {
        console.log("✅ Redis connected");
        this.isConnected = true;
      });

      this.client.on("disconnect", () => {
        console.log("❌ Redis disconnected");
        this.isConnected = false;
      });

      await this.client.connect();
      return this.client;
    } catch (error) {
      console.error("Failed to connect to Redis:", error);
      throw error;
    }
  }

  async disconnect() {
    if (this.client) {
      await this.client.disconnect();
    }
  }

  getClient() {
    if (!this.isConnected || !this.client) {
      throw new Error("Redis client not connected");
    }

    return this.client;
  }
}

export const redisManager = new RedisManager();
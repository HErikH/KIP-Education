import { v4 as uuidv4 } from "uuid";
import { redisManager } from "../config/redisConfig.js";
import { CHAT_HANDLER_ACTIONS as ACTIONS } from "../socket/handlers/chatHandler/chatActions.js"

export class ChatService {
  constructor() {
    this.redis = null;
  }

  async init() {
    this.redis = redisManager.getClient();
  }

  // Create message object
  createMessage(
    senderId,
    content,
    roomId,
    type = "message", // 'message', 'system', 'join', 'leave'
    senderRole = null,
  ) {
    return {
      messageId: uuidv4(),
      senderId: senderId, // Currently socket.id, easily replaceable with userId
      senderRole: senderRole,
      content: content,
      timestamp: new Date().toISOString(),
      roomId: roomId,
      type: type, 
    };
  }

  // Store message in Redis using sorted set for chronological ordering
  async storeMessage(message) {
    try {
      const key = `room:${message.roomId}:messages`;
      const score = Date.now(); // Use timestamp as score for ordering
      const value = JSON.stringify(message);

      await this.redis.zAdd(key, [{ score, value }]);

      // Optional: Keep only last 1000 messages per room to prevent memory issues
      await this.redis.zRemRangeByRank(key, 0, -1001);

      return message;
    } catch (error) {
      console.error("Error storing message:", error);
      throw error;
    }
  }

  // Get chat history for room
  async getChatHistory(roomId, limit = 100) {
    try {
      const key = `room:${roomId}:messages`;

      // Get messages in chronological order (oldest first)
      const messages = await this.redis.zRange(key, -limit, -1);

      return messages.map((msg) => JSON.parse(msg));
    } catch (error) {
      console.error("Error getting chat history:", error);
      return [];
    }
  }

  // Create system message for user join/leave
  createSystemMessage(roomId, content, type = "system") {
    return this.createMessage("system", content, roomId, type);
  }

  // Store and broadcast system message
  async handleSystemMessage(io, roomId, content, type = "system") {
    try {
      const systemMessage = this.createSystemMessage(roomId, content, type);
      await this.storeMessage(systemMessage);

      // Broadcast to room
      io.to(roomId).emit(ACTIONS.CHAT_MESSAGE, systemMessage);

      return systemMessage;
    } catch (error) {
      console.error("Error handling system message:", error);
      throw error;
    }
  }

  // Get room participant count (optional utility)
  async getRoomParticipantCount(roomId) {
    try {
      const key = `room:${roomId}:participants`;
      
      return await this.redis.sCard(key);
    } catch (error) {
      console.error("Error getting participant count:", error);
      return 0;
    }
  }

  // Add participant to room tracking
  async addParticipant(roomId, participantId) {
    try {
      const key = `room:${roomId}:participants`;
      await this.redis.sAdd(key, participantId);
    } catch (error) {
      console.error("Error adding participant:", error);
    }
  }

  // Remove participant from room tracking
  async removeParticipant(roomId, participantId) {
    try {
      const key = `room:${roomId}:participants`;
      await this.redis.sRem(key, participantId);
    } catch (error) {
      console.error("Error removing participant:", error);
    }
  }
}

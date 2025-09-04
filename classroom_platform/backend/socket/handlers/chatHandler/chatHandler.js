import { CHAT_HANDLER_ACTIONS as ACTIONS } from "./chatActions.js";
import { ChatService } from "../../../services/ChatService.js";
import { validateMessage } from "../../../helpers/functions/messageValidator.js";

export class ChatHandler {
  constructor(io, socket) {
    this.io = io;
    this.socket = socket;
    this.roomId = null;
    this.userId = socket.id; // Currently socket.id, easily replaceable
    this.userRole = null;
    this.chatService = new ChatService();
  }

  async registerHandlers() {
    // * Initialize chat service
    await this.chatService.init();

    this.socket.on(ACTIONS.SEND_MESSAGE, (data) =>
      this.#handleSendMessage(data),
    );
    this.socket.on("disconnect", () => this.#handleDisconnect());
  }

  async handleJoinRoom(roomId) {
    try {
      console.log(`💬 User ${this.userId} joining chat in room ${roomId}`);

      this.roomId = roomId;

      // Add to participant tracking
      await this.chatService.addParticipant(roomId, this.userId);

      // Send chat history to the new user
      await this.#sendChatHistory(roomId);

      // Notify others about new user (system message)
      await this.chatService.handleSystemMessage(
        this.io,
        roomId,
        `User ${this.userId} joined the chat`,
        "join",
      );

      console.log(
        `✅ User ${this.userId} successfully joined chat in room ${roomId}`,
      );
    } catch (error) {
      console.error("Error handling join room for chat:", error);
      this.socket.emit(ACTIONS.CHAT_ERROR, {
        error: "Failed to join chat room",
      });
    }
  }

  // Handle user leaving room (call this from your RoomsHandler)
  async handleLeaveRoom() {
    if (!this.roomId) return;

    try {
      console.log(`💬 User ${this.userId} leaving chat in room ${this.roomId}`);

      // Remove from participant tracking
      await this.chatService.removeParticipant(this.roomId, this.userId);

      // Notify others about user leaving (system message)
      await this.chatService.handleSystemMessage(
        this.io,
        this.roomId,
        `User ${this.userId} left the chat`,
        "leave",
      );

      this.roomId = null;
    } catch (error) {
      console.error("Error handling leave room for chat:", error);
    }
  }

  // Private method to handle sending messages
  async #handleSendMessage(data) {
    try {
      if (!this.roomId) {
        this.socket.emit(ACTIONS.CHAT_ERROR, {
          error: "Not in any room",
        });
        return;
      }

      const { content } = data;

      // Validate message content
      const validation = validateMessage(content);

      if (!validation.valid) {
        this.socket.emit(ACTIONS.CHAT_ERROR, {
          error: validation.error,
        });
        return;
      }

      // Create message object
      const message = this.chatService.createMessage(
        this.userId,
        validation.content,
        this.roomId,
        "message",
        this.userRole,
      );

      // Store message in Redis
      await this.chatService.storeMessage(message);

      // Broadcast message to all users in the room
      this.io.to(this.roomId).emit(ACTIONS.CHAT_MESSAGE, message);

      console.log(`📨 Message sent in room ${this.roomId} by ${this.userId}`);
    } catch (error) {
      console.error("Error handling send message:", error);
      this.socket.emit(ACTIONS.CHAT_ERROR, {
        error: "Failed to send message",
      });
    }
  }

  // Send chat history to user
  async #sendChatHistory(roomId) {
    try {
      const history = await this.chatService.getChatHistory(roomId, 50); // Last 50 messages

      this.socket.emit(ACTIONS.CHAT_HISTORY, {
        messages: history,
        roomId: roomId,
      });
    } catch (error) {
      console.error("Error sending chat history:", error);
      this.socket.emit(ACTIONS.CHAT_ERROR, {
        error: "Failed to load chat history",
      });
    }
  }

  // Handle disconnect
  async #handleDisconnect() {
    if (this.roomId) {
      await this.handleLeaveRoom();
    }
  }

  // Update user info (for future use when you have user data)
  updateUserInfo(userId, userRole = null) {
    this.userId = userId;
    this.userRole = userRole;
  }
}

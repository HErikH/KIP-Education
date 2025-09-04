import type { Socket } from "socket.io-client";
import { CHAT_HANDLER_ACTIONS as ACTIONS } from "./chatActions";
import type {
  T_ChatErrorData,
  T_ChatHistoryData,
  T_Message,
  T_SendMessageData,
} from "@/store/chat/types";
import { useChatStore } from "@/store/chat/store";

export class ChatHandler {
  private socket: Socket;

  constructor(socket: Socket) {
    this.socket = socket;
  }

  registerHandlers() {
    this.socket.on(ACTIONS.CHAT_MESSAGE, (data) =>
      this.handleChatMessage(data),
    );
    this.socket.on(ACTIONS.CHAT_HISTORY, (data) =>
      this.handleChatHistory(data),
    );
    this.socket.on(ACTIONS.CHAT_ERROR, (data) => this.handleChatError(data));
    this.socket.on("connect", () => this.handleConnect());
    this.socket.on("disconnect", () => this.handleDisconnect());
  }

  // * Listeners
  private handleChatMessage = (message: T_Message) => {
    useChatStore.getState().setChatMessage(message);
  };

  private handleChatHistory = (data: T_ChatHistoryData) => {
    useChatStore.getState().setChatHistory(data);
  };

  private handleChatError = (data: T_ChatErrorData) => {
    useChatStore.getState().setChatError(data);
  };

  private handleConnect = () => {
    useChatStore.getState().setIsConnected(true);
    useChatStore.getState().setSocketId(this.socket.id);
  };

  private handleDisconnect = () => {
    useChatStore.getState().setIsConnected(false);
  };

  // * Emitters
  sendMessage = (content: T_SendMessageData["content"]) => {
    if (!this.socket || !content.trim()) return;

    const messageData: T_SendMessageData = { content: content.trim() };

    this.socket.emit(ACTIONS.SEND_MESSAGE, messageData);
  };
}

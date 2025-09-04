export const CHAT_HANDLER_ACTIONS = {
  SEND_MESSAGE: "chat:sendMessage",
  CHAT_MESSAGE: "chat:message",
  CHAT_HISTORY: "chat:history",
  CHAT_ERROR: "chat:error",
  USER_JOINED_CHAT: "chat:userJoinedChat",
  USER_LEFT_CHAT: "chat:userLeftChat",
} as const;
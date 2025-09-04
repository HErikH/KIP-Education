export type T_Message = {
  messageId: string;
  senderId: string;
  senderRole: string | null;
  content: string;
  timestamp: string;
  roomId: string;
  type: 'message' | 'system' | 'join' | 'leave';
}

export type T_ChatState = {
  messages: T_Message[];
  isConnected: boolean;
  isLoading: boolean;
  error: string | null;
  currentRoomId: string | null;
  socketId: string | undefined;
}

export type T_SendMessageData = {
  content: string;
}

export type T_ChatHistoryData = {
  messages: T_Message[];
  roomId: string;
}

export type T_ChatErrorData = {
  error: string | null;
}

export type T_ChatActions = {
  setChatMessage: (message: T_Message) => void;
  setChatHistory: (data: T_ChatHistoryData) => void;
  setChatError: (data: T_ChatErrorData) => void;
  setIsConnected: (isConnected: T_ChatState["isConnected"]) => void;
  setIsLoading: (isLoading: T_ChatState["isLoading"]) => void;
  setSocketId: (socketId: T_ChatState["socketId"]) => void;
};

export type T_ChatStore = T_ChatState & T_ChatActions;

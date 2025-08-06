import { io } from "socket.io-client";

export const socket = io(import.meta.env.VITE_BACK_END_PORT, {
  transports: ["websocket", "polling"], // Allow fallback to polling
  upgrade: true,
  rememberUpgrade: true,
  autoConnect: true,
  forceNew: false,
  reconnection: true,
  reconnectionAttempts: Infinity,
  reconnectionDelay: 1000,
});

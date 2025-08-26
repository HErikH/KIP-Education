import { io, Socket } from "socket.io-client";
import { RoomsHandler } from "./handlers/roomsHandler/roomsHandler";
import { WebRTCHandler } from "./handlers/webRTCHandler/webRTCHandler";

export const socket: Socket = io(import.meta.env.VITE_BACK_END_PORT, {
  transports: ["websocket", "polling"], // Allow fallback to polling
  upgrade: true,
  rememberUpgrade: true,
  autoConnect: true,
  forceNew: false,
  reconnection: true,
  reconnectionAttempts: Infinity,
  reconnectionDelay: 1000,
});

export const roomsHandler = new RoomsHandler(socket);
export const webRTCHandler = new WebRTCHandler(socket);

export function onSocketConnection() {
    roomsHandler.registerHandlers();
    webRTCHandler.registerHandlers();
}

import { Server } from "socket.io";
import { RoomHandler } from "../socket/handlers/roomsHandler.js";

export function socketServer(server) {
  return new Server(server, {
    // Configure CORS for Socket.IO with WebSocket support
    cors: {
      // ! Change to the Prod origin
      origin: "*",
      methods: ["GET", "POST", "PUT", "DELETE"],
      credentials: true,
    },
    allowEIO3: true, // Allow Engine.IO v3 clients
    transports: ["websocket", "polling"], // Explicitly enable both transports
  });
}

export const onConnection = (io) => (socket) => {
  console.log("🔌 A user connected: ", socket.id);

  new RoomHandler(io, socket);
};

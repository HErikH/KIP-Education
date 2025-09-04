import { Server } from "socket.io";
import { RoomsHandler } from "../socket/handlers/roomsHandler/roomsHandler.js";

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
    connectionStateRecovery: {} // Helps when some socket stayed disconnected this will restore missed data
  });
}


export const onSocketConnection = (io) => (socket) => {
  console.log("🔌 A user connected: ", socket.id);
  
  const roomsHandler = new RoomsHandler(io, socket);

  roomsHandler.registerHandlers();
};

import { ACTIONS } from "../socketActions.js";
import { version, validate } from "uuid";

export class RoomsHandler {
  constructor(io, socket) {
    this.io = io;
    this.socket = socket;

    this.registerHandlers();

    this.shareRoomsInfo();
  }

  registerHandlers() {
    this.socket.on(ACTIONS.JOIN_ROOM, this.handleJoinRoom);
    this.socket.on(ACTIONS.LEAVE, this.leaveRoom);
    // this.socket.on("disconnecting", this.leaveRoom);
  }

  async handleJoinRoom({ roomId, userId, role }) {
    const joinedRooms = this.socket.rooms;

    if (this.isUserJoined(joinedRooms, roomId)) {
      return console.warn(`Already joined to the ${roomId}`);
    }

    const socketsInRoom = (await this.io.in(roomId).fetchSockets()) || [];

    socketsInRoom.forEach((clientSocket) => {
      this.io.to(clientSocket.id).emit(ACTIONS.ADD_PEER, {
        peerId: this.socket.id,
        createOffer: false,
      });

      this.socket.emit(ACTIONS.ADD_PEER, {
        peerId: clientSocket.id,
        createOffer: true,
      });
    });

    this.shareRoomsInfo();
    this.socket.join(roomId);

    console.log(`👤 User ${userId} (${role}) joined room: ${roomId}`);

    // Inform others in the room except the joined user
    this.socket.to(roomId).emit("user-joined", { userId, role });

    // Confirm to sender they joined successfully
    this.socket.emit("joined-room", { roomId, userId, role });
  }

  getClientRooms() {
    const rooms = this.io.sockets.adapter.rooms;

    console.log(Array.from(rooms.keys()))

    return Array.from(rooms.keys()).filter(
      (roomId) => validate(roomId) && version(roomId) === 4,
    );
  }

  shareRoomsInfo() {
    this.io.emit(ACTIONS.SHARE_ROOMS, {
      rooms: this.getClientRooms(),
    });
  }

  leaveRoom() {
    const rooms = this.socket.rooms;

    Array.from(rooms)
      // LEAVE ONLY CLIENT CREATED ROOM
      .filter((roomId) => validate(roomId) && version(roomId) === 4)
      .forEach((roomId) => {
        const clients = Array.from(
          this.io.sockets.adapter.rooms.get(roomId) || [],
        );

        clients.forEach((clientID) => {
          this.io.to(clientID).emit(ACTIONS.REMOVE_PEER, {
            peerID: this.socket.id,
          });

          this.socket.emit(ACTIONS.REMOVE_PEER, {
            peerID: clientID,
          });
        });

        this.socket.leave(roomId);
      });

    this.shareRoomsInfo();
  }

  isUserJoined(joinedRooms, roomId) {
    return Array.from(joinedRooms).includes(roomId);
  }
}

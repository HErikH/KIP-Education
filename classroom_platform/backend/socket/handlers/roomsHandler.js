import { ACTIONS } from "../socketActions.js";
import { version, validate } from "uuid";

export class RoomsHandler {
  constructor(io, socket) {
    this.io = io;
    this.socket = socket;

    // this.shareRoomsInfo();
  }

  registerHandlers() {
    this.socket.on(ACTIONS.JOIN_ROOM, (data) => this.#handleJoinRoom(data));
    this.socket.on(ACTIONS.LEAVE, (data) => this.#handleLeaveRoom(data));
    this.socket.on(ACTIONS.DISCONNECTING, (data) =>
      this.#handleLeaveRoom(data),
    );
    this.socket.on(ACTIONS.RELAY_SDP, (data) => this.#handleRelaySdp(data));
    this.socket.on(ACTIONS.RELAY_ICE, (data) => this.#handleRelayIce(data));
  }

  async #handleJoinRoom({ roomId, userId, role }) {
    const joinedRooms = this.socket.rooms;

    // if (this.#isUserJoined(joinedRooms, roomId)) {
    //   return console.warn(`Already joined to the ${roomId}`);
    // }

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

  #getClientRooms() {
    const rooms = this.io.sockets.adapter.rooms;

    console.log(Array.from(rooms.keys()), "share");

    return Array.from(rooms.keys());

    // return Array.from(rooms.keys()).filter(
    //   (roomId) => validate(roomId) && version(roomId) === 4,
    // );

    // ! Id's of rooms will generated with prefix class- in the php admin panel
    // ! for avoiding including client id's

    // return Array.from(rooms.keys()).filter(
    //   (roomId) => roomId.includes("class")
    // );
  }

  shareRoomsInfo() {
    this.io.emit(ACTIONS.SHARE_ROOMS, {
      rooms: this.#getClientRooms(),
    });
  }

  #handleLeaveRoom() {
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
            peerId: this.socket.id,
          });

          this.socket.emit(ACTIONS.REMOVE_PEER, {
            peerId: clientID,
          });
        });

        this.socket.leave(roomId);
      });

    this.shareRoomsInfo();
  }

  #handleRelaySdp({ peerId, sessionDescription }) {
    this.io.to(peerId).emit(ACTIONS.SESSION_DESCRIPTION, {
      peerId: this.socket.id,
      sessionDescription,
    });
  }

  #handleRelayIce({ peerId, iceCandidate }) {
    this.io.to(peerId).emit(ACTIONS.ICE_CANDIDATE, {
      peerId: this.socket.id,
      iceCandidate,
    });
  }

  #isUserJoined(joinedRooms, roomId) {
    return Array.from(joinedRooms).includes(roomId);
  }
}

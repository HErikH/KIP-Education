import type { Socket } from "socket.io-client";
import type { T_RoomInfo } from "@/helpers/types/rooms";
import { ROOMS_HANDLER_ACTIONS as ACTIONS } from "./roomsActions";
import { useRoomsStore } from "@/store";

export class RoomsHandler {
  private socket: Socket;

  constructor(socket: Socket) {
    this.socket = socket;
  }

  registerHandlers() {
    this.socket.on(ACTIONS.JOIN_ROOM, (data) => this.handleJoinRoom(data));
    this.socket.on(ACTIONS.SHARE_ROOMS, (data) => this.handleShareRooms(data));
  }

  // * Listeners

  private handleJoinRoom({ roomId, userId, role }: T_RoomInfo) {
    useRoomsStore.getState().setRoomInfo({ roomId, userId, role });
  }

  private handleShareRooms({ rooms }: { rooms: T_RoomInfo[] }) {
    console.log(rooms)
    useRoomsStore.getState().setRooms(rooms);
  }

  // * Emitters

  joinRoom(roomInfo: { roomId: string } & Partial<Omit<T_RoomInfo, "roomId">>) {
    this.socket.emit(ACTIONS.JOIN_ROOM, roomInfo);
  }

  leaveRoom() {
    this.socket.emit(ACTIONS.LEAVE_ROOM);
  }
}

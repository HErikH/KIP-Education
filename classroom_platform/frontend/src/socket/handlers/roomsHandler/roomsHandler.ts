import type { Socket } from "socket.io-client";
import type { T_RoomInfo } from "@/helpers/types/rooms";
import { ACTIONS } from "../../socketActions";
import { useRoomsStore } from "@/store";

export class RoomsHandler {
  private socket: Socket;

  constructor(socket: Socket) {
    this.socket = socket;
    this.registerHandlers();
  }

  registerHandlers() {
    this.socket.on(ACTIONS.JOIN_ROOM, this.handleJoinRoom);
    this.socket.on(ACTIONS.SHARE_ROOMS, this.handleShareRooms);
  }

  // * Listeners

  private async handleJoinRoom({ roomId, userId, role }: T_RoomInfo) {
    useRoomsStore.getState().setRoomInfo({ roomId, userId, role });
  }

  private async handleShareRooms(props: T_RoomInfo) {
    console.log(props)
    // useRoomsStore.getState().setRoomInfo({ roomId, userId, role });
  }

  // * Emitters
}

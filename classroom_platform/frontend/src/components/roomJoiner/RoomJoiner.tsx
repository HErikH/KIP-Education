import type { T_RoomInfo } from "@/helpers/types/rooms";
import { useRoom } from "@/hooks/useRooms";
import { useEffect } from "react";
import "./style.scss";

type T_Props = {} & T_RoomInfo;

export const RoomJoiner = ({ room_id, user_id, room_name, role }: T_Props) => {
  const { isConnected, joinRoom, checkRoomStatus } = useRoom();

  // useEffect(() => {
  //   checkRoomStatus({ roomId: room_id, userId: user_id });
  // }, [room_id, user_id]);

  const handleJoinRoom = () => {
    joinRoom({ roomId: room_id, userId: user_id });
  };

  return (
    <li className="rooms__item">
      <div className="rooms__info">
        <span className="rooms__label">Room Name:</span>
        <span className="rooms__value">{room_name}</span>
      </div>
      {/* <div className="rooms__info">
        <span className="rooms__label">Room ID:</span>
        <span className="rooms__value">{class_id}</span>
      </div> */}
      <div className="rooms__info">
        <span className="rooms__label">User ID:</span>
        <span className="rooms__value">{user_id}</span>
      </div>
      <div className="rooms__info">
        <span className="rooms__label">Role:</span>
        <span className="rooms__value">{role}</span>
      </div>
      <button
        className="rooms__button"
        onClick={handleJoinRoom}
        disabled={isConnected}
      >
        Join Room
      </button>
    </li>
  );
};

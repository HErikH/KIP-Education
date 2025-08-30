import type { T_RoomInfo } from "@/helpers/types/rooms";
import { useRoom } from "@/hooks/useRooms";
import "./style.scss";

type T_Props = {} & T_RoomInfo;

export const RoomJoiner = ({ roomId, userId, role }: T_Props) => {
  const { isConnected, joinRoom } = useRoom();

  const handleJoinRoom = () => {
    joinRoom(roomId);
  };

  return (
    <li key={roomId} className="rooms__item">
      <div className="rooms__info">
        <span className="rooms__label">Room ID:</span>
        <span className="rooms__value">{roomId}</span>
      </div>
      <div className="rooms__info">
        <span className="rooms__label">User ID:</span>
        <span className="rooms__value">{userId}</span>
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
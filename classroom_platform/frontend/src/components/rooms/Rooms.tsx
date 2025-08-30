import { RoomJoiner } from "../roomJoiner/RoomJoiner";
import { Room } from "../room/Room";
import { useRoomsData, useRoomId } from "@/store/rooms/selectors";

export function Rooms() {
  const rooms = useRoomsData();
  const selectedRoomId = useRoomId();

  return !selectedRoomId ? (
    <div className="rooms">
      <h1 className="rooms__title">Available Rooms</h1>
      <ul className="rooms__list">
        {rooms.map((item) => {
          return <RoomJoiner key={item.roomId} {...item} />;
        })}
      </ul>
    </div>
  ) : (
    <Room />
  );
}
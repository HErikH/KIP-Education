import RoomJoiner from "../roomJoiner/RoomJoiner";
import Room from "../room/Room";
import { useRoomsData, useSelectedRoomId } from "@/store/rooms/selectors";

function Rooms() {
  const rooms = useRoomsData();
  const selectedRoomId = useSelectedRoomId();

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

export default Rooms;

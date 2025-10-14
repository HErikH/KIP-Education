import { RoomJoiner } from "../roomJoiner/RoomJoiner";
import { Room } from "../room/Room";
import { Chat } from "../chat/Chat";
import { useRoomsData, useRoomId } from "@/store/rooms/selectors";
import "./style.scss";

export function Rooms() {
  const rooms = useRoomsData();
  const selectedRoomId = useRoomId();

  return !selectedRoomId ? (
    <div className="rooms">
      <h1 className="rooms__title">Available Rooms</h1>
      <ul className="rooms__list">
        {rooms.length
          ? rooms.map((item) => {
              return <RoomJoiner key={item.class_id} {...item} />;
            })
          : "No rooms found !"}
      </ul>
    </div>
  ) : (
    <div className="video-chat-container">
      <Room />
      <Chat />
    </div>
  );
}

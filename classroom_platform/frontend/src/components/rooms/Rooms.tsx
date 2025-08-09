import React, { useEffect, useState } from "react";
import RoomJoiner from "../roomJoiner/RoomJoiner";
import { useRoomsData } from "@/store/rooms/selectors";

function Room() {
  const rooms = useRoomsData();

  return rooms.map((item) => {
    return <RoomJoiner key={item.roomId} {...item} />;
  });
}

export default Room;

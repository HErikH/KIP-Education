import React, { Fragment, useEffect, useState } from "react";
import { socket } from "@/socket/socketServer";
import type { T_RoomInfo } from "@/helpers/types/rooms";

type T_Props = {

} & T_RoomInfo;

const RoomJoiner = ({ roomId, userId, role }: T_Props) => {

  // useEffect(() => {
  //   const connect = () => {
  //     console.log("Current transport:", socket.io.engine.transport.name);
  //   };

  //   const onUserJoined = ({ userId, role }: any) => {
  //     console.log(`User ${userId} (${role}) joined the room.`);
  //   };

  //   const onJoinedRoom = ({ roomId, userId, role }: any) => {
  //     console.log(`Successfully joined room ${roomId} as ${role}`);
  //   };

  //   socket.emit("room:join", { roomId, userId, role });

  //   socket.on("connect", connect);

  //   socket.on("user-joined", onUserJoined);

  //   socket.on("joined-room", onJoinedRoom);

  //   return () => {
  //     socket.off("connect", connect);
  //     socket.off("user-joined", onUserJoined);
  //     socket.off("joined-room", onJoinedRoom);
  //   };
  // }, [roomId, userId, role]);

  return (
    <Fragment>
      <h1>Available rooms</h1>

      <ul>
        <li>
          <span>{roomId}</span> <br />
          <span>{userId}</span> <br />
          <span>{role}</span> <br />
          <button>Join Room</button>
        </li>
      </ul>
    </Fragment>
  );
};

export default RoomJoiner;

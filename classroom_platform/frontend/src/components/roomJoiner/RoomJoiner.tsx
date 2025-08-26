import React, { Fragment, useEffect, useState } from "react";
import { roomsHandler, socket } from "@/socket/socketServer";
import type { T_RoomInfo } from "@/helpers/types/rooms";
import { useSetSelectedRoomId } from "@/store/rooms/actions";
import "./style.scss";

type T_Props = {} & T_RoomInfo;

const RoomJoiner = ({ roomId, userId, role }: T_Props) => {
  const setSelectedRoomId = useSetSelectedRoomId();

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
        onClick={() => {
          setSelectedRoomId(roomId);
          roomsHandler.joinRoom({ roomId, userId, role });
        }}
      >
        Join Room
      </button>
    </li>
  );
};

export default RoomJoiner;

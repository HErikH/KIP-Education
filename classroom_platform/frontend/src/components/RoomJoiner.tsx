import React, { useEffect, useState } from "react";
import { socket } from "@/socket/socket";
import axios from "axios";
import { createMarkup } from "@/helpers/functions/commonFunctions";

type T_Props = {
  roomId: string;
  userId: string;
  role: "teacher" | "student";
};

const RoomJoiner = ({ roomId, userId, role }: T_Props) => {
  const [htmlContent, setHtmlContent] = useState<{ __html: string } | null>(
    null,
  );

  useEffect(() => {
    // ! Move to separate places and write strict types
    (async function () {
      const res = await axios.get("https://localhost/header.php");
      const markup = createMarkup(res.data);
      console.log(markup);
      setHtmlContent(markup);
    })();

    const connect = () => {
      console.log("Current transport:", socket.io.engine.transport.name);
    };

    const onUserJoined = ({ userId, role }: any) => {
      console.log(`User ${userId} (${role}) joined the room.`);
    };

    const onJoinedRoom = ({ roomId, userId, role }: any) => {
      console.log(`Successfully joined room ${roomId} as ${role}`);
    };

    socket.emit("room:join", { roomId, userId, role });

    socket.on("connect", connect);

    socket.on("user-joined", onUserJoined);

    socket.on("joined-room", onJoinedRoom);

    return () => {
      socket.off("connect", connect);
      socket.off("user-joined", onUserJoined);
      socket.off("joined-room", onJoinedRoom);
    };
  }, [roomId, userId, role]);

  return (
    <div>
      {htmlContent ? (
        <div dangerouslySetInnerHTML={htmlContent} />
      ) : (
        <p>Loading content...</p>
      )}
      Connected to room <strong>{roomId}</strong> as <strong>{role}</strong>
    </div>
  );
};

export default RoomJoiner;

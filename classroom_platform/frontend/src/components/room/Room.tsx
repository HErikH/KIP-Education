import { useWebRTC } from "@/hooks/useWebRTC";
import { socket } from "@/socket/socketServer";
import { useSetSelectedRoomId } from "@/store/rooms/actions";
import { useSelectedRoomId } from "@/store/rooms/selectors";
import { useEffect } from "react";
import { useLocalMediaStream } from "@/store/webRTC/selectors";
import { VideoFrame } from "../videoFrame/VideoFrame";

function Room() {
  const selectedRoomId = useSelectedRoomId();
  const { clients, provideMediaRef } = useWebRTC(selectedRoomId);
  const setSelectedRoomId = useSetSelectedRoomId();
  const localMediaStream = useLocalMediaStream();

  console.log(clients);

  useEffect(() => {
    // const connect = () => {
    //   console.log("Current transport:", socket.io.engine.transport.name);
    // };

    const onUserJoined = ({ userId, role }: any) => {
      console.log(`User ${userId} (${role}) joined the room.`);
    };

    const onJoinedRoom = ({ roomId, userId, role }: any) => {
      console.log(`Successfully joined room ${roomId} as ${role}`);
    };

    // socket.emit("room:join", { roomId, userId, role });

    // socket.on("connect", connect);

    socket.on("user-joined", onUserJoined);

    socket.on("joined-room", onJoinedRoom);

    return () => {
      // socket.off("connect", connect);
      socket.off("user-joined", onUserJoined);
      socket.off("joined-room", onJoinedRoom);
    };
  }, [localMediaStream]);

  return (
    <div>
      {clients.map((clientId) => {
        return (
          <div key={clientId}>
            <VideoFrame
              ref={(node) => provideMediaRef(clientId, node)}
              clientId={clientId}
            />
          </div>
        );
      })}

      <div onClick={() => setSelectedRoomId(null)}>Leave The Room</div>
    </div>
  );
}

export default Room;

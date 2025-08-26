import { useEffect } from "react";
import "./App.scss";
import Rooms from "@/components/rooms/Rooms";
import { onSocketConnection, socket } from "./socket/socketServer";

function App() {
  useEffect(() => {
    // * Connect socket and register handlers on mount
    onSocketConnection();

    socket.on("connect", () => {
      console.log("Client Connected !")
    })

    // ! cleanup to close/reconnect sockets
    // return () => {
    //   socket.disconnect();
    // };
  }, []);

  return (
    <>
      <Rooms />
    </>
  );
}

export default App;

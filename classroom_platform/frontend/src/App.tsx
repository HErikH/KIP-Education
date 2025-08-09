import { useEffect } from "react";
import "./App.scss";
import Room from "@/components/rooms/Rooms";
import { onSocketConnection, socket } from "./socket/socketServer";

function App() {
  useEffect(() => {
    // Connect socket and register handlers on mount
    onSocketConnection();

    socket.on("connect", () => {
      console.log("Client Connected !")
    })

    // // Optional: cleanup if you need to close/reconnect sockets
    // return () => {
    //   socket.disconnect();
    // };
  }, []);

  return (
    <>
      <Room />
    </>
  );
}

export default App;

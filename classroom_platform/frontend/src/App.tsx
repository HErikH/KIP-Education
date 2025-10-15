import { useEffect, useState } from "react";
import "./App.scss";
import { Rooms } from "@/components/rooms/Rooms";
import { onSocketConnection, socket } from "./socket/socketServer";
import { useFetchRooms } from "@/store/rooms/actions";
import { Loader } from "./components/ui/loader/Loader";
import 'react-responsive-modal/styles.css';

function App() {
  const fetchRooms = useFetchRooms();
  const [isLoading, setLoading] = useState(true);

  useEffect(() => {
    (async () => {
      const pathSegments = window.location.pathname.split("/");

      if (pathSegments.length > 1 && pathSegments[1]) {
        await fetchRooms(Number(pathSegments[1]));
      }

      setLoading(false);
    })();

    // * Connect socket and register handlers on mount
    onSocketConnection();

    socket.on("connect", () => {
      console.log("Client Connected !");
    });

    // ! cleanup to close/reconnect sockets
    // return () => {
    //   socket.disconnect();
    // };
  }, []);

  return isLoading ? (
    <Loader />
  ) : (
    <>
      <Rooms />
    </>
  );
}

export default App;

import express from "express";
import { PORT } from "./config/envConfig.js";
import { createServer } from "http";
import { socketServer, onSocketConnection } from "./socket/socketServer.js";
import { indexRouter } from "./routes/index.js";
import { RoomsHandler } from "./socket/handlers/roomsHandler/roomsHandler.js";
import helmet from "helmet";
import cors from "cors";
import { redisManager } from "./config/redisConfig.js";
import path from "path";
import { ROOT_DIR } from "./config/rootDir.js";

const app = express();
const server = createServer(app);
const io = socketServer(server);

app.use(
  cors({
    // ! Change to the Prod origin get from the .env
    origin: "*",
    credentials: true,
  }),
);
app.use(
  helmet({
    crossOriginResourcePolicy: { policy: "cross-origin" },
  }),
);
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

app.use("/uploads", express.static(path.join(ROOT_DIR, "/uploads")));

// * Connect apis
app.use("/", indexRouter);

// *Connect to Redis
await redisManager.connect();

// *Initialize mediaSoup
await RoomsHandler.initMediaSoup();

// * Connect socket
io.on("connection", onSocketConnection(io));

app.all("*", (req, res) => {
  return res.status(404).json({ message: "Not Found" });
});

app.use(function (error, req, res, next) {
  res.status(error.status || 500);

  return res.json({ error });
});

process.on("SIGINT", async () => {
  console.log("🛑 Shutting down server...");

  await redisManager.disconnect();
  process.exit(0);
});

server.listen(PORT, () => {
  console.log(`Connected to the ${PORT} !`);
});

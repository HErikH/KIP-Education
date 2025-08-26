import express from "express";
import { PORT } from "./config/envConfig.js";
import { createServer } from "http";
import { socketServer, onSocketConnection } from "./socket/socketServer.js";
import { indexRouter } from "./routes/index.js";
import helmet from "helmet";
import cors from "cors";

const app = express();
const server = createServer(app);
const io = socketServer(server);

app.use(
  cors({
    // ! Change to the Prod origin
    origin: "*",
    credentials: true,
  }),
);
app.use(helmet());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// * Connect apis
app.use("/", indexRouter);

// * Connect socket
io.on("connection", onSocketConnection(io));

app.all("*", (req, res) => {
  return res.status(404).json({ message: "Not Found" });
});

app.use(function (error, req, res, next) {
  res.status(error.status || 500);

  return res.json({ error });
});

server.listen(PORT, () => {
  console.log(`Connected to the ${PORT} !`);
});

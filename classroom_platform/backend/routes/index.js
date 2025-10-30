import { Router } from "express";
import { roomsRouter } from "./roomsRouter.js";
import { whiteboardRouter } from "./whiteboardRouter.js";
import { lessonsRouter } from "./lessonsRouter.js";

const indexRouter = new Router();

indexRouter.use("/rooms", roomsRouter);
indexRouter.use("/rooms", whiteboardRouter);
indexRouter.use("/lessons", lessonsRouter);

export { indexRouter };
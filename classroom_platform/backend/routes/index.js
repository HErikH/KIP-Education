import { Router } from "express";
import { roomsRouter } from "./roomsRouter.js";
import { whiteboardRouter } from "./whitebourdRouter.js";

const indexRouter = new Router();

indexRouter.use("/rooms", roomsRouter);
indexRouter.use("/rooms", whiteboardRouter);

export { indexRouter };
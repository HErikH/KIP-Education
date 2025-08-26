import { Router } from "express";
import { roomsRouter } from "./roomsRouter.js";

const indexRouter = new Router();

indexRouter.use("/rooms", roomsRouter);

export { indexRouter };
import { Router } from "express";

const roomsRouter = Router();

// * Add controller
roomsRouter.get("/get");

export { roomsRouter };
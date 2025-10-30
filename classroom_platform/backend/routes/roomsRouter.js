import { Router } from "express";
import { RoomsController } from "../controllers/roomsController.js";

const roomsRouter = Router();

roomsRouter.get("/get/:userId", RoomsController.getUserRooms);

export { roomsRouter };
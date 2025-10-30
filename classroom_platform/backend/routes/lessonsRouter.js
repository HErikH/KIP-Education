import { Router } from "express";
import { LessonsController } from "../controllers/lessonsController.js";

const lessonsRouter = Router();

lessonsRouter.get("/get", LessonsController.getLessons);

export { lessonsRouter };

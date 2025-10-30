import { LessonsService } from "../services/lessonsService.js";

export class LessonsController {
  static async getLessons(req, res) {
    try {
      const structure = await LessonsService.getLessonsStructure();
      
      res.json({ tags: structure });
    } catch (err) {
      console.error(err);
      res.status(500).json({ error: "Failed to fetch lessons" });
    }
  }
}

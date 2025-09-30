import { RoomsService } from "../services/roomsService.js";

export class RoomsController {
  static async getUserRooms(req, res, next) {
    try {
      const { userId } = req.params;

      const rooms = await RoomsService.getUserRooms(userId);

      return res.json(rooms);
    } catch (error) {
      console.log(error);
      return res.status(500).json({ message: "Something went wrong" });
    }
  }
}

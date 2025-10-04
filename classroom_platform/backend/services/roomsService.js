import {
  RoomsModel,
  UsersModel,
  RoomEnrollmentsModel,
} from "../models/index.js";

export class RoomsService {
  static async getUserRooms(userId) {
    const user = await UsersModel.findByPk(userId);

    if (!user) throw new Error("User not found");

    if (user.role === "teacher") {
      // Teacher → fetch rooms where teacherId = userId
      return await RoomsModel.findByTeacher(userId, user.role);
    }

    if (user.role === "student") {
      // Student → fetch rooms via enrollments
      return await RoomEnrollmentsModel.findByStudent(userId, user.role);
    }

    return [];
  }
}

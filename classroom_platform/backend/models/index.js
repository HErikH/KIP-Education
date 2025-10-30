// database.js or models/index.js - called ONCE on app startup
import { RoomsModel } from "./roomsModel.js";
import { UsersModel } from "./usersModel.js";
import { RoomEnrollmentsModel } from "./roomEnrollmentsModel.js";
import { WhiteboardFileModel } from "./whiteboardFilesModel.js";
import { LessonsModel } from "./lessonsModel.js";

const models = {
  RoomsModel,
  UsersModel,
  RoomEnrollmentsModel,
  WhiteboardFileModel,
  LessonsModel,
};

// Associate all models once
Object.values(models).forEach((model) => {
  if (model.associate) model.associate(models);
});

export {
  RoomsModel,
  UsersModel,
  RoomEnrollmentsModel,
  WhiteboardFileModel,
  LessonsModel,
};
// database.js or models/index.js - called ONCE on app startup
import { RoomsModel } from "./roomsModel.js";
import { UsersModel } from "./usersModel.js";
import { RoomEnrollmentsModel } from "./roomEnrollmentsModel.js";

const models = { RoomsModel, UsersModel, RoomEnrollmentsModel };

// Associate all models once
Object.values(models).forEach((model) => {
  if (model.associate) model.associate(models);
});

export { RoomsModel, UsersModel, RoomEnrollmentsModel };
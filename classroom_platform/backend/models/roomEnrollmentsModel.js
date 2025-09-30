import { DataTypes, Model, Sequelize } from "sequelize";
import { DB_CONNECT } from "../services/dbConnect.js";
import { RoomsModel, UsersModel } from "./index.js";

export class RoomEnrollmentsModel extends Model {
  static associate(models) {
    RoomEnrollmentsModel.belongsTo(models.RoomsModel, {
      foreignKey: "room_id",
      as: "room",
    });
    RoomEnrollmentsModel.belongsTo(models.UsersModel, {
      foreignKey: "student_id",
      as: "student",
    });
  }

  static async findByStudent(studentId) {
    return await RoomEnrollmentsModel.findAll({
      where: { student_id: studentId },
      include: [
        {
          model: RoomsModel,
          as: "room",
          include: [{ model: UsersModel, as: "teacher" }],
        },
        { model: UsersModel, as: "student" },
      ],
    });
  }
}

RoomEnrollmentsModel.init(
  {
    enrollment_id: {
      autoIncrement: true,
      type: DataTypes.INTEGER,
      allowNull: false,
      primaryKey: true,
    },
    room_id: {
      type: DataTypes.INTEGER,
      allowNull: false,
      references: {
        model: "rooms",
        key: "room_id",
      },
    },
    student_id: {
      type: DataTypes.INTEGER,
      allowNull: false,
      references: {
        model: "users",
        key: "id",
      },
    },
    enrolled_at: {
      type: DataTypes.DATE,
      allowNull: false,
      defaultValue: Sequelize.Sequelize.fn("current_timestamp"),
    },
    status: {
      type: DataTypes.ENUM("active", "removed"),
      allowNull: false,
      defaultValue: "active",
    },
  },
  {
    sequelize: DB_CONNECT,
    tableName: "room_enrollments",
    timestamps: false,
  },
);

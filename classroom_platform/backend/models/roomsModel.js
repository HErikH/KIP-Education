import { DataTypes, Model } from "sequelize";
import { DB_CONNECT } from "../services/dbConnect.js";
import { UsersModel } from "./index.js";

// * Command for getting models fro db
// * sequelize-auto -h localhost -d admin12345_school -u root -x root -p 3306 --dialect mysql -o ./models/roomsModel.js -t rooms

export class RoomsModel extends Model {
  static associate(models) {
    this.belongsTo(models.UsersModel, {
      foreignKey: "teacher_id",
      as: "teacher",
    });
  }

  static async findByTeacher(teacherId, role) {
    const rooms = await this.findAll({
      where: { teacher_id: teacherId },
      // include: [{ model: UsersModel, as: "teacher" }],
      attributes: [
        "room_id",
        ["teacher_id", "user_id"],
        "class_id",
        "room_name",
      ],
    });

    return rooms.map((room) => ({
      ...room.get({ plain: true }),
      role,
    }));
  }
}

RoomsModel.init(
  {
    room_id: {
      autoIncrement: true,
      type: DataTypes.INTEGER,
      allowNull: false,
      primaryKey: true,
    },
    class_id: {
      type: DataTypes.STRING(50),
      allowNull: false,
      unique: "class_id",
    },
    room_name: {
      type: DataTypes.STRING(255),
      allowNull: false,
    },
    description: {
      type: DataTypes.TEXT,
      allowNull: true,
    },
    teacher_id: {
      type: DataTypes.INTEGER,
      allowNull: false,
      references: {
        model: "users",
        key: "id",
      },
    },
    max_students: {
      type: DataTypes.INTEGER,
      allowNull: false,
      defaultValue: 30,
    },
    created_at: {
      type: DataTypes.DATE,
      allowNull: false,
    },
    expires_at: {
      type: DataTypes.DATE,
      allowNull: true,
    },
    status: {
      type: DataTypes.ENUM("active", "inactive", "expired"),
      allowNull: false,
      defaultValue: "active",
    },
  },
  {
    sequelize: DB_CONNECT,
    tableName: "rooms",
    timestamps: false,
  },
);

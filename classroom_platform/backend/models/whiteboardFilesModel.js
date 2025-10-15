import { Model, DataTypes, where } from "sequelize";
import { DB_CONNECT } from "../services/dbConnect.js";

export class WhiteboardFileModel extends Model {
  static associate(models) {
    WhiteboardFileModel.belongsTo(models.RoomsModel, {
      foreignKey: "room_id",
      as: "room",
    });
  }

  static createFile = async (data) => await this.create(data);

  static getFilesByRoom = async (room_id) => {
    return await this.findAll({
      where: { room_id },
      order: [["created_at", "DESC"]],
      raw: true
    });
  };

  static getFileById = async (fileId) => await this.findByPk(fileId);

  static deleteFileById = async (fileId) =>
    await this.destroy({ where: { id: fileId } });
}

WhiteboardFileModel.init(
  {
    id: {
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
    name: {
      type: DataTypes.STRING,
      allowNull: false,
    },
    url: {
      type: DataTypes.STRING,
      allowNull: false,
    },
    type: {
      type: DataTypes.STRING,
      allowNull: false,
    },
    size: {
      type: DataTypes.INTEGER,
      allowNull: false,
    },
    width: {
      type: DataTypes.INTEGER,
    },
    height: {
      type: DataTypes.INTEGER,
    },
    uploaded_by: {
      type: DataTypes.STRING,
      allowNull: false,
    },
  },
  {
    sequelize: DB_CONNECT,
    timestamps: false,
    tableName: "whiteboard_files",
  },
);

import { DataTypes, Model, Sequelize } from "sequelize";
import { DB_CONNECT } from "../services/dbConnect.js";

export class LessonsModel extends Model {
  static async getAllLessons() {
    return await this.findAll({ raw: true });
  }
}

LessonsModel.init(
  {
    id: {
      autoIncrement: true,
      type: DataTypes.INTEGER,
      allowNull: false,
      primaryKey: true,
    },
    title: {
      type: DataTypes.STRING(255),
      allowNull: true,
    },
    tag: {
      type: DataTypes.STRING(255),
      allowNull: true,
    },
    files: {
      type: DataTypes.TEXT,
      allowNull: true,
    },
    date_created: {
      type: DataTypes.DATE,
      allowNull: false,
      defaultValue: Sequelize.Sequelize.fn("current_timestamp"),
    },
    program_name: {
      type: DataTypes.STRING(255),
      allowNull: false,
    },
  },
  {
    sequelize: DB_CONNECT,
    tableName: "lessons",
    timestamps: false,
  },
);

import { DataTypes, Model, Sequelize } from "sequelize";
import { DB_CONNECT } from "../services/dbConnect.js";

export class UsersModel extends Model {}

UsersModel.init(
  {
    id: {
      autoIncrement: true,
      type: DataTypes.INTEGER,
      allowNull: false,
      primaryKey: true,
    },
    email: {
      type: DataTypes.STRING(255),
      allowNull: false,
    },
    password: {
      type: DataTypes.STRING(255),
      allowNull: false,
    },
    first_last_name: {
      type: DataTypes.STRING(255),
      allowNull: false,
    },
    phone_number: {
      type: DataTypes.STRING(20),
      allowNull: true,
    },
    date_register: {
      type: DataTypes.DATE,
      allowNull: true,
      defaultValue: Sequelize.Sequelize.fn("current_timestamp"),
    },
    role: {
      type: DataTypes.ENUM("guest", "teacher", "student"),
      allowNull: true,
      defaultValue: "guest",
    },
    date_start_role: {
      type: DataTypes.DATE,
      allowNull: true,
    },
    date_end_role: {
      type: DataTypes.DATE,
      allowNull: true,
    },
    blocked: {
      type: DataTypes.ENUM("Yes", "No"),
      allowNull: true,
      defaultValue: "No",
    },
    time_left: {
      type: DataTypes.INTEGER,
      allowNull: true,
    },
    product_name: {
      type: DataTypes.STRING(255),
      allowNull: true,
    },
    product_id: {
      type: DataTypes.INTEGER,
      allowNull: true,
    },
    balance: {
      type: DataTypes.DECIMAL(10, 2),
      allowNull: false,
      defaultValue: 0.0,
    },
    ip_address: {
      type: DataTypes.STRING(45),
      allowNull: true,
    },
    country: {
      type: DataTypes.STRING(255),
      allowNull: true,
      defaultValue: "Armenia",
    },
    session_id: {
      type: DataTypes.STRING(255),
      allowNull: true,
    },
    bought_program_names: {
      type: DataTypes.TEXT,
      allowNull: false,
    },
  },
  {
    sequelize: DB_CONNECT,
    tableName: "users",
    timestamps: false,
  },
);

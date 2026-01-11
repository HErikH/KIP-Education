import { Sequelize } from "sequelize";
import {
  DB_HOST,
  DB_PORT,
  DB_USERNAME,
  DB_PASSWORD,
  DB_NAME,
} from "../config/envConfig.js";

let DB_CONNECT;

(async () => {
  try {
    DB_CONNECT = new Sequelize(DB_NAME, DB_USERNAME, DB_PASSWORD, {
      host: DB_HOST,
      port: DB_PORT,
      logging: false,
      dialect: "mysql",
    });

    await DB_CONNECT.authenticate();

    console.log("✅ Database connected");
  } catch (err) {
    console.error("❌ Connection failed:", err);
    throw err;
  }
})();

export { DB_CONNECT };
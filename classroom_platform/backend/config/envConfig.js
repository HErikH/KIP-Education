import { config } from "dotenv";

config({ path: `.env.${process.env.NODE_ENV || "development"}.local` });

// * Import/Export env's here for whole application
export const {
  PORT,
  NODE_ENV,
  MEDIASOUP_ANNOUNCED_IP = "127.0.0.1",
  REDIS_HOST,
  REDIS_PORT,
  REDIS_PASSWORD = undefined,
  DB_HOST,
  DB_PORT,
  DB_USERNAME,
  DB_PASSWORD,
  DB_NAME,
  MEDIA_BASE_URL,
  UPLOAD_DIR,
} = process.env;

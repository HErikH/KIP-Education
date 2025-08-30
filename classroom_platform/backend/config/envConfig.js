import { config } from "dotenv";

config({ path: `.env.${process.env.NODE_ENV || "development"}.local` });

// * Import/Export env's here for whole application
export const { PORT, NODE_ENV, MEDIASOUP_ANNOUNCED_IP = "127.0.0.1" } = process.env;
import multer from "multer";
import path from "path";
import { v4 as uuidv4 } from "uuid";
import { ROOT_DIR } from "../config/rootDir.js";
import {
  ALLOWED_EXTENSIONS_TYPES,
  FILE_TYPES,
} from "../helpers/constants/whiteboard.js";

const uploadPath = path.join(ROOT_DIR, "/uploads/whiteboard");

const storage = multer.diskStorage({
  destination: (req, file, cb) => cb(null, uploadPath),
  filename: (req, file, cb) =>
    cb(null, `${uuidv4()}${path.extname(file.originalname)}`),
});

const fileFilter = (req, file, cb) => {
  const extname = ALLOWED_EXTENSIONS_TYPES.test(
    path.extname(file.originalname).toLowerCase(),
  );
  const mimetype =
    file.mimetype.startsWith("image/") ||
    Object.values(FILE_TYPES).includes(file.mimetype);

  if (extname && mimetype) cb(null, true);
  else
    cb(
      new Error(
        "Only images (JPEG, PNG ) and (PDF) files are allowed",
      ),
    );
};

export const upload = multer({
  storage,
  limits: { fileSize: 10 * 1024 * 1024 },
  fileFilter,
});

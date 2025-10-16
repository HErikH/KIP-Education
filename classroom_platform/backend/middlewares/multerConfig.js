import multer from "multer";
import path from "path";
import { v4 as uuidv4 } from "uuid";
import { ROOT_DIR } from "../config/rootDir.js";

const uploadPath = path.join(ROOT_DIR, "/uploads/whiteboard");

const storage = multer.diskStorage({
  destination: (req, file, cb) => cb(null, uploadPath),
  filename: (req, file, cb) =>
    cb(null, `${uuidv4()}${path.extname(file.originalname)}`),
});

const fileFilter = (req, file, cb) => {
  const allowed = /jpeg|jpg|png|gif|pdf|docx/;
  const extname = allowed.test(path.extname(file.originalname).toLowerCase());
  const mimetype = allowed.test(file.mimetype);

  if (extname && mimetype) cb(null, true);
  else cb(new Error("Only images (JPEG, PNG, GIF) and (PDF, DOCX) files are allowed"));
};

export const upload = multer({
  storage,
  limits: { fileSize: 10 * 1024 * 1024 },
  fileFilter,
});

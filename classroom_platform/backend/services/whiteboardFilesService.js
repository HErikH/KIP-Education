import sharp from "sharp";
import path from "path";
import fs from "fs";
import { WhiteboardFileModel } from "../models/whiteboardFilesModel.js";
import { UPLOAD_DIR } from "../config/envConfig.js";
import { FILE_TYPES } from "../helpers/constants/whiteboard.js";
import { ROOT_DIR } from "../config/rootDir.js";

export class WhiteboardFilesService {
  static async uploadFile(room_id, file, uploaded_by) {
    let width = null;
    let height = null;
    let fileType = "image";

    if (file.mimetype.startsWith(FILE_TYPES.img)) {
      const metadata = await sharp(file.path).metadata();

      width = metadata.width;
      height = metadata.height;
    } else if (file.mimetype === FILE_TYPES.pdf) {
      fileType = "pdf";
      width = 800;
      height = 1000;
    } else if (file.mimetype === FILE_TYPES.doc) {
      fileType = "docx";
      width = 800;
      height = 1000;
    } else if (file.mimetype === FILE_TYPES.pptx) {
      fileType = "pptx";
      width = 800;
      height = 1000;
    }

    return await WhiteboardFileModel.createFile({
      room_id,
      name: file.originalname,
      url: `${UPLOAD_DIR}/uploads/whiteboard/${file.filename}`,
      type: fileType,
      size: file.size,
      width,
      height,
      uploaded_by,
    });
  }

  static async getFilesForRoom(roomId) {
    return await WhiteboardFileModel.getFilesByRoom(roomId);
  }

  static async deleteFile(fileId) {
    const file = await WhiteboardFileModel.getFileById(fileId);

    if (!file) throw new Error("File not found");

    const filePath = path.join(ROOT_DIR, file.url);

    if (fs.existsSync(filePath)) fs.unlinkSync(filePath);

    await file.destroy();
    return true;
  }
}

import sharp from "sharp";
import path from "path";
import fs from "fs";
import { WhiteboardFileModel } from "../models/index.js";
import { UPLOAD_DIR } from "../config/envConfig.js";
import { FILE_TYPES } from "../helpers/constants/whiteboard.js";
import { ROOT_DIR } from "../config/rootDir.js";
import { convertPdfToImages } from "../helpers/functions/pdfToImage.js";

export class WhiteboardFilesService {
  static async uploadFile(room_id, file, uploaded_by) {
    let width = null;
    let height = null;
    let fileType = "image";
    let url = `${UPLOAD_DIR}/uploads/whiteboard/${file.filename}`;

    if (file.mimetype.startsWith(FILE_TYPES.img)) {
      const metadata = await sharp(file.path).metadata();

      width = metadata.width;
      height = metadata.height;
    } else if (file.mimetype === FILE_TYPES.pdf) {
      fileType = "pdf";

      // convert PDF pages to images
      const pdfOutputDir = path.join(
        path.dirname(file.path),
        // `${path.basename(file.filename, path.extname(file.filename))}_pages`,
      );

      const images = await convertPdfToImages(file.path, pdfOutputDir);
      if (images.length > 0) {
        // Use first page as the preview / background for whiteboard
        url = `/uploads/whiteboard/${path.basename(images[0])}`;
      }

      width = 800;
      height = 1000;
    }

    return await WhiteboardFileModel.createFile({
      room_id,
      name: file.originalname,
      url,
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

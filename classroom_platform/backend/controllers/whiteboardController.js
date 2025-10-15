import { WhiteboardFilesService } from "../services/whiteboardFilesService.js";
import { MEDIA_BASE_URL } from "../config/envConfig.js";

export class WhiteboardFilesController {
  static async uploadFile(req, res) {
    try {
      const { roomId } = req.params;
      const { uploadedBy } = req.body;

      if (!req.file)
        return res
          .status(400)
          .json({ success: false, message: "No file uploaded" });

      const fileRecord = await WhiteboardFilesService.uploadFile(
        roomId,
        req.file,
        uploadedBy,
      );

      res.status(201).json({
        success: true,
        data: {
          id: fileRecord.id,
          name: fileRecord.name,
          url: MEDIA_BASE_URL + fileRecord.url,
          type: fileRecord.type,
          width: fileRecord.width,
          height: fileRecord.height,
          uploadedBy: fileRecord.uploadedBy,
        //   uploadedAt: fileRecord.createdAt.getTime(),
        },
      });
    } catch (error) {
      console.error("Error uploading file:", error);
      res.status(500).json({
        success: false,
        message: error.message || "Failed to upload file",
      });
    }
  }

  static async getFiles(req, res) {
    try {
      const { roomId } = req.params;
      const files = await WhiteboardFilesService.getFilesForRoom(roomId);

      res.json({
        success: true,
        data: files.map((f) => ({
          id: f.id,
          name: f.name,
          url: MEDIA_BASE_URL + f.url,
          type: f.type,
          width: f.width,
          height: f.height,
          uploadedBy: f.uploadedBy,
          uploadedAt: f.createdAt.getTime(),
        })),
      });
    } catch (error) {
      console.error("Error fetching files:", error);
      res
        .status(500)
        .json({ success: false, message: "Failed to fetch files" });
    }
  }

  static async deleteFile(req, res) {
    try {
      const { fileId } = req.params;

      await WhiteboardFilesService.deleteFile(fileId);

      res.json({ success: true, message: "File deleted successfully" });
    } catch (error) {
      console.error("Error deleting file:", error);

      res.status(500).json({
        success: false,
        message: error.message || "Failed to delete file",
      });
    }
  }
}

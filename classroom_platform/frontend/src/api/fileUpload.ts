import { whiteboardHandler } from "@/socket/socketServer";
import axios from "axios";

export async function handleFileUpload(
  file: File,
  roomId: string | null,
  userId: number | null,
) {
  try {
    const formData = new FormData();

    formData.append("file", file);
    formData.append("uploadedBy", String(userId));

    const response = await axios.post(
      `${import.meta.env.VITE_BACK_END_PORT}/rooms/${roomId}/upload`,
      formData
    );

    if (response.status === 500 || !roomId) {
      throw new Error("Upload failed");
    }

    const data = response.data;

    // Emit file uploaded event via socket
    whiteboardHandler.emitFileUploaded(roomId, data);

    console.log("✅ File uploaded successfully");
  } catch (error) {
    console.error("Failed to upload file:", error);
    throw error;
  }
}

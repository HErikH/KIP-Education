import React, { useCallback, useRef, useState } from "react";
import {
  useWhiteboardCurrentFile,
  useWhiteboardUploadedFiles,
} from "@/store/whiteboard/selectors";
import { isValidFileType } from "@/helpers/functions/whiteboard";
import { FiUpload, FiFile, FiX } from "react-icons/fi";
import clsx from "clsx";
import { handleFileUpload } from "@/api/fileUpload";
import { useRoomId, useUserId } from "@/store/rooms/selectors";
import { ACCEPT_FILES_TYPES } from "@/helpers/constants/whiteboard";
import { FaTrashAlt } from "react-icons/fa";
import { whiteboardHandler } from "@/socket/socketServer";
import "./style.scss";
import { FolderTree } from "../folderTree/FolderTree";

export function FileUpload() {
  const [isDragging, setIsDragging] = useState(false);
  const [isUploading, setIsUploading] = useState(false);
  const [uploadProgress, setUploadProgress] = useState(0);
  const currentFile = useWhiteboardCurrentFile();
  const uploadedFiles = useWhiteboardUploadedFiles();
  const roomId = useRoomId();
  const userId = useUserId();
  const whiteboardHandlerRef = useRef<typeof whiteboardHandler | null>(
    whiteboardHandler,
  );

  const handleDragOver = useCallback((e: React.DragEvent) => {
    e.preventDefault();
    setIsDragging(true);
  }, []);

  const handleDragLeave = useCallback((e: React.DragEvent) => {
    e.preventDefault();
    setIsDragging(false);
  }, []);

  const handleDrop = useCallback(
    async (e: React.DragEvent) => {
      e.preventDefault();
      setIsDragging(false);

      const files = Array.from(e.dataTransfer.files);
      const validFile = files.find((file) => isValidFileType(file));

      if (validFile) {
        await uploadFile(validFile);
      } else {
        alert("Please upload an image or PDF file.");
      }
    },
    [handleFileUpload],
  );

  const handleFileInput = useCallback(
    async (e: React.ChangeEvent<HTMLInputElement>) => {
      const files = e.target.files;
      if (!files || files.length === 0) return;

      const file = files[0];
      if (isValidFileType(file)) {
        await uploadFile(file);
      } else {
        alert("Please upload an image or PDF file.");
      }

      e.target.value = "";
    },
    [handleFileUpload],
  );

  const uploadFile = async (file: File) => {
    setIsUploading(true);
    setUploadProgress(0);

    try {
      // Simulate progress (in real implementation, track actual upload progress)
      const progressInterval = setInterval(() => {
        setUploadProgress((prev) => {
          if (prev >= 90) {
            clearInterval(progressInterval);
            return 90;
          }
          return prev + 10;
        });
      }, 100);

      await handleFileUpload(file, roomId, userId);

      clearInterval(progressInterval);
      setUploadProgress(100);

      setTimeout(() => {
        setIsUploading(false);
        setUploadProgress(0);
      }, 500);
    } catch (error) {
      console.error("Upload failed:", error);
      alert("Failed to upload file. Please try again.");
      setIsUploading(false);
      setUploadProgress(0);
    }
  };

  const handleDelete = (
    e: React.MouseEvent<SVGElement, MouseEvent>,
    fileId: string,
  ) => {
    e.stopPropagation();
    if (
      window.confirm(
        "Are you sure you want to delete the file? This action cannot be undone.",
      ) &&
      whiteboardHandlerRef.current &&
      roomId
    ) {
      whiteboardHandlerRef.current.emitDeleteFile({ roomId, fileId });
    }
  };

  return (
    <div className="file-upload">
      <div
        className={clsx("file-upload__dropzone", {
          "file-upload__dropzone--dragging": isDragging,
        })}
        onDragOver={handleDragOver}
        onDragLeave={handleDragLeave}
        onDrop={handleDrop}
      >
        <input
          type="file"
          id="file-input"
          className="file-upload__input"
          accept={ACCEPT_FILES_TYPES}
          onChange={handleFileInput}
          disabled={isUploading}
        />

        <label htmlFor="file-input" className="file-upload__label">
          <FiUpload size={48} />
          <span className="file-upload__text">
            {isUploading
              ? `Uploading... ${uploadProgress}%`
              : "Click to upload or drag and drop"}
          </span>
          <span className="file-upload__hint">
            Supports: Images (PNG, JPG) and PDF files
          </span>
        </label>

        {isUploading && (
          <div className="file-upload__progress">
            <div
              className="file-upload__progress-bar"
              style={{ width: `${uploadProgress}%` }}
            />
          </div>
        )}
      </div>

      {currentFile && (
        <div className="file-upload__current">
          <div className="file-upload__current-info">
            <FiFile size={20} />
            <span className="file-upload__current-name">
              {currentFile.name}
            </span>
          </div>

          <button
            className="file-upload__remove"
            onClick={() =>
              whiteboardHandlerRef.current?.emitRemoveFile({ roomId: roomId! })
            }
            title="Remove file"
          >
            <FiX size={16} />
          </button>
        </div>
      )}

      {uploadedFiles.length > 0 && (
        <div className="file-upload__history">
          <h3 className="file-upload__history-title">Previously Uploaded</h3>
          <div className="file-upload__history-list">
            {uploadedFiles.map((file) => (
              <button
                key={file.id}
                className={clsx("file-upload__history-item", {
                  "file-upload__history-item--active":
                    currentFile?.id === file.id,
                })}
                onClick={() =>
                  whiteboardHandlerRef.current?.emitCurrentFile({
                    roomId: roomId!,
                    file,
                  })
                }
              >
                <div className="file-upload__current-info">
                  <FiFile size={16} />
                  <span>{file.name}</span>
                </div>
                <FaTrashAlt
                  className="file-upload__delete"
                  onClick={(e) => handleDelete(e, file.id)}
                  title="Delete file"
                  size={12}
                />
              </button>
            ))}
          </div>
        </div>
      )}

      <FolderTree uploadFile={uploadFile} />
    </div>
  );
}

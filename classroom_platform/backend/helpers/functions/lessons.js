import path from "path";

const skipFileTypes = ["mp3", "wav", "mp4", "mov", "avi", "docx", "pptx"];

export function filterFiles(files) {
  return files.filter((f) => {
    const ext = path
      .extname(f.file || f)
      .slice(1)
      .toLowerCase();

    return !skipFileTypes.includes(ext);
  });
}

export function normalizeFiles(files) {
  if (!files) return [];

  try {
    const parsed = typeof files === "string" ? JSON.parse(files) : files;
    if (!Array.isArray(parsed)) return [];

    // Handle array of objects or array of paths
    return parsed.map((f) =>
      typeof f === "string"
        ? { title: path.basename(f), file: f }
        : { title: f.title || path.basename(f.file), file: f.file },
    );
  } catch {
    return [];
  }
}

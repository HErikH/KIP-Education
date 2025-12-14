<?php
include "db_connect.php";

// === CONFIG ===
// Local folder to scan
$mediaDir =
  "../../../Downloads/TA1_A2/7. Emotional Intelligence/5. Setting Personal Goals";
$prefixNum = "337";
$dbPathPrefix = "/uploads/lessons/$prefixNum"; // path to save in DB
$destDir = "../uploads/lessons" . DIRECTORY_SEPARATOR . $prefixNum;

$fullPath = realpath($mediaDir);
if (!$fullPath || !is_dir($fullPath)) {
  exit("❌ Directory not found: $mediaDir");
}

// === PARSE PATH ===
// Extract parts from path: Program → Tag → Lesson
$parts = explode(DIRECTORY_SEPARATOR, $fullPath);
$count = count($parts);
$lessonFolder = $parts[$count - 1]; // e.g. '1. Present simple'
$tagFolder = $parts[$count - 2]; // e.g. '1. Grammar'
$program_name = $parts[$count - 3]; // e.g. 'TA1_A1'

// Clean tag (remove numbers and dots)
$tag = preg_replace("/^\d+\.?\s*/", "", $tagFolder);

// Clean lesson title (remove numbers and dots)
$lessonName = preg_replace("/^\d+\.?\s*/", "", $lessonFolder);

// === GET LESSON NUMBER FOR PROGRAM ===
$stmt = $conn->prepare(
  "SELECT COUNT(*) AS lesson_count FROM lessons WHERE program_name = ?"
);
$stmt->bind_param("s", $program_name);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$lessonNumber = $row && $row["lesson_count"] ? $row["lesson_count"] + 1 : 1;
$stmt->close();

// Build final title: Lesson <num>_<Tag>_<LessonName>
$cleanTag = str_replace(" ", "_", $tag);
$cleanLesson = str_replace(" ", "_", $lessonName);
$title = "Lesson {$lessonNumber}_{$cleanTag}_{$cleanLesson}";

// === FILE SCAN ===
$allFiles = scandir($fullPath);

$videoFile = null;
$smallVideos = [];
$docs = [];

foreach ($allFiles as $file) {
  if ($file === "." || $file === "..") {
    continue;
  }
  $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

  // Build path for DB
  $relativePath = "$dbPathPrefix/$file";

  // Choose main video if not already picked
  if (!$videoFile && $ext === "mp4") {
    $videoFile = $relativePath;
    continue;
  }

  // Small videos (mp4/mp3), excluding the main video
  if (in_array($ext, ["mp4", "mp3"])) {
    $smallVideos[] = $relativePath;
  }

  // Docs (pdf, ppt, doc, etc.)
  if (in_array($ext, ["doc", "docx", "ppt", "pptx", "pdf"])) {
    $docs[] = $relativePath;
  }
}

// Remove main video from small videos if present
$smallVideos = array_values(
  array_filter($smallVideos, fn($v) => $v !== $videoFile)
);

// === PREPARE DATA ===
$video = $videoFile ?: "";
$smallVideosJson = json_encode($smallVideos);
$filesJson = json_encode($docs);
$dateCreated = date("Y-m-d H:i:s");

// === INSERT INTO DB ===
// id column is AUTO_INCREMENT
$sql = "INSERT INTO lessons 
    (title, tag, video, small_videos, files, date_created, program_name) 
    VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
  "sssssss",
  $title,
  $tag,
  $video,
  $smallVideosJson,
  $filesJson,
  $dateCreated,
  $program_name
);
$stmt->execute();
$stmt->close();

// === MOVE FILES ===
$allFiles = scandir($fullPath);

foreach ($allFiles as $file) {
  if ($file === "." || $file === ".." || $file === $prefixNum) {
    continue;
  }

  $sourcePath = $fullPath . DIRECTORY_SEPARATOR . $file;
  $destPath = $destDir . DIRECTORY_SEPARATOR . $file;

  if (is_file($sourcePath)) {
    rename($sourcePath, $destPath); // CUT + MOVE
  }
}

echo "✅ Inserted path: \"$prefixNum\" lesson \"$title\" successfully for program \"$program_name\".";

?>

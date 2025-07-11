<?php
include 'db_connect.php';

// === CONFIG ===
$mediaDir = 'uploads/lessons/206'; // change this to your folder
$id = 206;                 // manually set
$title = 'Lesson 64_Grammar Conjunctions';
$tag = 'Grammar';

$program_name = 'TA1_A0';

// === FILE SCAN ===
$fullPath = __DIR__ . '/' . $mediaDir;

if (!is_dir($fullPath)) {
    exit("❌ Directory not found: $mediaDir");
}

$allFiles = scandir($fullPath);

// === FILTER FILES ===
$videoFile = null;
$smallVideos = [];
$docs = [];

foreach ($allFiles as $file) {
    if ($file === '.' || $file === '..') continue;
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    $relativePath = "$mediaDir/$file";

    // Choose main video if not already picked
    if (!$videoFile && $ext === 'mp4') {
        $videoFile = $relativePath;
        continue;
    }

    // Small videos (mp4/mp3), excluding the chosen main video
    if (in_array($ext, ['mp4', 'mp3'])) {
        $smallVideos[] = $relativePath;
    }

    // Docs (pdf, ppt, doc, etc.)
    if (in_array($ext, ['doc', 'docx', 'ppt', 'pptx', 'pdf'])) {
        $docs[] = $relativePath;
    }
}

// Remove videoFile from smallVideos if exists
$smallVideos = array_values(array_filter($smallVideos, fn($v) => $v !== $videoFile));

// === PREPARE VALUES ===
$video = $videoFile ? $videoFile : '';
$smallVideosJson = json_encode($smallVideos);
$filesJson = json_encode($docs);
$dateCreated = date('Y-m-d H:i:s');

// === INSERT INTO DB ===
$sql = "INSERT INTO lessons 
    (id, title, tag, video, small_videos, files, date_created, program_name) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->execute([
    $id,
    $title,
    $tag,
    $video,
    $smallVideosJson,
    $filesJson,
    $dateCreated,
    $program_name
]);

echo "✅ Inserted lesson ID $id with title \"$title\" successfully.";
?>

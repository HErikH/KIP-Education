<?php
include "db_connect.php";

echo "Here";

if (!isset($_GET["id"]) || empty($_GET["id"])) {
  echo "error";
  exit();
}

$post_id = $_GET["id"];

// Prepare the SQL statement to check if the post exists
$stmt = $conn->prepare("SELECT savePath FROM quizzes WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
  // If no quizzes is found with the given ID
  echo "not_found";
  exit();
}

// Fetch the image URL if it exists (to delete the image file later if needed)
$stmt->bind_result($savePath);
$stmt->fetch();
$stmt->close();

// Now delete the post
$delete_stmt = $conn->prepare("DELETE FROM quizzes WHERE id = ?");
$delete_stmt->bind_param("i", $post_id);

if ($delete_stmt->execute()) {
  // If there's an image file associated with the post, delete it
  if (!empty($savePath) && file_exists($savePath)) {
    unlink($savePath); // Delete the image file from the server
  }

  echo "success";
  header("Location: quizzesadmin.php");
} else {
  echo "error";
}

$delete_stmt->close();
?>

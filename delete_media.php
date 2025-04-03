<?php
include 'db_connect.php';
header('Content-Type: application/json');

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the question_id is received
if (isset($_POST['question_id'])) {
    $questionId = $_POST['question_id'];

    // Fetch the current media paths from the database
    $query = "SELECT image, video FROM questions WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $questionId);
    $stmt->execute();
    $stmt->bind_result($image, $video);
    $stmt->fetch();
    $stmt->close();

    // Delete the media files if they exist
    if ($image && file_exists($image)) {
        unlink($image); // Delete the image file
    }
    if ($video && file_exists($video)) {
        unlink($video); // Delete the video file
    }

    // Update the database to set media fields to null
    $updateQuery = "UPDATE questions SET image = NULL, video = NULL WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("i", $questionId);
    if ($updateStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Media deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update the database.']);
    }
    $updateStmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request. Missing question ID.']);
}

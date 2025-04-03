<?php
session_start();
include 'db_connect.php';

// Check if the session is valid
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}

// Check if the ID parameter is provided
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // First, get the image, video, and files associated with the lesson to delete them from the server
    $lessonResult = $conn->query("SELECT image, video, files FROM lessons WHERE id = $id");
    if ($lessonResult && $lessonResult->num_rows > 0) {
        $lesson = $lessonResult->fetch_assoc();

        // Delete the image file if it exists
        if (!empty($lesson['image']) && file_exists($lesson['image'])) {
            unlink($lesson['image']);
        }

        // Delete the video file if it exists
        if (!empty($lesson['video']) && file_exists($lesson['video'])) {
            unlink($lesson['video']);
        }

        // Delete the associated files
        if (!empty($lesson['files'])) {
            $files = json_decode($lesson['files'], true);
            foreach ($files as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
    }

    // Delete the lesson from the database
    $stmt = $conn->prepare("DELETE FROM lessons WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Redirect to the lessons list page after successful deletion
        header("Location: lessons.php");
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "Invalid request. Lesson ID is missing.";
}

$conn->close();
?>

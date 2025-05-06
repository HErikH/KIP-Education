<?php
// Include database connection
include 'db_connect.php';
require_once 'constants.php';

// Enable error reporting (for debugging purposes, can be removed in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quizId = $_POST['quiz_id'];
    $title = $_POST['quizTitle'];
    $subtitle = $_POST['quizSubtitle'];
    $timeInSeconds = $_POST['time_in_seconds'];
    $imagePath = null;

    // Handle file upload if a new image is provided
    if (isset($_FILES['quizImage']) && $_FILES['quizImage']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = UPLOAD_DIR . 'resource/quiz/img/';
        $imageName = basename($_FILES['quizImage']['name']);
        $savePath = $uploadDir . $imageName;
        $imagePath = IMAGE_URL_BASE_FOR_DB . 'resource/quiz/img/' . $imageName;

        // Move the uploaded file to the desired directory
        if (!move_uploaded_file($_FILES['quizImage']['tmp_name'], $savePath)) {
            header("Location: quizzesadmin.php?message=Error uploading image.");
            exit();
        }
    }

    // Update the quiz in the database
    $stmt = $conn->prepare("UPDATE quizzes SET title = ?, subtitle = ?, time_in_seconds = ?, image = IFNULL(?, image) WHERE id = ?");
    $stmt->bind_param("ssisi", $title, $subtitle, $timeInSeconds, $imagePath, $quizId);

    // Execute the statement and check if it was successful
    if ($stmt->execute()) {
        header("Location: quizzesadmin.php?message=Quiz updated successfully.");
    } else {
        header("Location: quizzesadmin.php?message=Error updating quiz: " . urlencode($conn->error));
    }

    $stmt->close();
    $conn->close();
    exit(); // Make sure to exit after redirecting
}

<?php
session_start();
include 'db_connect.php'; // Ensure this connects to your database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lesson_id = $_POST['lesson_id'];

    // Start lesson
    if (isset($_POST['start_lesson'])) {
        $sql = "UPDATE lessons SET active = 1 WHERE id = $lesson_id";
        $conn->query($sql);
        echo json_encode(["status" => "success", "message" => "Lesson started"]);
        exit();
    }

    // Restart lesson
    if (isset($_POST['restart_lesson'])) {
        $sql = "UPDATE lessons SET active = 0 WHERE id = $lesson_id";
        $conn->query($sql);
        echo json_encode(["status" => "success", "message" => "Lesson restarted"]);
        exit();
    }
}

// Fetch lessons
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $lessonsResult = $conn->query("SELECT id, title, image, tag, video, files, active FROM lessons ORDER BY date_created DESC");
    $lessons = [];
    while ($row = $lessonsResult->fetch_assoc()) {
        $lessons[] = $row;
    }
    echo json_encode($lessons);
    exit();
}

$conn->close();
?>

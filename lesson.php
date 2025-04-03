<?php
session_start();
include 'db_connect.php'; // Ensure this connects to your database

// Function to calculate remaining time for countdown
function getRemainingTime($lesson) {
    if (isset($lesson['start_time']) && $lesson['start_time'] != null) {
        $now = new DateTime();
        $end_time = new DateTime($lesson['end_time']);
        $remaining = $end_time->getTimestamp() - $now->getTimestamp();
        return ($remaining > 0) ? $remaining : 0; // Return remaining time in seconds
    }
    return null;
}

// Fetch lessons from database
$lessonsResult = $conn->query("SELECT id, title, image, tag, start_time, end_time FROM lessons ORDER BY date_created DESC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if Start button was clicked
    if (isset($_POST['start_lesson'])) {
        $lesson_id = $_POST['lesson_id'];
        
        // Start the countdown for 30 seconds
        $start_time = new DateTime();
        $end_time = clone $start_time;
        $end_time->modify('+30 seconds');
        
        // Update database with start and end time
        $conn->query("UPDATE lessons SET start_time = '{$start_time->format('Y-m-d H:i:s')}', end_time = '{$end_time->format('Y-m-d H:i:s')}' WHERE id = {$lesson_id}");
        
        echo json_encode(['success' => true, 'start_time' => $start_time->getTimestamp(), 'end_time' => $end_time->getTimestamp()]);
        exit;
    }

    // Check if Restart button was clicked
    if (isset($_POST['restart_lesson'])) {
        $lesson_id = $_POST['lesson_id'];

        // Reset the lesson to its initial state
        $conn->query("UPDATE lessons SET start_time = NULL, end_time = NULL WHERE id = {$lesson_id}");
        
        echo json_encode(['success' => true]);
        exit;
    }
}

// Function to fetch active lessons with countdown details
if (isset($_GET['fetch_lessons'])) {
    $lessons = [];
    if ($lessonsResult && $lessonsResult->num_rows > 0) {
        while ($lesson = $lessonsResult->fetch_assoc()) {
            $lesson['remaining_time'] = getRemainingTime($lesson);
            $lessons[] = $lesson;
        }
    }
    echo json_encode($lessons);
    exit;
}
?>

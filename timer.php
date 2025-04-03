<?php
// Database connection
$servername = "localhost";
$username = "your_username";
$password = "your_password";
$dbname = "your_database";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// API for getting remaining time
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['userId'];
    $postId = $_POST['postId'];

    // Check if the timer exists
    $sql = "SELECT remaining_seconds, start_time, duration_seconds FROM user_timers WHERE user_id = ? AND post_id = ? AND is_active = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $postId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $startTime = strtotime($row['start_time']);
        $currentTime = time();
        $elapsedTime = $currentTime - $startTime;

        $remainingSeconds = $row['duration_seconds'] - $elapsedTime;

        if ($remainingSeconds <= 0) {
            // Timer has expired
            $remainingSeconds = 0;
            // Optionally, update the timer to inactive
            $updateSql = "UPDATE user_timers SET is_active = 0 WHERE user_id = ? AND post_id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("ii", $userId, $postId);
            $updateStmt->execute();
        }

        echo json_encode(['remaining_seconds' => $remainingSeconds]);
    } else {
        echo json_encode(['error' => 'Timer not found']);
    }

    $stmt->close();
}

$conn->close();
?>

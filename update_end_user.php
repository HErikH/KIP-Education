<?php
// Start a session for the quiz
session_name('quiz_session');
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quiz_id'])) {
    $quiz_id = intval($_POST['quiz_id']);
    $user_id = isset($_SESSION['quiz_user_id']) ? $_SESSION['quiz_user_id'] : 0;

    if ($user_id > 0 && $quiz_id > 0) {
        // Fetch the current end_user_id from the quiz
        $quizQuery = "SELECT end_user_id FROM quizzes WHERE id = ?";
        $stmt = $conn->prepare($quizQuery);
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();
        $stmt->bind_result($existing_end_user_id);
        $stmt->fetch();
        $stmt->close();

        // Split the existing end_user_id string into an array
        $user_ids = !empty($existing_end_user_id) ? explode(',', $existing_end_user_id) : [];

        // Add user_id to the array if it's not already there
        if (!in_array($user_id, $user_ids)) {
            $user_ids[] = $user_id;
            $new_end_user_id = implode(',', $user_ids);

            // Update the quiz with the new end_user_id
            $updateQuery = "UPDATE quizzes SET end_user_id = ? WHERE id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("si", $new_end_user_id, $quiz_id);
            if ($stmt->execute()) {
                echo "User ID added successfully.";
            } else {
                echo "Failed to update the quiz.";
            }
            $stmt->close();
        } else {
            echo "User ID already exists.";
        }
    } else {
        echo "Invalid user or quiz ID.";
    }
} else {
    echo "Invalid request.";
}
?>

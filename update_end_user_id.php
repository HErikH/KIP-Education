<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quiz_id = intval($_POST['quiz_id']);
    $new_user_id = intval($_POST['user_id']);

    // Fetch the current value of end_user_id
    $query = "SELECT end_user_id FROM quizzes WHERE id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare statement']);
        exit();
    }

    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $stmt->bind_result($current_end_user_id);
    $stmt->fetch();
    $stmt->close();

    // If current_end_user_id is not empty, append the new user_id
    if (!empty($current_end_user_id)) {
        // Check if the new user ID already exists in the list to avoid duplication
        $user_ids = explode(',', $current_end_user_id); // Split the current end_user_id by commas
        if (!in_array($new_user_id, $user_ids)) {
            $user_ids[] = $new_user_id; // Append the new user ID
            $updated_end_user_id = implode(',', $user_ids); // Join the array back into a string
        } else {
            // If the user ID is already in the list, no need to update
            echo json_encode(['status' => 'success', 'message' => 'User ID already exists']);
            exit();
        }
    } else {
        // If no existing end_user_id, set it to the new user ID
        $updated_end_user_id = $new_user_id;
    }

    // Update the end_user_id with the new value
    $update_query = "UPDATE quizzes SET end_user_id = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare update statement']);
        exit();
    }

    $stmt->bind_param("si", $updated_end_user_id, $quiz_id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update end_user_id']);
    }

    $stmt->close();
}
?>

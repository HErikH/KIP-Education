<?php
// Include database connection
include 'db_connect.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Check if the required data is received
if (isset($_POST['question_id'], $_POST['answer_index'], $_POST['is_checked'])) {
    $questionId = $_POST['question_id'];
    $answerIndex = (int)$_POST['answer_index'];
    $isChecked = $_POST['is_checked'] === 'true' ? 1 : 0;

    // Fetch the current true_answer values
    $query = "SELECT true_answer FROM questions WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $questionId);
    $stmt->execute();
    $stmt->bind_result($trueAnswers);
    $stmt->fetch();
    $stmt->close();

    $trueAnswersArray = explode(',', $trueAnswers);
    $answerKey = array_search($answerIndex, $trueAnswersArray);

    // Update the true_answer list based on the checkbox state
    if ($isChecked && $answerKey === false) {
        // Add the answer to the true_answer list
        $trueAnswersArray[] = $answerIndex;
    } elseif (!$isChecked && $answerKey !== false) {
        // Remove the answer from the true_answer list
        unset($trueAnswersArray[$answerKey]);
    }

    // Save the updated true_answer values
    $updatedTrueAnswers = implode(',', $trueAnswersArray);

    $updateQuery = "UPDATE questions SET true_answer = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("si", $updatedTrueAnswers, $questionId);

    if ($updateStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Answer status updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update answer status.']);
    }

    $updateStmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}

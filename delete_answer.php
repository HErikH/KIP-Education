<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $questionId = $_POST['question_id'];
    $answerIndex = $_POST['answer_index'];

    // Create dynamic field name for deletion
    $field = "answer_" . $answerIndex;
    $query = "UPDATE questions SET $field = NULL WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $questionId);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(["success" => false, "error" => "Invalid request"]);
}

$conn->close();
?>

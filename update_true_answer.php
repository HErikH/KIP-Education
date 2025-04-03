<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $questionId = $_POST['question_id'];
    $trueAnswer = $_POST['true_answer'];

    // Update the true answer in the database
    $query = "UPDATE questions SET true_answer = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $trueAnswer, $questionId);

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

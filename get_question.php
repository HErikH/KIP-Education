<?php
include 'db_connect.php';

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM questions WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($question = $result->fetch_assoc()) {
    echo json_encode(['success' => true, 'question' => $question]);
} else {
    echo json_encode(['success' => false, 'error' => 'Question not found.']);
}

$stmt->close();
$conn->close();
?>

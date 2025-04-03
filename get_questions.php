<?php
// Include the database connection
include 'db_connect.php';

// Get the quiz ID from the GET request
$quizId = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

// Validate the quiz ID
if ($quizId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid quiz ID']);
    exit;
}

// Fetch questions from the database based on the quiz ID
$query = $conn->prepare("SELECT * FROM questions WHERE quiz_id = ?");
$query->bind_param("i", $quizId);
$query->execute();
$result = $query->get_result();

$questions = [];
while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
}

// Close the database connection
$query->close();
$conn->close();

// Return the data as JSON
echo json_encode(['success' => true, 'questions' => $questions]);
?>

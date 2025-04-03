<?php
include 'db_connect.php';

$result = $conn->query("SELECT * FROM questions WHERE quiz_id = 1"); // You can modify the query based on your requirements

$questions = [];
while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
}

echo json_encode($questions);
$conn->close();
?>

<?php
// Include the database connection
include 'db_connect.php';

// Get the raw POST data
$data = json_decode(file_get_contents('php://input'), true);

// Check if the ID is provided
if (isset($data['id'])) {
    $questionId = $data['id'];

    // Prepare the delete statement
    $stmt = $conn->prepare("DELETE FROM questions WHERE id = ?");
    $stmt->bind_param("i", $questionId);

    // Execute the delete statement
    if ($stmt->execute()) {
        // Return a success response
        echo json_encode(['success' => true]);
    } else {
        // Return an error response
        echo json_encode(['success' => false, 'error' => 'Failed to delete the question.']);
    }

    $stmt->close();
    $conn->close();
} else {
    // Return an error response
    echo json_encode(['success' => false, 'error' => 'Invalid request.']);
}
?>

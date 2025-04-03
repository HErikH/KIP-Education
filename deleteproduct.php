<?php
// Include database connection
include 'db_connect.php';

// Check if the delete_id is passed via URL
if (isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];

    // Delete query
    $deleteQuery = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $deleteId);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Product deleted successfully.</div>";
        header("Location: programmsadmin.php");
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}
?>

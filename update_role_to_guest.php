<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Include the database connection
    include('db_connect.php');

    // Get the user ID from the POST request
    if (isset($_POST['user_id']) && is_numeric($_POST['user_id'])) {
        $userId = (int)$_POST['user_id'];

        // SQL query to update the user's role to 'guest' and reset start and end dates
        $sql = "UPDATE users SET role = 'guest', date_start_role = NULL, date_end_role = NULL WHERE id = ?";

        // Prepare the SQL statement
        if ($stmt = $conn->prepare($sql)) {
            // Bind the user ID
            $stmt->bind_param('i', $userId);

            // Execute the statement
            if ($stmt->execute()) {
                echo "Role updated to guest and dates cleared";
            } else {
                echo "Error updating role: " . $stmt->error;
            }

            // Close the statement
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    }

    // Close the database connection
    $conn->close();
}
?>

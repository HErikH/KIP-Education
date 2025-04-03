<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start session if not already started
}

// Include the database connection
include('db_connect.php');

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    // Get the current date and time
    $currentDate = date('Y-m-d H:i:s');

    // SQL query to fetch the role and date_end_role
    $sql = "SELECT role, date_end_role FROM users WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        // Bind the userId parameter
        $stmt->bind_param('i', $userId);

        // Execute the query
        $stmt->execute();

        // Bind result variables
        $stmt->bind_result($role, $dateEndRole);

        // Fetch the result
        if ($stmt->fetch()) {
            // Check if date_end_role has passed and the user is not already a guest
            if (!empty($dateEndRole) && $currentDate > $dateEndRole && $role !== 'guest') {
                // Update the user's role to 'guest'
                $updateSql = "UPDATE users SET role = 'guest' WHERE id = ?";
                if ($updateStmt = $conn->prepare($updateSql)) {
                    $updateStmt->bind_param('i', $userId);
                    $updateStmt->execute();
                    $updateStmt->close();

                    // Update the session role to guest
                    $_SESSION['role'] = 'guest';
                }
            }
        }

        // Close the statement
        $stmt->close();
    }
}

// Close the database connection
$conn->close();
?>

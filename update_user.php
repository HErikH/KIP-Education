<?php
// Include the database connection
include 'db_connect.php';
require_once './constants.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate user ID
    if (isset($_POST['user_id']) && is_numeric($_POST['user_id'])) {
        $userId = intval($_POST['user_id']);
    } else {
        // Log the error and exit silently if the user_id is invalid
        error_log("Invalid user ID provided");
        exit;
    }

    // Validate and sanitize the incoming fields
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $firstLastName = htmlspecialchars(trim($_POST['first_last_name']));
    $phoneNumber = htmlspecialchars(trim($_POST['phone_number'] ?? ''));  // Default to empty string if not provided
    $country = htmlspecialchars(trim($_POST['country'] ?? 'Armenia'));  // Default to Armenia
    $balance = floatval($_POST['balance'] ?? 0.00);  // Default balance to 0.00
    $role = htmlspecialchars(trim($_POST['role'] ?? 'guest'));  // Default role to guest

    // If email validation fails, log the error and exit
    if (!$email) {
        error_log("Invalid email provided: " . $_POST['email']);
        exit;
    }

    // Ensure that balance is numeric
    if (!is_numeric($balance)) {
        error_log("Invalid balance provided: " . $_POST['balance']);
        exit;
    }

    // Prepare the SQL statement to update the user fields
    $stmt = $conn->prepare("UPDATE users SET email = ?, first_last_name = ?, phone_number = ?, country = ?, balance = ?, role = ?, bought_program_names	= ? WHERE id = ?");
    
    if ($stmt) {
        // Bind parameters and execute the query
        $stmt->bind_param('sssssssi', $email, $firstLastName, $phoneNumber, $country, $balance, $role, json_encode(ALL_PROGRAM_NAMES), $userId);
        if ($stmt->execute()) {
            // Close the statement and the connection
            $stmt->close();
            $conn->close();
            // Redirect to the previous page after successful update
            header("Location: " . $_SERVER['HTTP_REFERER'] . "?success=true");
            exit;
        } else {
            error_log("Failed to execute update: " . $stmt->error);
        }
    } else {
        // Log SQL preparation error
        error_log("Failed to prepare SQL statement: " . $conn->error);
    }

    // Close the database connection in case of error
    $conn->close();
    // Redirect back with an error
    header("Location: " . $_SERVER['HTTP_REFERER'] . "?error=update_failed");
    exit;
}

?>

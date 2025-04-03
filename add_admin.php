<?php
// Include database connection
include 'db_connect.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $email = $_POST['email'];
    $password = $_POST['password'];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hash the password for security
    $role = 'admin'; // You can set other roles based on your use case

    // Prepare the SQL statement to insert the new admin
    $stmt = $conn->prepare("INSERT INTO admins (email, password, role, created_at) VALUES (?, ?, ?, NOW())");

    if (!$stmt) {
        // If there's an error preparing the statement, return "error"
        echo 'error';
        exit();
    }

    // Bind the parameters and execute the query
    $stmt->bind_param("sss", $email, $hashed_password, $role);

    if ($stmt->execute()) {
        // Return "success" if the query was executed successfully
        echo 'success';
    } else {
        // Return "error" if something went wrong
        echo 'error';
    }

    // Close the statement
    $stmt->close();
}

// Close the database connection
$conn->close();

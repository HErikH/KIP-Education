<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Sanitize the input
    $email = $conn->real_escape_string($email);
    $password = $conn->real_escape_string($password);
    $role = $conn->real_escape_string($role);

    // Insert the new user or admin into the appropriate table
    if ($role === 'user') {
        $sql = "INSERT INTO students (email, password) VALUES ('$email', '$password')";
    } else {
        $sql = "INSERT INTO admins (email, password) VALUES ('$email', '$password')";
    }

    if ($conn->query($sql) === TRUE) {
        header("Location: users.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>

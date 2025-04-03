<?php
// Database connection parameters
$servername = "localhost";
$username = "admin12345_admin1234";
$password = "Kip2024edu";
$dbname = "admin12345_school";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

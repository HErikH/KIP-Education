<?php
session_start();
include 'db_connect.php'; // Կապը բազայի հետ

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ստուգել, արդյոք հարցումը POST մեթոդով է
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ստանալ post_id, reason և օգտատիրոջ ID-ն
    $post_id = $_POST['post_id'];
    $reason = $_POST['reason'];
    $user_id = $_SESSION['user_id']; // Ստանում ենք օգտատիրոջ ID-ն session-ից

    // Ստուգել, արդյոք դաշտերը լցված են
    if (!empty($post_id) && !empty($reason) && !empty($user_id)) {
        // Պատրաստել SQL հարցումը՝ report պահպանելու համար
        $report_name = "Activation Request for Post ID: " . $post_id;
        $stmt = $conn->prepare("INSERT INTO reports (report_name, date_created, reason, user_id, post_id) VALUES (?, NOW(), ?, ?, ?)");
        
        if (!$stmt) {
            die("Error preparing statement: " . $conn->error); // Display error if prepare fails
        }

        $stmt->bind_param("ssii", $report_name, $reason, $user_id, $post_id);

        if ($stmt->execute()) {
            echo "success"; // Հաջողության դեպքում վերադարձնել "success"
        } else {
            die("Error executing statement: " . $stmt->error); // Display error if execution fails
        }

        $stmt->close();
    } else {
        echo "Please fill all fields."; // If fields are empty, return a message
    }
} else {
    echo "Invalid request method."; // If the request is not POST
}

$conn->close();
?>

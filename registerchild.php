<?php
session_start();
include 'db_connect.php';

// Get the quiz ID from the URL
$quiz_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['first_name'], $_POST['last_name'], $_POST['phone_number'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone_number = trim($_POST['phone_number']);
    $company_name = isset($_POST['company_name']) ? trim($_POST['company_name']) : null;

    // Insert the data into the database
    $stmt = $conn->prepare("INSERT INTO children (first_name, last_name, company_name, phone_number) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $first_name, $last_name, $company_name, $phone_number);

    if ($stmt->execute()) {
        $_SESSION['registration_success'] = true; // Set session variable to show success message
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $quiz_id); // Redirect to avoid form resubmission
        exit();
    }
    $stmt->close();
}
?>

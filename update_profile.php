<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $field = $_POST['field'];
    $value = $_POST['value'];

    $sql = "UPDATE users SET $field = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $value, $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo 'Data updated successfully.';
    } else {
        echo 'No changes were made.';
    }

    header('Location: profile.php');
}
?>

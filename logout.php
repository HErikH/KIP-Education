<?php
session_start();

// Очистка session_id в базе данных
include 'db_connect.php';
$user_id = $_SESSION['user_id'];
$update_stmt = $conn->prepare("UPDATE users SET session_id = NULL WHERE id = ?");
$update_stmt->bind_param("i", $user_id);
$update_stmt->execute();

$_SESSION = array();
session_destroy();

header("Location: login");
exit();

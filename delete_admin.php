<?php
session_start();
include 'db_connect.php'; // Միացնում ենք բազային

// Ստուգում ենք՝ արդյոք admin ID-ն փոխանցվել է
if (isset($_POST['id'])) {
    $admin_id = $_POST['id'];

    // Պատրաստում ենք SQL հարցումը՝ admin ջնջելու համար
    $stmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
    $stmt->bind_param("i", $admin_id);

    if ($stmt->execute()) {
        echo 'success'; // Հաջող ջնջում
    } else {
        echo 'error'; // Սխալ՝ հարցման մեջ
    }

    $stmt->close(); // Փակում ենք հարցման statement-ը
    $conn->close(); // Փակում ենք կապը
} else {
    echo 'error'; // Սխալ՝ ID-ն չի փոխանցվել
}
?>

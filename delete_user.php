<?php
session_start();
include 'db_connect.php'; // Միացնում ենք բազային

// Ստուգում ենք՝ արդյոք օգտատիրոջ ID-ն փոխանցվել է
if (isset($_POST['id'])) {
    $user_id = $_POST['id'];

    // Պատրաստում ենք SQL հարցումը՝ օգտատիրոջը users աղյուսակից ջնջելու համար
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo 'success'; // Հաջող ջնջում
    } else {
        echo 'error'; // Սխալ հարցման մեջ
    }

    $stmt->close(); // Փակում ենք հարցման statement-ը
    $conn->close(); // Փակում ենք կապը
} else {
    echo 'error'; // ID-ն չի փոխանցվել
}
?>

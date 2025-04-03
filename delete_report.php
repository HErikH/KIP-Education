<?php
session_start();
include 'db_connect.php'; // Կապը բազայի հետ

// Ստուգել, արդյոք հարցումը POST մեթոդով է
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ստանալ հաղորդման ID-ն, որը պետք է ջնջվի
    if (isset($_POST['id'])) {
        $report_id = $_POST['id'];

        // Պատրաստել SQL հարցումը
        $stmt = $conn->prepare("DELETE FROM reports WHERE id = ?");
        $stmt->bind_param("i", $report_id);

        // Ստուգել, արդյոք հարցումը հաջող է կատարվել
        if ($stmt->execute()) {
            echo "success"; // Վերադարձնել հաջողության հաղորդագրություն
        } else {
            echo "error"; // Եթե հարցումը ձախողվել է, վերադարձնել սխալ հաղորդագրություն
        }

        $stmt->close();
    } else {
        echo "error"; // Եթե ID-ն բացակայում է
    }
}

$conn->close();
?>

<?php
include 'db_connect.php'; // Ձեզ մոտ արդեն կապ է ստեղծված բազայի հետ

$conn->set_charset("utf8mb4");

// Ստուգում ենք, արդյոք հարցման մեթոդը POST է
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ստանում ենք field, newValue և userId փոփոխականները POST հարցումից
    $field = $_POST['field'];
    $newValue = $_POST['newValue'];
    $userId = $_POST['userId'];

    // Ստուգում ենք, որ բոլորը լրացված են
    if (empty($field) || empty($newValue) || empty($userId)) {
        echo "Missing data.";
        exit();
    }

    // Խուսափում ենք SQL ներարկումներից, ստուգելով field-ը
    // Ավելացնում ենք 'first_last_name' դաշտը թույլատրելի դաշտերի ցանկում
    $allowed_fields = ['first_last_name', 'phone_number', 'address']; // Դաշտերի ցանկը, որոնք կարելի է թարմացնել
    if (!in_array($field, $allowed_fields)) {
        echo "Invalid field.";
        exit();
    }

    // Պատրաստում ենք SQL հարցումը, օգտագործելով հստակ field անունը
    $sql = "UPDATE users SET $field = ? WHERE id = ?";

    // Պատրաստում ենք նախապես մշակված հարցումը
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing query: " . $conn->error);
    }

    // Կապում ենք արժեքները և հարցումը կատարում
    $stmt->bind_param("si", $newValue, $userId);
    if ($stmt->execute()) {
        echo "Success"; // Համոզվեք, որ սա է գրվում սերվերից
    } else {
        echo "Error: " . $stmt->error; // Տպում ենք սխալները
    }

    $stmt->close();
}

?>

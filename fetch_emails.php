<?php
// Միացնում ենք տվյալների բազայի հետ կապը
require 'db_connect.php';

// Ստուգում ենք օգտատիրոջ session-ը, որպեսզի իմանանք նրա ID-ն
session_start();
$current_user_id = $_SESSION['user_id'];  // Օրինակ, օգտատիրոջ ID-ն ստանում ենք session-ից

// Ստեղծում ենք հարցումը
$query = $conn->prepare("SELECT * FROM user_emails WHERE user_id = ?");
$query->bind_param("i", $current_user_id); // "i" նշում է, որ օգտագործվում է integer տիպի փոփոխական
$query->execute();

// Արդյունքների ստացում
$result = $query->get_result();
$emails = $result->fetch_all(MYSQLI_ASSOC);

// Ցուցադրում ենք նամակները
foreach ($emails as $email) {
    echo "Subject: " . htmlspecialchars($email['email_subject']) . "<br>";
    echo "Body: " . htmlspecialchars($email['email_body']) . "<br>";
}

// Փակենք հարցման և տվյալների բազայի կապը
$query->close();
$conn->close();
?>

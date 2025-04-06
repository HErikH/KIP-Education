<?php
// Միացնում ենք բազայի հետ կապը
include 'db_connect.php';

// Ստուգում ենք՝ արդյոք form-ը ուղարկվել է
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ստանում ենք form-ի դաշտերը
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $company_name = isset($_POST['company_name']) ? $_POST['company_name'] : null; // Ընտրովի դաշտ
    $phone_number = $_POST['phone_number'];
    $points = isset($_POST['points']) ? $_POST['points'] : 0; // Default արժեքը 0 է, եթե չի մուտքագրվել

    // Պատրաստում ենք SQL հարցումը
    $stmt = $conn->prepare("INSERT INTO children (first_name, last_name, company_name, phone_number, points) VALUES (?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Փոխանցում ենք արժեքները SQL հարցման մեջ
    $stmt->bind_param("ssssd", $first_name, $last_name, $company_name, $phone_number, $points);

    // Փորձում ենք կատարել հարցումը
    if ($stmt->execute()) {
        // Եթե հարցումը հաջողվեց, տեղափոխում ենք հետ դեպի children list էջը
        header("Location: children_list?success=1");
        exit();
    } else {
        // Եթե հարցումը ձախողվեց, ցույց ենք տալիս սխալը
        echo "Error: " . $stmt->error;
    }

    // Փակվում ենք prepared statement-ը
    $stmt->close();
}

// Փակում ենք բազայի կապը
$conn->close();
?>

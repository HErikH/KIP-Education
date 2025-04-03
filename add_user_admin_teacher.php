<?php
include 'db_connect.php';

// Ստուգում ենք՝ ֆորման արդյոք ուղարկվել է POST մեթոդով
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Հեշավորում ենք գաղտնաբառը
    $role = $_POST['role'];
    $registration_date = date('Y-m-d H:i:s');

    // Որոշում ենք, թե որ աղյուսակին է պատկանում տվյալ օգտագործողը
    if ($role === 'user') {
        $stmt = $conn->prepare("INSERT INTO students (email, password, registration_date) VALUES (?, ?, ?)");
    } elseif ($role === 'admin') {
        $stmt = $conn->prepare("INSERT INTO admins (email, password, registration_date) VALUES (?, ?, ?)");
    } elseif ($role === 'teacher') {
        $stmt = $conn->prepare("INSERT INTO teachers (email, password, registration_date) VALUES (?, ?, ?)");
    }

    // Արժեքների կապում և կատարում
    $stmt->bind_param("sss", $email, $password, $registration_date);
    if ($stmt->execute()) {
        header("Location: users.php"); // Հաջողությամբ ավելացնելուց հետո վերադառնում ենք users.php
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

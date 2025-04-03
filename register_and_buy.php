<?php
session_start(); // Start the session

include 'db_connect.php'; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect user details from form submission
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Secure password hashing
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $phoneNumber = $_POST['phone_number'];
    $address = $_POST['address'];
    $userType = $_POST['userType'];
    $companyName = $_POST['companyName'] ?? null;
    $role = 'guest'; // Default role
    $isBlocked = 'No'; // Default blocked status
    $timeLeft = null; // Time left initially null
    $dateRegister = date('Y-m-d H:i:s'); // Date of registration

    // Step 1: Insert user into `users` table
    $stmt = $conn->prepare("INSERT INTO users (email, password, first_name, last_name, address, phone_number, user_type, company_name, date_register, role, blocked, time_left) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssssssssss', $email, $password, $firstName, $lastName, $address, $phoneNumber, $userType, $companyName, $dateRegister, $role, $isBlocked, $timeLeft);
    $stmt->execute();

    // Get the user ID of the newly inserted user
    $userId = $stmt->insert_id;

    // Step 2: Insert purchase details into `buyproductreport`
    $productName = $_POST['product_name'] ?? null;
    $productId = $_POST['product_id'] ?? null;
    $cash = $_POST['cash'] ?? null;

    if ($productId && $cash && $productName) {
        $stmtReport = $conn->prepare("INSERT INTO buyproductreport (product_id, user_id, cash, email, first_name, last_name, data) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmtReport->bind_param('iisssss', $productId, $userId, $cash, $email, $firstName, $lastName, $dateRegister);
        $stmtReport->execute();
    }

    // Step 3: Automatically log in the user by setting session variables
    $_SESSION['user_id'] = $userId;
    $_SESSION['email'] = $email;
    $_SESSION['first_name'] = $firstName;
    $_SESSION['last_name'] = $lastName;
    $_SESSION['role'] = $role;

    // Redirect to the dashboard or home page after successful login
    echo json_encode(['success' => true, 'redirect' => 'dashboard.php']); // You can change the redirect URL

    // Close statements
    $stmt->close();
    if (isset($stmtReport)) {
        $stmtReport->close();
    }
    $conn->close();
}
?>

<?php
// Include the database connection
include 'db_connect.php';

session_start(); // Start session to track user login state

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debugging: Print form data
    var_dump($_POST); // Outputs form data
    die(); // Stops execution to see form data
    
    // Retrieve posted data
    $userId = $_SESSION['user_id'];
    $productId = $_POST['product_id'];
    $productPrice = $_POST['cash'];
    $paymentMethod = $_POST['payment_method'];
    $userEmail = $_POST['email'];
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $currentDate = date('Y-m-d H:i:s'); // Current timestamp

    // Fetch user's balance
    $sql = "SELECT balance FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($balance);
    $stmt->fetch();
    $stmt->close();

    // If payment is from balance
    if ($paymentMethod === 'balance') {
        if ($balance >= $productPrice) {
            // Deduct price from user's balance
            $newBalance = $balance - $productPrice;
            $updateBalanceSql = "UPDATE users SET balance = ? WHERE id = ?";
            $stmt = $conn->prepare($updateBalanceSql);
            $stmt->bind_param("di", $newBalance, $userId);
            if ($stmt->execute()) {
                // Record the purchase in buyproductreport table
                $insertReportSql = "INSERT INTO buyproductreport (product_id, user_id, cash, email, first_name, last_name, data) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insertReportSql);
                $stmt->bind_param("iisssss", $productId, $userId, $productPrice, $userEmail, $firstName, $lastName, $currentDate);
                if ($stmt->execute()) {
                    echo "<script>alert('Payment successful!');</script>";
                } else {
                    echo "<script>alert('Error recording transaction.');</script>";
                }
                $stmt->close();
            } else {
                echo "<script>alert('Error processing payment.');</script>";
            }
        } else {
            echo "<script>alert('Insufficient balance.');</script>";
        }
    } else {
        echo "<script>alert('Redirecting to card payment...');</script>";
    }
}
?>

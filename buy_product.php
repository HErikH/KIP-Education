<?php
// Include the database connection
include 'db_connect.php';
require_once './constants.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // Start session to track user login state

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve posted data
    $userId = $_SESSION['user_id'];
    $productId = $_POST['product_id'];
    $productPrice = $_POST['cash'];
    $paymentMethod = $_POST['payment_method'];
    $userEmail = $_POST['email'];
    $firstLastName = $_POST['first_last_name'];
    $currentDate = date('Y-m-d H:i:s');

    // Validate inputs
    if (empty($userId) || empty($productId) || empty($productPrice) || empty($paymentMethod) || empty($userEmail) || empty($firstLastName)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input data. Please check the information provided.']);
        exit;
    }

    // Fetch product details to get group, title, and type
    $productSql = "SELECT title, `group`, `type`, product_name FROM products WHERE id = ?";
    $productStmt = $conn->prepare($productSql);

    if ($productStmt === false) {
        error_log('SQL prepare failed (product): ' . $conn->error);
        echo json_encode(['status' => 'error', 'message' => 'Database error while fetching product details.']);
        exit;
    }

    $productStmt->bind_param("i", $productId);
    if (!$productStmt->execute()) {
        error_log('SQL execute failed (product): ' . $productStmt->error);
        echo json_encode(['status' => 'error', 'message' => 'Failed to execute query for product details.']);
        exit;
    }

    $productStmt->bind_result($productTitle, $productGroup, $productType, $productName);
    $productStmt->fetch();
    $productStmt->close();

    if (empty($productTitle)) {
        echo json_encode(['status' => 'error', 'message' => 'Product not found.']);
        exit;
    }

    // Fetch user's balance and role
    $sql = "SELECT balance, role, bought_program_names FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log('SQL prepare failed (user): ' . $conn->error);
        echo json_encode(['status' => 'error', 'message' => 'Database error while fetching user details.']);
        exit;
    }

    $stmt->bind_param("i", $userId);
    if (!$stmt->execute()) {
        error_log('SQL execute failed (user): ' . $stmt->error);
        echo json_encode(['status' => 'error', 'message' => 'Failed to execute query for user details.']);
        exit;
    }

    $stmt->bind_result($balance, $userRole, $bought_program_names);
    $stmt->fetch();
    $stmt->close();

    $bought_program_names = json_decode($bought_program_names, true);

    $response = [];

    // Process payment from balance
    if ($paymentMethod === 'balance') {
        if ($balance >= $productPrice) {
            $newBalance = $balance - $productPrice;

            // Start a transaction to ensure consistency
$conn->begin_transaction();

try {
    // Update balance
    $updateBalanceSql = "UPDATE users SET balance = ?, bought_program_names = ? WHERE id = ?";
    $stmt = $conn->prepare($updateBalanceSql);
    if ($stmt === false) {
        throw new Exception('SQL prepare failed (update balance): ' . $conn->error);
    }

    // Loop through possible program names and check if any one is equal to productName 
    foreach (ALL_PROGRAM_NAMES as $program_name) {
        if ($productName == $program_name && !in_array($program_name, $bought_program_names)) {
            $bought_program_names[] = $program_name;
        }
    }

    // Encode the array as JSON for storing in DB
    $boughtProgramsJson = json_encode($bought_program_names);
    $stmt->bind_param("dsi", $newBalance, $boughtProgramsJson, $userId);
    if (!$stmt->execute()) {
        throw new Exception('SQL execute failed (update balance): ' . $stmt->error);
    }

    // Record the purchase
    $insertReportSql = "INSERT INTO buyproductreport (product_id, user_id, cash, email, first_last_name, data) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertReportSql);
    if ($stmt === false) {
        throw new Exception('SQL prepare failed (insert report): ' . $conn->error);
    }

    $stmt->bind_param("iissss", $productId, $userId, $productPrice, $userEmail, $firstLastName, $currentDate);
    if (!$stmt->execute()) {
        throw new Exception('SQL execute failed (insert report): ' . $stmt->error);
    }

    // Determine the new role based on the product type
    $newRole = ($productType === 'teacher') ? 'teacher' : 'student';
    error_log("Determined new role: $newRole for user_id: $userId");

// Update user role if necessary
if ($userRole !== $newRole) {
    // Nullify date_start_role and date_end_role when updating the role
    $nullValue = null;
    $updateRoleSql = "UPDATE users SET role = ?, date_start_role = ?, date_end_role = ?, product_name = ?, product_id = ? WHERE id = ?";
    $stmt = $conn->prepare($updateRoleSql);
    if ($stmt === false) {
        throw new Exception('SQL prepare failed (update role): ' . $conn->error);
    }

    // Bind parameters and set date_start_role and date_end_role to NULL
    $stmt->bind_param("sssiii", $newRole, $nullValue, $nullValue, $productTitle, $productId, $userId);
    if (!$stmt->execute()) {
        throw new Exception('SQL execute failed (update role): ' . $stmt->error);
    } else {
        error_log("Successfully updated user role to $newRole for user_id: $userId, date_start_role and date_end_role set to NULL");
    }
} else {
    // If the role remains the same, simply update the product name and ID
    $updateRoleSql = "UPDATE users SET product_name = ?, product_id = ? WHERE id = ?";
    $stmt = $conn->prepare($updateRoleSql);
    if ($stmt === false) {
        throw new Exception('SQL prepare failed (update product): ' . $conn->error);
    }

    $stmt->bind_param("sii", $productTitle, $productId, $userId);
    if (!$stmt->execute()) {
        throw new Exception('SQL execute failed (update product): ' . $stmt->error);
    } else {
        error_log("Product updated for user_id: $userId to $productTitle with product_id: $productId");
    }
}

    // Commit the transaction
    $conn->commit();
    $response['status'] = 'success';
    $response['message'] = 'Payment successful!';
    $response['packageGroup'] = $productGroup;
    $response['packageTitle'] = $productTitle;
} catch (Exception $e) {
    // Rollback the transaction on error
    $conn->rollback();
    error_log('Transaction failed: ' . $e->getMessage());
    $response['status'] = 'error';
    $response['message'] = 'An error occurred while processing your payment. Please try again.';
}
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Insufficient balance.';
        }
    } else {
        $response['status'] = 'redirect';
        $response['url'] = 'card_payment.php';
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}
?>

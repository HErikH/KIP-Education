<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if session is started, if not, start it
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login");
    exit();
}

include 'db_connect.php'; // Include the database connection

$user_id = $_SESSION['user_id'];

// Set a default value for $new_role
$new_role = 'student'; // Default value

// Retrieve status, assuming 'success' if it's not provided
$status = isset($_GET['status']) ? $_GET['status'] : 'success';
error_log("All received GET data: " . print_r($_GET, true));

// Check if the order is confirmed and the type is present in the URL
if (isset($_GET['orderId']) && $status === 'success') {
    error_log("Received orderId: " . $_GET['orderId']);
    error_log("Received status: " . (isset($_GET['status']) ? $_GET['status'] : 'Not set'));
    error_log("Received type: " . (isset($_GET['type']) ? $_GET['type'] : 'Not set'));
    error_log("Received full GET data: " . print_r($_GET, true));

    // Check session stored values
    if (isset($_SESSION['product_name']) && isset($_SESSION['product_price'])) {
        $product_name = $_SESSION['product_name'];
        $product_price = $_SESSION['product_price'];

        // Log the session values for debugging
        error_log("Session product_name: $product_name, product_price: $product_price");

        // Fetch the type from the URL if present
        $product_type = isset($_GET['type']) ? $_GET['type'] : 'student';
        error_log("Determined product type: $product_type");

        // Determine the new role based on the fetched product type
        $new_role = ($product_type === 'teacher') ? 'teacher' : 'student';
        error_log("Determined new role: $new_role");

        // Update the user's role, date_start_role, and set date_end_role to NULL
        $update_stmt = $conn->prepare("UPDATE users SET role = ?, date_start_role = NOW(), date_end_role = NULL WHERE id = ?");
        if ($update_stmt === false) {
            error_log("Prepare failed: " . $conn->error);
        } else {
            $update_stmt->bind_param("si", $new_role, $user_id);
            if ($update_stmt->execute()) {
                error_log("Role updated successfully for user_id: $user_id to $new_role");
                // Clear session data related to the order
                unset($_SESSION['product_name']);
                unset($_SESSION['product_price']);
            } else {
                error_log("Failed to update user role: " . $update_stmt->error);
            }

            $update_stmt->close();
        }
    } else {
        error_log("Session values for product_name or product_price are not set.");
    }
} else {
    error_log("Order not confirmed or invalid status.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deposit Success</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(90deg, #4b6cb7 0%, #182848 100%);
            color: white;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .success-container {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 600px;
            margin: 20px auto;
        }
        .btn-primary {
            background-color: #3498db;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            transition: background-color 0.3s ease;
            font-size: 18px;
            font-weight: bold;
            color: white;
            text-decoration: none;
        }
        .btn-primary:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <h2>Deposit Successful!</h2>
        <p>Your account has been credited and your role has been updated to <?= htmlspecialchars($new_role); ?>.</p>
        <a href="profile" class="btn btn-primary">Return to Profile</a>
    </div>
</body>
</html>

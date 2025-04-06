<?php
session_start();
include 'db_connect.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log('User is not logged in.');
    echo json_encode(['status' => 'error', 'message' => 'User is not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$product_name = $_POST['product_name'];
$product_id = $_POST['product_id']; // Add the product_id
$cash = floatval($_POST['cash']) * 100; // Convert to smallest currency unit (cents)

// Log received data for debugging
error_log("Received data: user_id: $user_id, product_name: $product_name, product_id: $product_id, cash: $cash");

// Fetch product type from database
$stmt = $conn->prepare("SELECT type FROM products WHERE id = ?");
if ($stmt === false) {
    error_log('SQL prepare failed (fetch product type): ' . $conn->error);
    echo json_encode(['status' => 'error', 'message' => 'Database error while fetching product type.']);
    exit();
}

$stmt->bind_param("i", $product_id);
$stmt->execute();
$stmt->bind_result($product_type);
$stmt->fetch();
$stmt->close();

error_log("Fetched product type: $product_type for product_id: $product_id");

// Prepare the payment registration data
$api_url = "https://ipay.arca.am/payment/rest/register.do";
$userName = "21536001_api";
$password = "Inessa2006";
$orderNumber = uniqid("order_");
$returnUrl = "deposit_success?type=$product_type"; // Pass the type as a query parameter

// Log the return URL for verification
error_log("Prepared returnUrl: $returnUrl");

$data = [
    'userName' => $userName,
    'password' => $password,
    'orderNumber' => $orderNumber,
    'amount' => $cash,
    'returnUrl' => $returnUrl,
    'description' => $product_name
];

// Log the data being sent to the payment gateway
error_log("Sending data to payment gateway: " . json_encode($data));

// Use cURL to send the request
$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

$response = curl_exec($ch);
curl_close($ch);

// Log the response from the payment gateway
error_log("Received response from payment gateway: $response");

// Parse the response
$response_data = json_decode($response, true);
if (isset($response_data['orderId']) && isset($response_data['formUrl'])) {
    // Save the order details in the session to be used later when the payment is confirmed
    $_SESSION['order_id'] = $response_data['orderId'];
    $_SESSION['product_name'] = $product_name;
    $_SESSION['product_price'] = $cash;

    error_log("Order registered with orderId: {$response_data['orderId']}, redirecting to: {$response_data['formUrl']}");

    // Return the payment form URL
    echo json_encode(['status' => 'success', 'redirectUrl' => $response_data['formUrl']]);
} else {
    error_log("Payment registration failed: " . $response_data['errorMessage']);
    echo json_encode(['status' => 'error', 'message' => $response_data['errorMessage']]);
}
?>

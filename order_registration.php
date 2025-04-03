<?php
// Example of order registration
$api_url = "https://ipay.arca.am/payment/rest/register.do";
$userName = "21536001_api"; // Username
$password = "Inessa2006"; // Password

// Collect form data
$orderNumber = uniqid("order_"); // Generate a unique order number
$amount = intval($_POST['deposit_amount']) * 100; // Amount in the smallest currency unit (cents)
$returnUrl = "https://yourdomain.com/payment_status.php"; // Return URL after payment

// Prepare data for the request
$data = [
    'userName' => $userName,
    'password' => $password,
    'orderNumber' => $orderNumber,
    'amount' => $amount,
    'currency' => 643, // Change as needed
    'returnUrl' => $returnUrl,
    'description' => "Deposit Funds"
];

// Use cURL to send the request
$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

// Execute the request
$response = curl_exec($ch);
curl_close($ch);

// Process the response
$response_data = json_decode($response, true);
if (isset($response_data['orderId']) && isset($response_data['formUrl'])) {
    // Redirect the user to the payment form
    header("Location: " . $response_data['formUrl']);
    exit();
} else {
    echo "Order registration error: " . $response_data['errorMessage'];
}

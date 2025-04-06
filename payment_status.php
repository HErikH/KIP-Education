<?php
// Check if the session has started, and if not, start it
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login");
    exit();
}

$api_url = "https://ipay.arca.am/payment/rest/getOrderStatusExtended.do";
$userName = "21536001_api";
$password = "Inessa2006";

// Check if `orderId` has been received
$orderId = $_GET['orderId'] ?? null;

if ($orderId) {
    $data = [
        'userName' => $userName,
        'password' => $password,
        'orderId' => $orderId
    ];

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

    $response = curl_exec($ch);
    curl_close($ch);

    $response_data = json_decode($response, true);
    if ($response_data['orderStatus'] == 2) {
        echo "The payment was successful.";
    } else {
        echo "The payment failed or is still pending: " . $response_data['actionCodeDescription'];
    }
} else {
    echo "Order ID was not found.";
}

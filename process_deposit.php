<?php
// Ստուգում ենք, արդյոք session-ը սկսված է, և եթե ոչ՝ սկսում ենք
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ստուգում ենք, արդյոք օգտատերը մուտք է գործել
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ստուգում ենք, արդյոք ձևի մեջ նշված է գումարը և այն թիվ է
if (isset($_POST['deposit_amount']) && is_numeric($_POST['deposit_amount'])) {

    $user_id = $_SESSION['user_id'];
    $deposit_amount = floatval($_POST['deposit_amount']); // Դրամով գումարը

    // API հարցումը պատվերի գրանցման համար
    $api_url = "https://ipay.arca.am/payment/rest/register.do";
    $userName = "21536001_api"; // Ձեր IPay API-ի օգտագործողի անունը
    $password = "Inessa2006"; // Ձեր IPay API-ի գաղտնաբառը
    $orderNumber = uniqid("order_"); // Եզակի պատվերի համար
    $returnUrl = "deposit_success.php"; // Վերադարձի URL վճարից հետո

    // Պատրաստում ենք տվյալները հարցման համար
    $data = [
        'userName' => $userName,
        'password' => $password,
        'orderNumber' => $orderNumber,
        'amount' => $deposit_amount * 100, // Փոքրագույն արժույթի միավոր (լումաներ)
        'returnUrl' => $returnUrl,
        'description' => "Deposit Funds"
    ];

    // Օգտագործում ենք cURL հարցումը ուղարկելու համար
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

    // Գործարկում ենք հարցումը
    $response = curl_exec($ch);
    curl_close($ch);

    // Պատասխանի մշակումը
    $response_data = json_decode($response, true);
    if (isset($response_data['orderId']) && isset($response_data['formUrl'])) {
        // Գումարը պահպանում ենք session-ում, որպեսզի այն օգտագործենք հետագա թարմացման համար
        $_SESSION['deposit_amount'] = $deposit_amount;
        
        // Եթե formUrl-ը կա պատասխանի մեջ, օգտատիրոջը ուղարկում ենք այդ հասցեով
        header("Location: " . $response_data['formUrl']);
        exit();
    } else {
        echo "Սխալ է կատարվել պատվերի գրանցման ընթացքում: " . $response_data['errorMessage'];
    }
} else {
    echo "Սխալ է կատարվել: Գումարը նշված չէ կամ սխալ է:";
}

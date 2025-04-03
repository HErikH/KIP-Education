<?php
session_start();
include 'db_connect.php'; // Միացնում ենք բազայի միացումն ապահովող ֆայլը

// Error reporting՝ սխալները տեսնելու համար
error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = ''; // Տեղադրում ենք փոփոխական, որտեղ կպահենք հաջող կամ սխալ հաղորդագրությունները
$code_sent = false; // Ցույց կտա՝ արդյոք կոդը ուղարկված է
$email = ''; // Պահպանում ենք էլ. հասցեն

// Կոդի ուղարկման ֆունկցիա
function send_verification_code($email) {
    global $conn, $message;

    // Գեներացնում ենք 5 նիշանոց կոդ
    $verification_code = random_int(10000, 99999);
    $expires = date("U") + 1800; // 30 րոպե հետո կդադարի գործել (Unix timestamp)

    // Պահպանում ենք գեներացված կոդը `password_resets` աղյուսակում
$stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires, code) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE token=?, expires=?, code=?");
    $token = bin2hex(random_bytes(50));  // 50 բայթանոց (100 նիշանոց) պատահական տող
    $stmt->bind_param("ssisssi", $email, $token, $expires, $verification_code, $token, $expires, $verification_code);
    $stmt->execute();

    // Ստեղծում ենք էլ․ նամակ
    $subject = "Password Reset Verification Code";
    $message_content = "
    <html>
    <head>
      <title>Password Reset Verification Code</title>
    </head>
    <body>
      <h2>Password Reset Verification Code</h2>
      <p>We received a request to reset your password. Please enter the following 5-digit code to reset your password:</p>
      <h3>{$verification_code}</h3>
      <p>If you did not request a password reset, please ignore this email.</p>
    </body>
    </html>
    ";

    // Նամակի համար հեդերներ՝ HTML ֆորմատով
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: no-reply@yourwebsite.com";

    // Ուղարկում ենք նամակը
    if (mail($email, $subject, $message_content, $headers)) {
        $message = "A 5-digit code has been sent to your email.";
    } else {
        $message = "Failed to send the verification code.";
    }
}

// Ստուգում ենք՝ արդյոք սերվերի հարցումը POST մեթոդով է (երբ ֆորման ուղարկվում է)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email'])) {
        // Մուտքագրած email
        $email = trim($_POST['email']); // Վերցնում ենք մուտքագրված էլ․ հասցեն

        // Ստուգում ենք՝ արդյոք էլ․ հասցեն առկա է users աղյուսակում
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // Եթե օգտատերը գտնվել է, ուղարկում ենք կոդը
            send_verification_code($email);
            $code_sent = true;
            $_SESSION['email'] = $email; // Պահպանում ենք email-ը session-ում
        } else {
            $message = "No account found with this email address.";
        }
    }

    // Եթե կոդ է մուտքագրվել՝ ստուգում ենք
    if (isset($_POST['code'])) {
        $entered_code = trim($_POST['code']);
        $email = $_SESSION['email']; // Վերցնում ենք email-ը session-ից

        // Ստուգում ենք՝ արդյոք կոդը վավեր է
        $stmt = $conn->prepare("SELECT * FROM password_resets WHERE email = ? AND code = ? AND expires >= ?");
        $current_time = date("U");
        $stmt->bind_param("ssi", $email, $entered_code, $current_time);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // Կոդը ճիշտ է, կարող ենք փոխել գաղտնաբառը
            header("Location: reset_password.php?email=" . $email);
            exit();
        } else {
            $message = "Invalid or expired code.";
        }
    }

    // Եթե սեղմել են «Վերագործարկել կոդը»
    if (isset($_POST['resend'])) {
        $email = $_SESSION['email']; // Վերցնում ենք email-ը session-ից
        send_verification_code($email); // Կրկին ուղարկում ենք կոդը
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(90deg, #4b6cb7 0%, #182848 100%);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            flex-direction: column;
        }
        .forgot-container {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .forgot-container h2 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: bold;
        }
        .form-group label {
            font-weight: bold;
        }
        .form-control {
            background-color: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
        }
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        .btn-primary {
            background-color: #3498db;
            border: none;
            width: 100%;
            padding: 12px;
            border-radius: 25px;
            transition: background-color 0.3s ease;
            font-size: 18px;
            font-weight: bold;
        }
        .btn-primary:hover {
            background-color: #2980b9;
        }
        .alert-danger, .alert-success {
            display: block;
            background-color: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
        }
        .alert-success {
            background-color: rgba(0, 255, 0, 0.1);
        }
        .divider {
            border-top: 1px solid rgba(255, 255, 255, 0.5);
            margin: 20px 0;
        }
        .main-page-btn {
            margin-top: 20px;
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            font-size: 16px;
            transition: background-color 0.3s ease;
            border: 2px solid #3498db;
            text-align: center;
            display: inline-block;
        }
        .main-page-btn:hover {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <h2 class="mt-5">Forgot Password</h2>
        
        <?php if (!empty($message)): ?>
            <div class="alert <?php echo strpos($message, 'sent') !== false ? 'alert-success' : 'alert-danger'; ?> text-center" role="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if (!$code_sent): ?>
        <!-- Մուտքագրելու ֆորմա էլ․ հասցեն -->
        <form method="POST">
            <div class="form-group position-relative">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
            </div>
            <button type="submit" class="btn btn-primary">Send Code</button>
        </form>
        <?php else: ?>
        <!-- Կոդի մուտքագրման ֆորմա -->
        <form method="POST">
            <div class="form-group position-relative">
                <label for="code">Enter the 5-digit code:</label>
                <input type="text" id="code" name="code" class="form-control" placeholder="Enter code" required>
            </div>
            <button type="submit" class="btn btn-primary">Verify Code</button>
        </form>
        
        <form method="POST" style="margin-top: 10px;">
            <input type="hidden" name="resend" value="1">
            <button type="submit" class="btn btn-secondary">Resend Code</button>
        </form>
        <?php endif; ?>

        <div class="divider"></div>

    </div>
    
    <a href="index.php" class="main-page-btn">Go to Main Page</a>
</body>
</html>

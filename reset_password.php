<?php
session_start();
include 'db_connect.php'; // Միացնում ենք բազայի միացումն ապահովող ֆայլը

// Ստուգում ենք՝ արդյոք email հասցեն փոխանցված է
if (isset($_GET['email'])) {
    $email = $_GET['email']; // Վերցնում ենք էլ. հասցեն
} else {
    die('Invalid request.'); // Եթե էլ. հասցեն չկա, ցուցադրում ենք սխալ
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Նոր գաղտնաբառը մուտքագրվում է
    $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Թարմացնում ենք գաղտնաբառը `users` աղյուսակում
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $new_password, $email);
    $stmt->execute();

    // Հաջող գաղտնաբառի թարմացումից հետո ուղղորդում ենք մուտքի էջ
    header("Location: login.php?password_reset=success");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <!-- Ավելացնում ենք Bootstrap-ի CSS-ը -->
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
        .reset-container {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .reset-container h2 {
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
        .alert-success {
            background-color: rgba(0, 255, 0, 0.1);
            color: white;
            border: none;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <h2 class="mt-5">Reset Password</h2>

        <!-- Ցուցադրում ենք հաջողության հաղորդագրությունը, եթե գաղտնաբառը թարմացված է -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Գաղտնաբառի վերականգնման ֆորմա -->
        <form method="POST">
            <div class="form-group">
                <label for="password">New Password:</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter new password" required>
            </div>
            <button type="submit" class="btn btn-primary">Reset Password</button>
        </form>

        <!-- Գլխավոր էջ կոճակ -->
        <div class="mt-3 text-center">
            <a href="index.php" class="btn btn-secondary">Go to Main Page</a>
        </div>
    </div>

    <!-- Ավելացնում ենք Bootstrap-ի JS-ը -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

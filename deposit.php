<?php
// Ստուգում ենք, արդյոք session-ը սկսված է, և եթե ոչ՝ սկսում ենք
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Ստուգում ենք, արդյոք օգտատերը մուտք է գործել
if (!isset($_SESSION["user_id"])) {
  // Եթե օգտատերը մուտք չի գործել, ուղարկում ենք login.php էջ
  header("Location: login.php");
  exit();
}

// Ներառում ենք header.php
include "header.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deposit</title>

    <!-- Bootstrap CSS (CDN) -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <!-- FontAwesome (CDN for icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(90deg, #4b6cb7 0%, #182848 100%);
            color: white;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .deposit-container {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
        }
        .deposit-container h2 {
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
        .small-text {
            font-size: 14px;
            color: #f0f0f0;
            margin-top: 5px;
        }
        /* Հաղորդագրության համար */
        .error-message {
            color: red;
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>

  <!-- Deposit Form Container -->
    <div class="deposit-container">
        <h2>Deposit Funds</h2>

        <form method="POST" action="process_deposit.php" onsubmit="return validateAmount()">
            <!-- Գումար -->
            <div class="form-group">
                <label for="deposit_amount">Deposit Amount:</label>
                <input type="number" id="deposit_amount" name="deposit_amount" class="form-control" placeholder="Enter deposit amount" min="100" max="1000000" required>
                <small class="small-text">Min: 100 AMD, Max: 1,000,000 AMD</small>
                <div id="error-message" class="error-message"></div>
            </div>
            <div class="form-group text-center mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-wallet"></i> Submit Deposit</button>
            </div>
        </form>
    </div>

    <!-- jQuery (CDN) -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

    <!-- Bootstrap JS (CDN) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Սահմանաչափի ստուգում JavaScript-ով
        function validateAmount() {
            const amount = document.getElementById('deposit_amount').value;
            const errorMessage = document.getElementById('error-message');
            if (amount < 100 || amount > 1000000) {
                errorMessage.textContent = "Գումարը պետք է լինի 100-ից 1,000,000 դրամի սահմաններում:";
                return false;
            }
            errorMessage.textContent = ""; // Մաքրում է սխալի հաղորդագրությունը
            return true;
        }
    </script>

</body>
</html>

<?php // Ներառում ենք footer.php

include "footer.php"; ?>
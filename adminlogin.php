<?php
session_start();
session_regenerate_id(true); // Ստեղծում է նոր session ID, կանխելով session հարձակումները
include 'db_connect.php';

// Եթե ադմինը մուտք է գործել, ուղղորդում ենք admin.php
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header("Location: admin.php");
    exit();
}

// Եթե request-ը POST է (ֆորմայի ուղարկումից հետո)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ստուգում ենք մուտքագրված տվյալները
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Փորձում ենք գտնել օգտատիրոջ տվյալները admins աղյուսակից
    $query = $conn->prepare("SELECT id, password FROM admins WHERE email = ?");
    if ($query === false) {
        die("SQL query preparation failed: " . $conn->error);
    }
    $query->bind_param("s", $email);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Համեմատում ենք գաղտնաբառը
        if ($password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = 'admin'; // Պահպանում ենք օգտատիրոջ դերը որպես admin

            // Վերաուղղում դեպի admin.php
            header("Location: admin.php");
            exit();
        } else {
            $error = "Incorrect password";
        }
    } else {
        $error = "Invalid email or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <!-- Ավելացնում ենք CSS-ն Bootstrap-ի համար -->
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
        .login-container {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-container h2 {
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
        .alert-danger {
            background-color: rgba(255, 0, 0, 0.1);
            border: none;
            color: white;
        }
        .show-hide {
            color: white;
            position: absolute;
            right: 15px;
            top: 38px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2 class="mt-5">Admin Login</h2>

        <!-- Ցուցադրում ենք սխալը, եթե կա -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger text-center" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Մուտքի ֆորմա -->
        <form method="POST">
            <div class="form-group position-relative">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Enter your admin email" required>
            </div>
            <div class="form-group position-relative">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                <span class="show-hide" onclick="togglePassword()">Show</span>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>

    <!-- Ավելացնում ենք Bootstrap-ի JS dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const showHideBtn = document.querySelector('.show-hide');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                showHideBtn.textContent = 'Hide';
            } else {
                passwordField.type = 'password';
                showHideBtn.textContent = 'Show';
            }
        }
    </script>
</body>
</html>

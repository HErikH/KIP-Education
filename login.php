<?php
session_start();
include 'db_connect.php'; // Include the database connection file

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is already logged in
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: profile");
    exit();
}

$message = ''; // Initialize variable for messages

// Handle form submission on POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validate input
    if (empty($email) || empty($password)) {
        $message = "Please fill in both fields.";
    } else {
        // Check the admins table
        $stmt = $conn->prepare("SELECT id, password, 'admin' AS role FROM admins WHERE email = ?");
        
        if ($stmt === false) {
            die("Error preparing statement for admins: " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // If not found, check the users table
            $stmt = $conn->prepare("SELECT id, password, 'user' AS role FROM users WHERE email = ?");
            
            if ($stmt === false) {
                die("Error preparing statement for users: " . $conn->error);
            }

            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
        }

        // Check if the user was found
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // If the password is hashed (bcrypt)
            if (password_verify($password, $user['password'])) {
                // Successful login
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];

                // Get the user's IP address
                $ip_address = $_SERVER['REMOTE_ADDR'];

                // Update the user's IP address in the database
                $update_sql = "UPDATE users SET ip_address = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);

                if ($update_stmt === false) {
                    die("Error preparing the update statement: " . $conn->error);
                }

                $update_stmt->bind_param("si", $ip_address, $user['id']);
                $update_successful = $update_stmt->execute();

                if (!$update_successful) {
                    die("Error updating IP address: " . $update_stmt->error);
                }

                // Check if the same user and IP address already exist in `login_history`
                $check_sql = "SELECT * FROM login_history WHERE user_id = ? AND ip_address = ?";
                $check_stmt = $conn->prepare($check_sql);
                if ($check_stmt === false) {
                    die("Error preparing the check statement: " . $conn->error);
                }

                $check_stmt->bind_param("is", $user['id'], $ip_address);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows > 0) {
                    // If the record exists, update only the `login_time`
                    $update_login_sql = "UPDATE login_history SET login_time = NOW() WHERE user_id = ? AND ip_address = ?";
                    $update_login_stmt = $conn->prepare($update_login_sql);
                    if ($update_login_stmt === false) {
                        die("Error preparing the update login statement: " . $conn->error);
                    }
                    $update_login_stmt->bind_param("is", $user['id'], $ip_address);
                    $update_login_stmt->execute();
                } else {
                    // If no record exists, insert a new record
                    $insert_login_sql = "INSERT INTO login_history (user_id, email, ip_address, login_time) VALUES (?, ?, ?, NOW())";
                    $insert_login_stmt = $conn->prepare($insert_login_sql);

                    if ($insert_login_stmt === false) {
                        die("Error preparing the login history statement: " . $conn->error);
                    }

                    $insert_login_stmt->bind_param("iss", $user['id'], $email, $ip_address);
                    $insert_login_stmt->execute();
                }

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    $message = "Login successful! Welcome Admin.";
                    header("Location: admin");
                    exit();
                } else {
                    $message = "Login successful! Welcome User.";
                    header("Location: profile");
                    exit();
                }
            } else {
                $message = "Invalid password.";
            }
        } else {
            $message = "No user found with this email address.";
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Add Bootstrap CSS -->
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
        .alert {
            display: none; /* Initially hide */
        }
        .alert-danger {
            display: block;
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
        .divider {
            border-top: 1px solid rgba(255, 255, 255, 0.5);
            margin: 20px 0;
        }
        .btn-secondary {
            background-color: #f39c12;
            border: none;
            width: auto;
            padding: 10px 25px;
            border-radius: 25px;
            transition: background-color 0.3s ease;
            font-size: 16px;
            font-weight: bold;
        }
        .btn-secondary:hover {
            background-color: #e67e22;
        }
        .forgot-password {
            text-align: center;
            margin-top: 10px;
        }
        .forgot-password a {
            color: #3498db;
            text-decoration: underline;
            font-size: 14px;
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
        .no-credentials {
            margin-top: 10px;
            text-align: center;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
        }
        .click-here-btn {
            background-color: #f3ed17;
            border: none;
            padding: 10px 25px;
            border-radius: 25px;
            color: white;
            font-weight: bold;
            font-size: 16px;
            margin-top: 10px;
            transition: background-color 0.3s ease, transform 0.3s ease;
            cursor: pointer;
        }
        .click-here-btn:hover {
            background-color: #27ae60;
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2 class="mt-5">Login</h2>
        
        <!-- Display error if exists -->
        <?php if (isset($message) && !empty($message)): ?>
            <div class="alert alert-danger text-center" role="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Login form -->
        <form method="POST">
            <div class="form-group position-relative">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
            </div>
            <div class="form-group position-relative">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                <span class="show-hide" onclick="togglePassword()">Show</span>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>

        <div class="forgot-password">
            <a href="forgot_password.php">Forgot your password?</a>
        </div>

        <div class="divider"></div>

        <div class="no-credentials">
            If you are not registered yet, click below<br>
            <a href="register"><button class="click-here-btn">Register Now</button></a>
        </div>
    </div>

    <a href="index" class="main-page-btn">Go to Main Page</a>
    
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

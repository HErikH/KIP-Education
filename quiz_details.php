<?php
// Start a separate session for the quiz
session_name('quiz_session');
session_start();
include 'db_connect.php';
require_once 'helpers.php';

// Get the quiz ID from the URL
$quiz_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['first_name'], $_POST['last_name'], $_POST['phone_number'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone_number = $_POST['phone_number'];
    $company_name = isset($_POST['company_name']) ? $_POST['company_name'] : null;

    // Insert the data into the database
    $stmt = $conn->prepare("INSERT INTO children (first_name, last_name, company_name, phone_number) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $first_name, $last_name, $company_name, $phone_number);

    if ($stmt->execute()) {
        $_SESSION['registration_success'] = true;
        $_SESSION['quiz_user_id'] = $conn->insert_id; // Store user ID in the quiz session
        $_SESSION['quiz_first_name'] = $first_name; // Store first name in the quiz session
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $quiz_id);
        exit();
    }
    $stmt->close();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_phone_number'])) {
    $login_phone_number = $_POST['login_phone_number'];

    // Check if the user exists in the database
    $stmt = $conn->prepare("SELECT id, first_name FROM children WHERE phone_number = ?");
    $stmt->bind_param("s", $login_phone_number);
    $stmt->execute();
    $stmt->bind_result($user_id, $first_name);
    if ($stmt->fetch()) {
        $_SESSION['login_success'] = true;
        $_SESSION['quiz_user_id'] = $user_id; // Store user ID in the quiz session
        $_SESSION['quiz_first_name'] = $first_name; // Store first name in the quiz session
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $quiz_id);
        exit();
    } else {
        $_SESSION['login_error'] = "Invalid phone number.";
    }
    $stmt->close();
}

// Fetch quiz details from the database
$quizQuery = "SELECT title, subtitle FROM quizzes WHERE id = ?";
$stmt = $conn->prepare($quizQuery);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$stmt->bind_result($quizTitle, $quizSubtitle);
$stmt->fetch();
$stmt->close();

// Check if the quiz has been completed by this user
$isQuizCompleted = false;
if (isset($_SESSION['quiz_user_id'])) {
    $user_id = $_SESSION['quiz_user_id'];
    
    // Check if the end_user_id field has this user's ID for the given quiz
    $completionCheckQuery = "SELECT COUNT(*) FROM quizzes WHERE id = ? AND FIND_IN_SET(?, end_user_id)";
    $stmt = $conn->prepare($completionCheckQuery);
    $stmt->bind_param("ii", $quiz_id, $user_id);
    $stmt->execute();
    $stmt->bind_result($completionCount);
    $stmt->fetch();
    $stmt->close();

    if ($completionCount > 0) {
        $isQuizCompleted = true; // Quiz is completed
    }
}

// Include the separated header
include 'headerchild.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Details</title>
    <link rel="icon" href="<?= addMediaBaseUrl('resource/img/favicon.png') ?>" type="image/png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css">
    <style>
        /* Body styling */
        body {
            background: linear-gradient(90deg, #4b6cb7 0%, #182848 100%);
            color: #333;
            font-family: 'Comic Sans MS', cursive, sans-serif;
            margin: 0;
            padding: 0;
        }

        /* Adding space for footer */
            body::after {
            content: "";
            display: block;
            height: 100px;
        }

        .main-content {
            padding-top: 60px;
            text-align: center;
        }

        .quiz-title {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #ffffff;
        }

        .quiz-subtitle {
            font-size: 20px;
            color: #e0e0e0;
            margin-bottom: 30px;
        }

        .container-box {
            background-color: #ffffff;
            color: #333;
            padding: 25px;
            border-radius: 25px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
            margin: 20px auto;
            width: 80%;
            max-width: 400px;
            text-align: left;
        }

        .container-box h3 {
            color: #4b6cb7;
            text-align: center;
            font-size: 24px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-control {
            border-radius: 15px;
            font-size: 14px;
        }

        .intl-tel-input {
            width: 100%;
        }

        .btn-primary {
            background-color: #4b6cb7;
            border-color: #4b6cb7;
            border-radius: 15px;
        }

        .btn-primary:hover {
            background-color: #182848;
            border-color: #182848;
        }

        .toggle-text {
            margin-top: 15px;
            text-align: center;
            color: #4b6cb7;
            cursor: pointer;
        }

        .alert {
            margin-top: 20px;
        }

        .start-game-btn {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 15px;
            color: #fff;
        }

        .start-game-btn.active {
            background-color: #4b6cb7;
            border-color: #4b6cb7;
        }

        .start-game-btn.active:hover {
            background-color: #182848;
            border-color: #182848;
        }

        .start-game-btn.completed {
            background-color: grey;
            border-color: grey;
            cursor: not-allowed;
        }
    </style>
</head>

<body>

    <!-- Main Content -->
    <div class="main-content">
        <!-- <?php if ($isQuizCompleted): ?>
        <button class="btn start-game-btn completed" disabled>
            Completed
        </button>
        <?php else: ?>
        <a href="quiz_game?id=<?= $quiz_id ?>" class="btn start-game-btn active">
            <i class="fas fa-play"></i> Start Game
        </a>
        <?php endif; ?> -->
        
        <?php if (isset($_SESSION['registration_success']) && $_SESSION['registration_success']): ?>
        <div class="alert alert-success" role="alert" id="successAlert">
            You have successfully registered,
            <?= htmlspecialchars($_SESSION['quiz_first_name']); ?>!
        </div>
        <?php unset($_SESSION['registration_success']); ?>
        <?php elseif (isset($_SESSION['login_success']) && $_SESSION['login_success']): ?>
        <div class="alert alert-success" role="alert" id="successAlert">
            Welcome back,
            <?= htmlspecialchars($_SESSION['quiz_first_name']); ?>!
        </div>
        <?php unset($_SESSION['login_success']); ?>
        <?php elseif (isset($_SESSION['login_error'])): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($_SESSION['login_error']); ?>
        </div>
        <?php unset($_SESSION['login_error']); ?>
        <?php endif; ?>

        <?php if (!empty($quizTitle)): ?>
        <div class="quiz-title">
            <?= htmlspecialchars($quizTitle) ?>
        </div>
        <div class="quiz-subtitle">
            <?= htmlspecialchars($quizSubtitle) ?>
        </div>

        <!-- Start Game button for logged-in users -->
        <?php if (isset($_SESSION['quiz_user_id'])): ?>
        <?php if ($isQuizCompleted): ?>
        <button class="btn start-game-btn completed" disabled>
            Completed
        </button>
        <?php else: ?>
        <a href="quiz_game?id=<?= $quiz_id ?>" class="btn start-game-btn active">
            <i class="fas fa-play"></i> Start Game
        </a>
        <?php endif; ?>
        <?php endif; ?>
        <?php else: ?>
        <p>Quiz not found.</p>
        <?php endif; ?>

        <!-- Other content for login and register -->
        <?php if (!isset($_SESSION['quiz_user_id'])): ?>
        <!-- Show Login and Register Containers if user is not logged in -->
        <div class="container-box" id="loginContainer">
            <h3>Login</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="login_phone_number">Phone Number: <span style="color: red;">*</span></label>
                    <input type="tel" class="form-control" id="login_phone_number" name="login_phone_number" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            <div class="toggle-text" onclick="toggleContainers()">Not registered? Click here to register.</div>
        </div>

        <div class="container-box" id="registerContainer" style="display: none;">
            <h3>Register to Continue</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="first_name">First Name: <span style="color: red;">*</span></label>
                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name: <span style="color: red;">*</span></label>
                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                </div>
                <div class="form-group">
                    <label for="phone_number">Phone Number: <span style="color: red;">*</span></label>
                    <input type="tel" class="form-control" id="phone_number" name="phone_number" required>
                </div>
                <div class="form-group">
                    <label for="company_name">Company Name <small>(optional)</small>:</label>
                    <input type="text" class="form-control" id="company_name" name="company_name">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Register</button>
            </form>
            <div class="toggle-text" onclick="toggleContainers()">Already registered? Click here to login.</div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <script>
        function toggleContainers() {
            const loginContainer = document.getElementById("loginContainer");
            const registerContainer = document.getElementById("registerContainer");
            if (loginContainer.style.display === "none") {
                loginContainer.style.display = "block";
                registerContainer.style.display = "none";
            } else {
                loginContainer.style.display = "none";
                registerContainer.style.display = "block";
            }
        }

        $(document).ready(function () {
            setTimeout(function () {
                $("#successAlert").fadeOut("slow");
            }, 3000);
        });
    </script>
</body>

</html>
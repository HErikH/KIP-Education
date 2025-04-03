<?php
session_start(); // Start the session

// Include your database connection
include 'db_connect.php';

// Check if the session is valid, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Retrieve the user ID from the session
$user_id = $_SESSION['user_id'];

// Fetch the account expiration date from the database
$sql = "SELECT account_expiration_date FROM students WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $expiration_date = $user['account_expiration_date']; // Get the account expiration date
} else {
    echo "User not found.";
    exit();
}

// More queries can go here if needed

// Close the statement
$stmt->close();

// Do not close the connection here if it will be used in other parts of the code
?>


<header class="main-header">
    <div class="container">
        <div class="logo">
            <a href="profile.php">
                <img src="resource/img/logo.png" alt="Logo" style="height: 35px;">
            </a>
        </div>
        <nav class="main-menu">
            <ul>
                <li><a href="profile.php" class="menu-link"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="video.php" class="menu-link"><i class="fas fa-video"></i> Lessons</a></li>
                <li><a href="teachers.php" class="menu-link"><i class="fas fa-chalkboard-teacher"></i> For Teachers</a></li>
            </ul>
        </nav>
        <div class="user-section">
            <div class="user-info">
                <div id="countdown" class="countdown-timer"></div>
                <div class="balance">Balance: $0.00</div>
            </div>
            <a href="profile.php"><img src="resource/img/profile.png" alt="Profile Image" class="profile-img"></a>
        </div>
        <div class="toggle-menu">
            <span>&#9776;</span>
        </div>
    </div>
</header>

<!-- Show Menu for Mobile -->
<div class="mobile-menu">
    <div class="mobile-user-info">
        <a href="profile.php"><img src="resource/img/profile.png" alt="Profile Image" class="profile-img"></a>
        <div class="user-email">user@example.com</div>
        <div class="countdown-timer"></div>
        <div class="balance">Balance: $0.00</div>
    </div>
    <nav>
        <ul>
            <li><a href="profile.php" class="menu-link"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="video.php" class="menu-link"><i class="fas fa-video"></i> Lessons</a></li>
            <li><a href="teachers.php" class="menu-link"><i class="fas fa-chalkboard-teacher"></i> For Teachers</a></li>
        </ul>
    </nav>
</div>

<!-- Font Awesome Icons for buttons -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<style>
    /* General Styles for Header */
    .main-header {
        width: 100%;
        background-color: transparent;
        padding: 10px 0;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1000;
        transition: all 0.3s ease;
    }

    .container {
        width: 90%;
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .logo img {
        height: 35px;
        transition: transform 0.3s ease, filter 0.3s ease;
    }

    .logo img:hover {
        transform: scale(1.05);
        filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.2));
    }

    .main-menu ul {
        list-style-type: none;
        margin: 0;
        padding: 0;
        display: flex;
        align-items: center;
    }

    .main-menu ul li {
        margin-left: 15px;
    }

    .menu-link {
        text-decoration: none;
        color: #ffffff;
        font-size: 15px;
        padding: 8px 16px;
        border-radius: 25px;
        font-weight: bold;
        display: flex;
        align-items: center;
        transition: background 0.4s ease, transform 0.3s ease;
    }

    .menu-link i {
        margin-right: 8px;
    }

    .menu-link:hover {
        background-color: rgba(255, 255, 255, 0.2);
        transform: translateY(-2px);
        color: #ffffff;
    }

    .toggle-menu {
        display: none;
        cursor: pointer;
        font-size: 30px;
        color: #ffffff;
        transition: color 0.3s ease;
    }

    .toggle-menu:hover {
        color: #ff512f;
    }

    /* User Section with Profile Image, Countdown, and Balance */
    .user-section {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 20px;
    }

    .profile-img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: 2px solid #ffffff;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #ffffff;
    }

    .countdown-timer {
        font-size: 14px;
        font-weight: bold;
        color: #ffd700; /* Gold color */
    }

    .balance {
        font-size: 14px;
        color: #ffffff;
        background-color: #f3ed17; /* Background for balance */
        padding: 5px 10px;
        border-radius: 15px;
        font-weight: bold;
    }

    /* Mobile Menu */
    .mobile-menu {
        display: none;
        background-color: rgba(0, 0, 0, 0.9);
        color: white;
        padding: 20px;
        position: absolute;
        top: 70px;
        right: 0;
        width: 100%;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        z-index: 1001;
    }

    .mobile-user-info {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        margin-bottom: 20px;
    }

    .mobile-user-info .profile-img {
        margin-bottom: 10px;
    }

    .user-email {
        font-size: 16px;
        margin-bottom: 10px;
    }

    /* Responsive Styles for Tablets */
    @media (max-width: 1024px) {
        .main-header {
            padding: 20px 0;
        }
        .main-menu ul {
            font-size: 14px;
        }
    }

    /* Responsive Menu for Mobile Devices */
    @media (max-width: 768px) {
        .main-header {
            background-color: rgba(0, 0, 0, 0.8);
        }

        .main-menu ul {
            display: none;
            flex-direction: column;
            position: absolute;
            top: 70px;
            right: 0;
            background-color: rgba(0, 0, 0, 0.9);
            width: 100%;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .main-menu ul li {
            text-align: center;
            margin: 10px 0;
        }

        .main-menu ul li .menu-link {
            background: none;
            color: #ffffff;
        }

        .main-menu ul li .menu-link:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
        }

        .toggle-menu {
            display: block;
            margin-top: 10px;
        }

        .user-section {
            display: none; /* Hide user section on mobile */
        }

        .mobile-menu {
            display: none; /* Initially hide mobile menu */
        }

        .mobile-menu.show {
            display: block; /* Show mobile menu when active */
        }

        .countdown-timer {
            display: block;
        }

        .balance {
            display: block; /* Show balance on mobile */
        }
    }
</style>

<script>
    // Fetch account expiration date from PHP
    var expirationDate = new Date("<?php echo $expiration_date; ?>");

    // Function to calculate remaining days
    function calculateRemainingDays() {
        var currentDate = new Date();
        var timeDiff = expirationDate - currentDate;
        var daysLeft = Math.ceil(timeDiff / (1000 * 60 * 60 * 24)); // Calculate days

        if (daysLeft > 0) {
            document.querySelectorAll(".countdown-timer").forEach(function (element) {
                element.innerHTML = daysLeft + " days left";
            });
        } else {
            document.querySelectorAll(".countdown-timer").forEach(function (element) {
                element.innerHTML = "Account expired!";
            });
        }
    }

    // Call the function immediately and set interval to update daily
    calculateRemainingDays();
    setInterval(calculateRemainingDays, 86400000); // Update every day
</script>

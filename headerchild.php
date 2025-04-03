<!-- headerchild.php -->
<?php
// Ensure the session is started and the database connection is available
if (session_status() == PHP_SESSION_NONE) {
    session_name('quiz_session');
    session_start();
}
include 'db_connect.php';

// Initialize variables
$userName = 'Guest';
$points = 0.0;
$isUserLoggedIn = isset($_SESSION['quiz_user_id']); // Check if the user is logged in

// Get the user's name and points if logged in
if ($isUserLoggedIn) {
    $userId = $_SESSION['quiz_user_id'];
    $userName = $_SESSION['quiz_first_name'];

    // Fetch the user's points from the database
    $stmt = $conn->prepare("SELECT points FROM children WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($points);
    $stmt->fetch();
    $stmt->close();
}

// Handle logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: https://kipeducationlessons.am/quizzes.php");
    exit();
}
?>

<div class="header">
    <div class="left-section">
        <a href="https://kipeducationlessons.am/quizzes.php" class="exit-link">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <!-- Leaderboard Button -->
        <a href="https://kipeducationlessons.am/leaderboard.php" class="leaderboard-btn">
            <i class="fas fa-trophy"></i> <span class="leaderboard-text">Leaderboard</span>
        </a>
    </div>
    
    <?php if ($isUserLoggedIn): ?>
        <div class="points-container">
            <div class="points">
                <i class="fas fa-crown"></i>
                <span class="points-value"><?= number_format($points, 1) ?></span>
                <span class="points-text">Points</span>
            </div>
        </div>
        <div class="user-info">
            <span class="user-name"><?= htmlspecialchars($userName) ?></span>
            <a href="?logout=true" class="logout-icon" title="Log out">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    <?php endif; ?>
</div>

<style>
    /* Header styling */
    .header {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        background-color: #182848;
        color: #ffffff;
        padding: 10px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }
    .left-section {
        display: flex;
        align-items: center;
    }
    .exit-link {
        color: #ffffff;
        text-decoration: none;
        font-size: 16px;
        margin-right: 20px;
    }
    .exit-link i {
        margin-right: 8px;
    }

    .leaderboard-btn {
        background-color: #ff9800;
        color: #ffffff;
        padding: 10px 20px;
        border-radius: 20px;
        text-decoration: none;
        font-size: 16px;
        font-weight: bold;
        display: flex;
        align-items: center;
        transition: background-color 0.3s;
    }
    .leaderboard-btn i {
        margin-right: 8px;
    }
    .leaderboard-btn:hover {
        background-color: #e68900;
    }

    .points-container {
        flex: 1;
        display: flex;
        justify-content: center; /* Center the points horizontally */
    }
    .points {
        display: flex;
        align-items: center;
        font-size: 18px;
        color: #ffd700; /* Gold color for points */
    }
    .points i {
        margin-right: 5px;
    }
    .user-info {
        display: flex;
        align-items: center;
    }
    .user-name {
        margin-right: 15px;
        font-size: 16px;
    }
    .logout-icon {
        color: #ffffff;
        cursor: pointer;
        font-size: 18px;
        display: flex;
        align-items: center;
    }
    .logout-icon i {
        margin-right: 5px;
    }

    /* Media query for small screens */
    @media (max-width: 768px) {
        .leaderboard-text, .points-text {
            display: none; /* Hide "Leaderboard" and "Points" text on smaller screens */
        }

        .points-container .points {
            font-size: 24px; /* Optionally, increase the font size for the icon and number on small screens */
        }
    }
</style>

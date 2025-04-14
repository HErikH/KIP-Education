<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); // Start session if not already started
}

// Include the database connection
include "db_connect.php";

// Initialize default values for balance, email, role, and date_end_role
$balance = 0.0;
$userEmail = "";
$userRole = "";
$dateEndRole = "";

// Check if user is logged in
if (isset($_SESSION["user_id"])) {
  $userId = $_SESSION["user_id"];

  // SQL query to fetch user details
  $sql = "SELECT balance, email, role, date_end_role FROM users WHERE id = ?";

  // Prepare the statement
  if ($stmt = $conn->prepare($sql)) {
    // Bind the userId parameter to the query
    $stmt->bind_param("i", $userId);

    // Execute the statement
    if ($stmt->execute()) {
      // Bind the result to variables
      $stmt->bind_result($balance, $userEmail, $userRole, $dateEndRole);

      // Fetch the result
      if ($stmt->fetch()) {
        // Store role in session for later use
        $_SESSION["role"] = $userRole;
      } else {
        echo "User not found.";
      }
    } else {
      // Handle execution error
      echo "Error executing the query: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
  } else {
    // Handle error preparing the statement
    echo "Error preparing the query: " . $conn->error;
  }
}

// Close the database connection
$conn->close();
?>

<!-- Combined Header and Secondary Menu -->
<div class="header-wrapper">
    <!-- Navigation Bar -->
    <nav class="navbar">
        <!-- Logo on the Left -->
        <a href="index" class="navbar-brand">
            <img src="resource/img/logo.png" alt="Logo">
        </a>
        
        <!-- Navbar Links on the Left (Visible on Desktop) -->
<div class="navbar-links" id="navbar-links">
    <a href="about_us" class="menu-item <?php echo basename(
        $_SERVER["PHP_SELF"]
      ) == "about_us.php"
        ? "active"
        : ""; ?>">
          <i class="fas fa-users"></i> About Us
      </a>
    <a href="programms" class="menu-item programs <?php echo basename(
      $_SERVER["PHP_SELF"]
    ) == "programms.php"
      ? "active"
      : ""; ?>">
        <i class="fas fa-book"></i> Programs
    </a>
    <a href="quizzes" class="menu-item <?php echo basename(
      $_SERVER["PHP_SELF"]
    ) == "quizzes.php"
      ? "active"
      : ""; ?>">
        <i class="fas fa-question-circle"></i> Quizzes
    </a>
    <a href="contact" class="menu-item <?php echo basename(
      $_SERVER["PHP_SELF"]
    ) == "contact.php"
      ? "active"
      : ""; ?>">
        <i class="fas fa-envelope"></i> Contact Me
    </a>
    <a href="blog" class="menu-item <?php echo basename(
      $_SERVER["PHP_SELF"]
    ) == "blog.php"
      ? "active"
      : ""; ?>">
        <i class="fas fa-blog"></i> Blog
    </a>
</div>
        
        <!-- Profile, Balance, and Deposit Button on the Right (Visible on Desktop) -->
<div class="account-container">
            <?php if (isset($_SESSION["user_id"])): ?> 
                <!-- Countdown Container (Only if date_end_role is set) -->
                <?php if (!empty($dateEndRole)): ?>
                    <div class="countdown-container">
                        <div id="countdown"></div> <!-- Countdown placeholder -->
                    </div>
                <?php endif; ?>
            
                <!-- Balance and Deposit Container -->
                <div class="balance-deposit-container">
                    <!-- Deposit Button -->
                    <a href="deposit" class="deposit-button"><i class="fas fa-wallet"></i> Deposit</a>

                    <!-- Balance Display -->
                    <div class="balance-display">
                        <span><?php echo number_format(
                          $balance,
                          2
                        ); ?> AMD</span>
                    </div>
                </div>

                <!-- Profile Image -->
                <div class="profile-image">
                    <a href="profile">
                        <img src="resource/img/profile.png" alt="Profile Image">
                    </a>
                </div>
            <?php else: ?> 
                <!-- Only show My Account if user is not logged in -->
                <a href="login" class="account-button desktop-account-button"><i class="fas fa-user"></i> My Account</a>
            <?php endif; ?>
        </div>

        <!-- Mobile Icons: Hamburger Menu -->
        <div class="mobile-icons">
            <!-- Hamburger Menu Icon -->
            <span class="menu-toggle" id="menu-toggle"><i class="fas fa-bars"></i></span>
        </div>
    </nav>

<!-- Secondary Menu for Logged-in Users (Hidden on Mobile) -->
<?php if (isset($_SESSION["user_id"])): ?>
    <div class="secondary-menu">
        <!-- Secondary Menu Items -->
        <div class="secondary-menu-items">
            <?php 
                if (isset($show_back_button) && $show_back_button) {
                    echo '<a onclick="window.history.back()" class="exit-link">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>';
                }
            ?>
            <!-- Dashboard visible to guest and teachers -->
            <a href="profile" class="menu-item"><i class="fas fa-tachometer-alt"></i> Dashboard</a>

            <!-- For Teacher only visible to teachers -->
            <?php if (
              isset($_SESSION["role"]) &&
              $_SESSION["role"] === "teacher"
            ): ?>
                <a href="teachers" class="menu-item"><i class="fas fa-chalkboard-teacher"></i> For Teacher</a>
            <?php endif; ?>

            <!-- Lessons only visible to students -->
            <?php if (
              isset($_SESSION["role"]) &&
              $_SESSION["role"] === "student"
            ): ?>
                <a href="video" class="menu-item"><i class="fas fa-book-reader"></i> Lessons</a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

</div>

<!-- Sidebar for Mobile -->
<div class="sidebar" id="sidebar">
    <!-- Close Button -->
    <span class="close-sidebar" id="close-sidebar">&times;</span>

    <!-- User Information Section -->
    <?php if (isset($_SESSION["user_id"])): ?>
    <div class="user-info">
        <div class="profile-image">
            <a href="profile">
                <img src="resource/img/profile.png" alt="Profile Image">
            </a>
        </div>
        <div class="user-details">
            <span class="user-email"><?php echo htmlspecialchars(
              $userEmail
            ); ?></span>
            <span class="user-id">ID: <?php echo htmlspecialchars(
              $_SESSION["user_id"]
            ); ?></span>
            <div class="balance-display">
                <span><?php echo number_format($balance, 2); ?> AMD</span>
            </div>
            <a href="deposit" class="deposit-button">Deposit</a>
        </div>
    </div>
    <?php endif; ?>

   <!-- Top Section (Dashboard, Programs, Contact, Blog, For Teacher, Lessons) -->
<nav class="menu d-flex flex-column">
    <!-- Dashboard visible to logged-in users only -->
    <?php if (isset($_SESSION["user_id"])): ?>
        <a href="profile" class="menu-item"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <?php endif; ?>

    <a href="about_us" class="menu-item"><i class="fas fa-users"></i> About Us</a>
    <a href="programms" class="menu-item"><i class="fas fa-book"></i> Programs</a>
    <a href="quizzes" class="menu-item"><i class="fas fa-question-circle"></i> Quizzes</a>
    <a href="contact" class="menu-item"><i class="fas fa-envelope"></i> Contact Me</a>
    <a href="blog" class="menu-item"><i class="fas fa-blog"></i> Blog</a>

    <!-- For Teacher only visible to teachers -->
    <?php if (isset($_SESSION["role"]) && $_SESSION["role"] === "teacher"): ?>
        <a href="teachers" class="menu-item"><i class="fas fa-chalkboard-teacher"></i> For Teacher</a>
    <?php endif; ?>

    <!-- Lessons only visible to students -->
    <?php if (isset($_SESSION["role"]) && $_SESSION["role"] === "student"): ?>
        <a href="video" class="menu-item"><i class="fas fa-book-reader"></i> Lessons</a>
    <?php endif; ?>
</nav>

    <!-- Log Out Button -->
    <?php if (isset($_SESSION["user_id"])): ?>
    <div class="menu-item logout-item">
        <a href="logout">
            <i class="fas fa-sign-out-alt"></i> Log Out
        </a>
    </div>
    <?php endif; ?>

    <!-- My Account at the Bottom (Only show if user is not logged in) -->
    <?php if (!isset($_SESSION["user_id"])): ?>
    <div class="menu-item account-sidebar-item">
        <a href="login">
            <i class="fas fa-user"></i> My Account
        </a>
    </div>
    <?php endif; ?>
</div>


<!-- JavaScript for toggling the hamburger menu and sidebar -->
<script>
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');
    const closeSidebar = document.getElementById('close-sidebar');

    menuToggle.addEventListener('click', function() {
        sidebar.classList.toggle('active');
    });

    closeSidebar.addEventListener('click', function() {
        sidebar.classList.remove('active');
    });
</script>
<script>
<?php if (!empty($dateEndRole)): ?>
    // Set the end date and time from PHP
    var countDownDate = new Date("<?php echo $dateEndRole; ?>").getTime();

    // Update the countdown every 1 second
    var countdownFunction = setInterval(function() {
        var now = new Date().getTime();
        var distance = countDownDate - now;

        // Time calculations for months and days only
        var months = Math.floor(distance / (1000 * 60 * 60 * 24 * 30.44)); // Approximate months
        var days = Math.floor((distance % (1000 * 60 * 60 * 24 * 30.44)) / (1000 * 60 * 60 * 24));

        // Check if the screen is smaller than 768px
        if (window.innerWidth < 768) {
            // Display in short format for mobile
            document.getElementById("countdown").innerHTML = 
                months + " m. " + days + " d.";
        } else {
            // Display in full format for larger screens
            document.getElementById("countdown").innerHTML = 
                months + " months " + days + " days ";
        }

        // If the countdown is finished, display an expired message
        if (distance < 0) {
            clearInterval(countdownFunction);
            document.getElementById("countdown").innerHTML = "EXPIRED";

            // Send AJAX request to update the role to 'guest'
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "update_role_to_guest.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send("user_id=<?php echo $userId; ?>");
        }
    }, 1000);
<?php endif; ?>



</script>



<style>
/* Countdown Container Style */
.countdown-container {
    background-color: #34495e;
    padding: 10px 20px;
    border-radius: 5px;
    color: #ecf0f1;
    display: inline-block;
    font-size: 18px;
    font-weight: bold;
    text-align: center;
    margin-right: 15px; /* Adjust spacing as needed */
}

#countdown {
    width: 120px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: #f3ed17; /* Color for the countdown text */
    font-size: 20px;
    font-weight: bold;
}

#countdown:hover {
    white-space: wrap;
    overflow: visible;
    text-overflow: unset;
}


    /* Combined Wrapper Styling */
    .header-wrapper {
        width: 100%;
        position: fixed; /* Fix the header to the top */
        top: 0; /* Position at the very top */
        left: 0;
        z-index: 1000; /* Ensure it's on top of other elements */
        background-color: #34495e; /* Optional: set background color */
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Optional: add shadow for depth */
    }

    /* Header Styling */
    .navbar {
        background-color: #34495e;
        padding: 10px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        position: relative;
        top: 0;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

.navbar-brand {
    display: flex;
    align-items: center;
    font-size: 22px;
    font-weight: bold;
    color: #ecf0f1;
    background-color: white; /* Լոգոյի սպիտակ ֆոն */
    padding: 5px; /* Փոքր padding, որպեսզի լոգոն գեղեցիկ տեղավորվի */
    border-radius: 5px; /* Կլորացված անկյուններ */
}

.navbar-brand img {
    height: 40px;
    margin-right: 10px;
    background-color: white; /* Ֆոնը դարձնում ենք սպիտակ */
    border-radius: 5px; /* Կլորացնում ենք անկյունները */
}


    /* Navbar Links (Visible on Desktop) */
    .navbar-links {
        display: flex;
        align-items: center;
        justify-content: flex-start; /* Move links to the left */
        flex-grow: 1;
    }

    .navbar-links a {
        color: #ecf0f1;
        margin-left: 20px;
        font-size: 16px;
        transition: color 0.3s ease, background-color 0.3s ease;
        padding: 8px 15px;
        border-radius: 5px;
        text-decoration: none;
    }

    /* Icon Spacing */
    .navbar-links a i {
        margin-right: 8px; /* Spacing between icon and text */
    }

    .navbar-links a:hover {
        color: #34495e;
        background-color: #bdc3c7;
    }

    /* Profile, Balance and Deposit Button Styling */
    .account-container {
        margin-left: auto;
        display: flex;
        align-items: center;
    }

    .balance-deposit-container {
        display: flex;
        align-items: center;
        background-color: #ffffff1a; /* Container background for spacing */
        padding: 5px 20px 5px 5px;
        border-radius: 8px;
        margin-right: 20px;
        min-width: 180px; /* Increased width for better appearance */
    }

    .deposit-button {
        background-color: #f3ed17; /* Background for Deposit */
        color: white;
        padding: 10px 15px;
        border-radius: 5px;
        font-weight: bold;
        text-decoration: none;
        display: flex;
        align-items: center;
        transition: background-color 0.3s ease;
        margin-right: 15px; /* Increased spacing from the balance text */
    }
    
    .deposit-button i {
    margin-right: 12px; /* Ավելացրեք կամ փոփոխեք այս արժեքը ըստ ձեր նախընտրության */
}

    .deposit-button:hover {
        background-color: #f3ed17; /* Keep the same color */
        opacity: 0.85; /* Slight transparency effect on hover */
        color: white;
    }

    .balance-display {
        color: white;
        font-size: 16px;
        padding: 0 10px; /* Spacing inside the balance display */
    }

    .profile-image img {
        height: 40px;
        width: 40px;
        border-radius: 50%;
        border: 2px solid #ecf0f1;
        margin-right: 0px;
    }

    /* Secondary Menu Styling */
    .secondary-menu {
        background-color: #2c3e50;
        padding: 10px 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .secondary-menu-items {
        display: flex;
        align-items: center;
    }

    .secondary-menu .menu-item {
        color: #ecf0f1;
        margin-right: 20px;
        font-size: 16px;
        text-decoration: none;
        display: flex;
        align-items: center;
        padding: 8px 15px;
        transition: background-color 0.3s ease;
        border-radius: 5px;
    }

    .secondary-menu .menu-item i {
        margin-right: 8px;
    }

    .secondary-menu .menu-item:hover {
        background-color: #bdc3c7;
        color: #34495e;
    }

    .exit-link {
        position: absolute;
        color: #ffffff;
        text-decoration: none;
        font-size: 16px;
        margin-right: 20px;
        cursor: pointer;
        left: 2rem;
    }

    .exit-link:hover {
        color: #ff9800;
        text-decoration: none;
    }

    .exit-link i {
        margin-right: 8px;
    }

    /* Hide balance-deposit-container on mobile */
    @media (max-width: 768px) {
        .balance-deposit-container {
            display: none; /* Hide balance-deposit-container on mobile */
        }

        .secondary-menu {
            display: none;
        }
    }

    /* Mobile Icons */
    .mobile-icons {
        display: flex;
        align-items: center;
    }

    /* Hamburger Menu Icon */
    .menu-toggle {
        font-size: 24px;
        cursor: pointer;
        color: #ecf0f1;
        display: none;
    }

/* Sidebar Styling */
.sidebar {
    display: none;
    position: fixed;
    top: 0;
    right: 0;
    height: 100%;
    width: 70%; /* Set width to 70% */
    max-width: 300px; /* Optional: Set a max width */
    background-color: #34495e;
    box-shadow: -4px 0 6px rgba(0, 0, 0, 0.1);
    z-index: 1001;
    padding-top: 60px;
    transition: transform 0.3s ease;
}

.sidebar.active {
    display: block;
    transform: translateX(0);
}

    /* Close Button Styling */
    .close-sidebar {
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 24px;
        color: white;
        cursor: pointer;
    }

    /* Sidebar Menu Items */
    .menu {
        display: flex;
        flex-direction: column;
        max-height: 300px;
        padding: 1rem 0;
        overflow: auto;
    }

    .menu-item {
        display: block;
        padding: 15px;
        text-transform: uppercase;
        color: #ecf0f1;
        text-decoration: none;
        font-size: 18px;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .menu-item i {
        margin-right: 8px;
    }

    .menu-item:hover {
        background-color: #bdc3c7;
        color: #34495e;
    }

    /* Sidebar Top Section */
    .account-sidebar-item {
        background-color: #2980b9;
        color: white;
        padding: 15px;
        text-align: center;
        font-size: 18px;
        border-radius: 5px;
        margin-bottom: 10px;
    }

    .account-sidebar-item a {
        color: white;
        text-decoration: none;
        font-weight: bold;
    }

    .account-sidebar-item a i {
        margin-right: 8px;
    }

    /* Media Query for mobile screens */
    @media (max-width: 768px) {
        .menu-toggle {
            display: block;
        }

        .navbar-links {
            display: none;
        }

        .desktop-account-button {
            display: none;
        }
    }
    
    /* User Info Styling in Sidebar */
.user-info {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 15px;
    background-color: #34495e;
    border-bottom: 1px solid #2c3e50;
}

.profile-image {
    margin-right: 10px;
}

.profile-image img {
    height: 50px;
    width: 50px;
    border-radius: 50%;
    border: 2px solid #ecf0f1;
}

.user-details {
    color: #ecf0f1;
}

.user-email {
    font-size: 16px;
    font-weight: bold;
}

.user-id {
    font-size: 14px;
    margin-bottom: 5px;
}

.balance-display {
    color: #ecf0f1;
    font-size: 16px;
    margin-bottom: 5px;
}

.deposit-button {
    background-color: #f3ed17; /* Background for Deposit */
    color: white;
    padding: 5px 10px;
    border-radius: 5px;
    font-weight: bold;
    text-decoration: none;
    transition: background-color 0.3s ease;
}

.deposit-button:hover {
    background-color: #27ae60; /* Darker green on hover */
    text-decoration: none;
}

/* Log Out Button Styling */
.logout-item {
    position: absolute;
    width: 100%;
    bottom: 0;
    padding: 15px;
    background-color: #e74c3c; /* Red background for Log Out */
    border-radius: 5px;
    text-align: center;
}

.logout-item a {
    color: white;
    text-decoration: none;
    font-weight: bold;
    display: flex;
    align-items: center;
}

.logout-item a i {
    margin-right: 8px; /* Spacing for icon */
}

.logout-item:hover {
    background-color: #c0392b; /* Darker red on hover */
}
/* My Account Button Styling */
.desktop-account-button {
    background-color: #3498db; /* Blue background for My Account */
    color: white;
    padding: 10px 20px;
    border-radius: 5px;
    font-weight: bold;
    border: none;
    transition: background-color 0.3s ease, transform 0.2s ease; /* Transition effects */
    text-decoration: none; /* Remove underline */
    display: inline-flex; /* Align icon and text */
    align-items: center; /* Center the icon vertically */
    margin-right: 10px;
}

.desktop-account-button:hover {
    background-color: #2980b9; /* Darker blue on hover */
    transform: scale(1.05); /* Slightly enlarge on hover */
    color:white;
}

.desktop-account-button i {
    margin-right: 8px; /* Space between icon and text */
}

.menu-item.programs {
    position: relative;
    padding: 10px 20px;
    font-size: 16px;
    text-decoration: none;
    color: #ecf0f1;
    border-radius: 5px;
    transition: color 0.3s ease;
    animation: border-animation 1.5s infinite; /* Infinite loop */
}

.menu-item.programs:hover {
    color: #34495e;
    background-color: #bdc3c7;
    animation: none; /* Երբ մկնիկը hover է, անիմացիան դադարեցնում ենք */
}

.menu-item.active {
    background-color: #2980b9; /* Կոճակի ակտիվ վիճակի գույն */
    border: 2px solid #2980b9; /* Ակտիվ վիճակի սահման */
    animation: none; /* Ակտիվ վիճակում անիմացիան դադարեցվում է */
}

@keyframes border-animation {
    0% {
        border: 2px solid transparent;
    }
    50% {
        border: 2px solid #3498db; /* Կարգավորեք գույնը ձեր ցանկությամբ */
    }
    100% {
        border: 2px solid transparent;
    }
}
</style>

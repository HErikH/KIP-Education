<?php
session_start();
?>

<!-- User-specific Navigation Bar -->
<nav class="navbar">
    <!-- Logo on the Left -->
    <a href="index.php" class="navbar-brand">
        <img src="resource/img/logo.png" alt="Logo"> Kipeducation.am
    </a>
    
    <!-- Navbar Links for the User -->
    <ul class="navbar-links" id="navbar-links">
        <li><a href="profileuser.php" class="menu-link"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="video.php" class="menu-link"><i class="fas fa-video"></i> Lessons</a></li>
        <li><a href="logout.php" class="menu-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>

    <!-- User Profile Image -->
<div class="user-profile">
    <a href="profileuser.php">
        <img src="resource/img/profile.png" alt="User Image" class="user-image">
    </a>
</div>

    
    <!-- Hamburger Menu Icon for Mobile -->
    <span class="menu-toggle" id="menu-toggle"><i class="fas fa-bars"></i></span>
</nav>

<!-- Sidebar for Mobile -->
<div class="sidebar" id="sidebar">
    <!-- Close Button for Sidebar -->
    <span class="close-sidebar" id="close-sidebar">&times;</span>
    
    <ul class="menu d-flex flex-column">
        <li><a href="profileuser.php" class="menu-link"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="video.php" class="menu-link"><i class="fas fa-video"></i> Lessons</a></li>
        <li><a href="logout.php" class="menu-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
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

    // Optional: Close sidebar when clicking outside of it
    window.addEventListener('click', function(e) {
        if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
            sidebar.classList.remove('active');
        }
    });
</script>

<style>
   /* General Styles */
   body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

.navbar {
    background-color: #34495e;
    padding: 0 20px; /* Reduce padding to 0 vertically for better centering */
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: fixed;
    width: 100%;
    top: 0;
    z-index: 1000;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    height: 70px; /* Height of the navbar */
}

.navbar-brand,
.navbar-links,
.user-profile {
    display: flex;
    align-items: center; /* Vertically center items within their container */
}

.navbar-brand {
    justify-content: center; /* Center horizontally */
    font-size: 18px;
    font-weight: bold;
    color: #ecf0f1;
    text-transform: uppercase;
}

.navbar-brand img {
    height: 35px; /* Adjust the logo size */
    margin-right: 10px;
}
    .navbar-links {
    display: flex;
    margin-top: 10px;
    align-items: center; /* Center the links vertically */
    justify-content: center; /* Center the links horizontally */
    flex-grow: 1; /* Allow links to grow and center with respect to the other content */
}

.navbar-links li {
    list-style: none;
    margin-left: 15px;
}

    .navbar-links a {
        color: #ecf0f1;
        font-size: 14px;
        text-decoration: none;
        padding: 6px 12px;
        border-radius: 5px;
        transition: color 0.3s ease, background-color 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .navbar-links a:hover {
        color: #34495e;
        background-color: #bdc3c7;
    }

    .navbar .menu-toggle {
        display: none;
        font-size: 24px;
        cursor: pointer;
        color: #ecf0f1;
    }

    /* User Image in Circle */
    .user-profile {
        display: flex;
        align-items: center;
    }

    .user-image {
        width: 45px;
        height: 45px;
        margin-bottom: 5px;
        border-radius: 50%;
        border: 2px solid #ecf0f1;
        object-fit: cover;
    }

    /* Sidebar Styling */
    .sidebar {
        display: none;
        position: fixed;
        top: 0;
        right: 0;
        height: 100%;
        width: 250px;
        background-color: #34495e;
        box-shadow: -4px 0 6px rgba(0, 0, 0, 0.1);
        z-index: 1001;
        padding-top: 60px;
        transition: transform 0.3s ease;
    }

    /* Close Button Styling */
    .close-sidebar {
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 24px;
        cursor: pointer;
        color: white;
    }

    .sidebar.active {
        display: block;
    }

    .menu-link {
        display: block;
        padding: 15px;
        text-transform: uppercase;
        color: #ecf0f1;
        text-decoration: none;
        font-size: 18px;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .menu-link:hover {
        background-color: #bdc3c7;
        color: #34495e;
    }

    /* Mobile Responsive Styles */
    @media (max-width: 768px) {
        .navbar-links {
            display: none;
        }

        .navbar .menu-toggle {
            display: block;
        }

        .navbar {
            height: 70px;
        }

        .sidebar {
            transform: translateX(100%);
            transition: transform 0.3s ease;
        }

        .sidebar.active {
            transform: translateX(0);
        }

        /* Adjust user profile and logo for mobile */
        .user-profile {
            margin-right: 10px;
        }

        .navbar-brand {
            flex: 1;
        }

        .menu-toggle {
            margin-right: 10px;
        }
    }
</style>

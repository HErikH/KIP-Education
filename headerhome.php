<?php
// headerhome.php
require_once 'helpers.php';
?>

<!-- Navigation Bar -->
<nav class="navbar">
    <!-- Logo on the Left -->
    <a href="index" class="navbar-brand">
        <img src="<?= addMediaBaseUrl('resource/img/logo.png') ?>" alt="Logo">
    </a>
    
    <!-- Navbar Links on the Left (Visible on Desktop) -->
    <div class="navbar-links" id="navbar-links">
        <a href="programms" class="menu-item"><i class="fas fa-book"></i> Programs</a>
        <a href="contact" class="menu-item"><i class="fas fa-envelope"></i> Contact Me</a>
        <a href="blog" class="menu-item"><i class="fas fa-blog"></i> Blog</a>
    </div>
    
    <!-- My Account Button on the Right (Visible on Desktop) -->
    <div class="account-container">
        <a href="login" class="account-button desktop-account-button"><i class="fas fa-user"></i> My Account</a>
    </div>
    
    <!-- Mobile Icons: User Icon and Hamburger Menu -->
    <div class="mobile-icons">
        <!-- User Icon in Circle (Visible on Mobile Only) -->
        <div class="user-icon-circle">
            <a href="login">
                <img src="<?= addMediaBaseUrl('resource/img/user2.png') ?>" alt="User Icon">
            </a>
        </div>

        <!-- Hamburger Menu Icon -->
        <span class="menu-toggle" id="menu-toggle"><i class="fas fa-bars"></i></span>
    </div>
</nav>

<!-- Sidebar for Mobile -->
<div class="sidebar" id="sidebar">
    <!-- Close Button -->
    <span class="close-sidebar" id="close-sidebar">&times;</span>

    <!-- My Account with different design in Sidebar -->
    <div class="menu-item account-sidebar-item">
        <a href="login">
            <i class="fas fa-user"></i> My Account
        </a>
    </div>

    <nav class="menu d-flex flex-column">
        <a href="programms" class="menu-item"><i class="fas fa-book"></i> Programs</a>
        <a href="contact" class="menu-item"><i class="fas fa-envelope"></i> Contact Me</a>
        <a href="blog" class="menu-item"><i class="fas fa-blog"></i> Blog</a>
    </nav>
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

<style>
    /* Header Styling */
    .navbar {
        background-color: #34495e;
        padding: 10px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: fixed;
        width: 100%;
        top: 0;
        z-index: 1000;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .navbar-brand {
        display: flex;
        align-items: center;
        font-size: 22px;
        font-weight: bold;
        color: #ecf0f1;
    }

    .navbar-brand img {
        height: 40px;
        margin-right: 10px;
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
    }

    .navbar-links a:hover {
        color: #34495e;
        background-color: #bdc3c7;
    }

    /* My Account Button Container */
    .account-container {
        margin-left: auto; /* Align the My Account button to the right */
    }

    /* Mobile Icons */
    .mobile-icons {
        display: flex;
        align-items: center;
    }

    /* User Icon in Circle (Visible only on Mobile) */
    .user-icon-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        overflow: hidden;
        margin-right: 10px;
        display: none;
        justify-content: center;
        align-items: center;
        background-color: #2980b9; /* Blue background for contrast */
        border: 2px groove #ffffff; /* Groove styled white border */
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2); /* Soft shadow for depth */
        transition: transform 0.3s ease, box-shadow 0.3s ease; /* Smooth animation */
    }

    .user-icon-circle:hover {
        transform: scale(1.1); /* Slightly increase size on hover */
        box-shadow: 0px 6px 12px rgba(0, 0, 0, 0.3); /* Stronger shadow on hover */
    }

    /* Center the icon inside the circle */
    .user-icon-circle img {
        width: 22px; /* Slightly larger icon for better visibility */
        height: 22px;
        display: block;
        margin: 0 auto; /* Center the image horizontally */
        color: #ffffff; /* Ensure the icon is white */
        transition: transform 0.3s ease; /* Smooth animation */
    }

    .user-icon-circle:hover img {
        transform: scale(1.1); /* Slight increase in icon size on hover */
    }

    /* Hamburger Menu Icon */
    .menu-toggle {
        font-size: 24px;
        cursor: pointer;
        color: #ecf0f1;
        display: none; /* Hidden on desktop */
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

    /* My Account Styling in Sidebar */
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

    /* Sidebar Menu Items */
    .menu {
        display: flex;
        flex-direction: column;
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

    .menu-item:hover {
        background-color: #bdc3c7;
        color: #34495e;
    }

    /* My Account Button Styling for Desktop */
    .desktop-account-button {
        background-color: #f39c12; /* Orange background for My Account */
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        font-weight: bold;
        border: none;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .desktop-account-button:hover {
        background-color: #e67e22; /* Darker orange on hover */
        color: #34495e;
    }

    /* Media Query for mobile screens */
    @media (max-width: 768px) {
        .menu-toggle {
            display: block; /* Visible on mobile */
        }

        .navbar-links {
            display: none; /* Hide links on mobile */
        }

        /* Show User Icon on Mobile */
        .user-icon-circle {
            display: flex; /* Visible only on mobile */
        }

        .desktop-account-button {
            display: none; /* Hide My Account on mobile */
        }
    }
</style>

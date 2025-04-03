<?php
// Получение текущего имени страницы для определения активного элемента меню
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="admin.php">
            <img src="resource/img/logo.png" alt="Logo" style="height: 40px; margin-right: 10px;"> Admin Panel
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'admin.php') ? 'active' : ''; ?>" href="admin.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'users.php') ? 'active' : ''; ?>" href="users.php">Users</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>" href="reports.php">Reports</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'lessons.php') ? 'active' : ''; ?>" href="lessons.php">Lessons</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'programmsadmin.php') ? 'active' : ''; ?>" href="programmsadmin.php">Programms</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'quizzesadmin.php') ? 'active' : ''; ?>" href="quizzesadmin.php">Quizzes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'blogadmin.php') ? 'active' : ''; ?>" href="blogadmin.php">Blog</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>" href="settings.php">Settings</a>
                </li>
                <!-- New menu item for Child Admin -->
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'childadmin.php') ? 'active' : ''; ?>" href="childadmin.php">Child Admin</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Стилизация активного элемента и меню -->
<style>
    .navbar {
        background-color: #343a40;
        padding: 15px;
    }
    .navbar-brand {
        font-weight: bold;
        font-size: 24px;
    }
    .navbar-nav .nav-link {
        color: #ffffff;
        padding: 10px 15px;
        font-size: 18px;
        transition: background-color 0.3s, color 0.3s;
    }
    .navbar-nav .nav-link:hover {
        background-color: #495057;
        color: #ffffff;
        border-radius: 5px;
    }
    .navbar-nav .nav-link.active {
        background-color: #007bff;
        color: #ffffff !important;
        border-radius: 5px;
    }

    /* Sidebar Active for Mobile */
    @media (max-width: 768px) {
        .navbar {
            padding: 10px;
        }
        .navbar-brand {
            font-size: 20px;
        }
        .navbar-nav .nav-link {
            font-size: 16px;
            padding: 8px 10px;
        }
    }
</style>

<!-- Include jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

<!-- Include Popper.js (necessary for Bootstrap 4) -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>

<!-- Include Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

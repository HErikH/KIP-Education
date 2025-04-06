<?php
session_start();
include 'db_connect.php';

// Проверка сессии
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index");
    exit();
}

// Закрытие соединения с БД
$conn->close();

// Включение headeradmin.php
include 'headeradmin.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Settings</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .content {
            padding: 20px;
        }
        .content-header {
            margin-bottom: 20px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
        }
        .card {
            border-radius: 10px;
        }
        .card-header {
            background-color: #007bff;
            color: white;
            border-radius: 10px 10px 0 0;
            font-size: 18px;
        }
        .btn-custom {
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .btn-custom:hover {
            background-color: #0056b3;
        }
        .form-group {
            margin-bottom: 15px;
        }
        /* Mobile Styles */
        @media (max-width: 768px) {
            .content {
                padding: 10px;
            }
        }
    </style>
</head>
<body>

    <div class="content">
        <div class="content-header">
            <h1>Settings</h1>
            <p>Manage your website settings here.</p>
        </div>

        <!-- General Settings Card -->
        <div class="card mt-4">
            <div class="card-header">General Settings</div>
            <div class="card-body">
                <form action="update_settings.php" method="POST">
                    <div class="form-group">
                        <label for="site_title">Site Title</label>
                        <input type="text" class="form-control" id="site_title" name="site_title" value="My Website" required>
                    </div>
                    <div class="form-group">
                        <label for="admin_email">Admin Email</label>
                        <input type="email" class="form-control" id="admin_email" name="admin_email" value="admin@example.com" required>
                    </div>
                    <div class="form-group">
                        <label for="timezone">Timezone</label>
                        <select class="form-control" id="timezone" name="timezone" required>
                            <option value="UTC">UTC</option>
                            <option value="Europe/Moscow">Europe/Moscow</option>
                            <option value="Asia/Yerevan">Asia/Yerevan</option>
                            <!-- Add more timezones as necessary -->
                        </select>
                    </div>
                    <button type="submit" class="btn btn-custom">Save Settings</button>
                </form>
            </div>
        </div>

        <!-- Security Settings Card -->
        <div class="card mt-4">
            <div class="card-header">Security Settings</div>
            <div class="card-body">
                <form action="update_security.php" method="POST">
                    <div class="form-group">
                        <label for="password_length">Minimum Password Length</label>
                        <input type="number" class="form-control" id="password_length" name="password_length" value="8" required>
                    </div>
                    <div class="form-group">
                        <label for="enable_2fa">Enable Two-Factor Authentication</label>
                        <select class="form-control" id="enable_2fa" name="enable_2fa" required>
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-custom">Save Security Settings</button>
                </form>
            </div>
        </div>
        
        <!-- Time Settings Card -->
        <div class="card mt-4">
            <div class="card-header">Time Settings</div>
            <div class="card-body">
                <form action="update_time_settings.php" method="POST">
                    <div class="form-group">
                        <label for="lesson_duration">Lesson Duration</label>
                        <select class="form-control" id="lesson_duration" name="lesson_duration" required>
                            <option value="10_seconds">10 seconds</option>
                            <option value="1_minute">1 minute</option>
                            <option value="10_minutes">10 minutes</option>
                            <option value="60_minutes">60 minutes</option>
                            <option value="3_hours">3 hours</option>
                            <option value="12_hours">12 hours</option>
                            <option value="48_hours">48 hours</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-custom">Save Time Settings</button>
                </form>
            </div>
        </div>

        <!-- Appearance Settings Card -->
        <div class="card mt-4">
            <div class="card-header">Appearance Settings</div>
            <div class="card-body">
                <form action="update_appearance.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="logo">Site Logo</label>
                        <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label for="theme_color">Theme Color</label>
                        <input type="color" class="form-control" id="theme_color" name="theme_color" value="#007bff">
                    </div>
                    <button type="submit" class="btn btn-custom">Save Appearance Settings</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (Optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

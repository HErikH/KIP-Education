<?php
session_start(); // Добавляем session_start() в самом начале

// Проверка авторизации и роли
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Если пользователь не авторизован, перенаправляем на страницу логина
    header("Location: login");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    // Если пользователь авторизован, но не админ, показываем ошибку или перенаправляем
    header("Location: index");
    exit();
}

// Include database connection

include 'db_connect.php';



// Fetch total number of users from the 'students' table

$userCountQuery = "SELECT COUNT(*) as user_count FROM students";

$userCountResult = $conn->query($userCountQuery);

$userCount = $userCountResult->fetch_assoc()['user_count'];



// Fetch total number of reports from the 'reports' table

$reportCountQuery = "SELECT COUNT(*) as report_count FROM reports";

$reportCountResult = $conn->query($reportCountQuery);

$reportCount = $reportCountResult->fetch_assoc()['report_count'];



// Fetch recent activities (for example, from a 'logs' or 'activities' table)

// $recentActivitiesQuery = "SELECT * FROM reports ORDER BY activity_date DESC LIMIT 10";

// $recentActivitiesResult = $conn->query($recentActivitiesQuery);

$recentActivitiesQuery = "SELECT * FROM reports ORDER BY date_created DESC LIMIT 10";

$recentActivitiesResult = $conn->query($recentActivitiesQuery);


include 'headeradmin.php';



?>





<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Panel - Dashboard</title>

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

        .table-striped > tbody > tr:nth-of-type(odd) {

            --bs-table-accent-bg: #e9ecef;

            color: #495057;

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



    

    <!-- Content -->

    <div class="content">

        <div class="content-header">

            <h1>Dashboard</h1>

            <p>Manage your dashboard</p>
            <?php if (isset($_SESSION['welcome_message'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['welcome_message']; unset($_SESSION['welcome_message']); ?>
                </div>
            <?php endif; ?>
        </div>



        <div class="row">

            <div class="col-md-4">

                <div class="card">

                    <div class="card-header">Users</div>

                    <div class="card-body">

                        <h5 class="card-title">Total Users: <?php echo $userCount; ?></h5>

                        <a href="users.php" class="btn btn-custom">View Users</a>

                    </div>

                </div>

            </div>

            <div class="col-md-4">

                <div class="card">

                    <div class="card-header">Reports</div>

                    <div class="card-body">

                        <h5 class="card-title">New Reports: <?php echo $reportCount; ?></h5>

                        <a href="reports.php" class="btn btn-custom">View Reports</a>

                    </div>

                </div>

            </div>

            <div class="col-md-4">

                <div class="card">

                    <div class="card-header">Settings</div>

                    <div class="card-body">

                        <h5 class="card-title">Manage Settings</h5>

                        <a href="settings.php" class="btn btn-custom">Go to Settings</a>

                    </div>

                </div>

            </div>

        </div>



        <div class="mt-4">

            <div class="card">

                <div class="card-header">Recent Activities</div>

                <div class="card-body">

                    <table class="table table-striped">

                        <thead>

                            <tr>

                                <th scope="col">#</th>

                                <th scope="col">User</th>

                                <th scope="col">Activity</th>

                                <th scope="col">Date</th>

                            </tr>

                            <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">User ID</th>
                                <th scope="col">Report Name</th>
                                <th scope="col">Date Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recentActivitiesResult && $recentActivitiesResult->num_rows > 0): ?>
                                <?php while ($activity = $recentActivitiesResult->fetch_assoc()): ?>
                                    <tr>
                                        <th scope="row"><?php echo $activity['id']; ?></th>
                                        <td><?php echo $activity['user_id']; ?></td>
                                        <td><?php echo $activity['report_name']; ?></td>
                                        <td><?php echo $activity['date_created']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4">No recent reports.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        
                    </table>

                </div>

            </div>

        </div>

    </div>



    <!-- Bootstrap JS (Optional) -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>


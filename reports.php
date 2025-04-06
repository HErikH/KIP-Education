<?php
session_start();
include 'db_connect.php'; // Connect to the database

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Проверка сессии
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index");
    exit();
}

// Проверка, если форма отправлена
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post_id = $_POST['post_id'];
    $reason = $_POST['reason'];

    // Проверка, что поля не пустые
    if (!empty($post_id) && !empty($reason)) {
        // Получаем user_id из сессии
        $user_id = $_SESSION['user_id'];

        // Формируем название отчета
        $report_name = "Activation Request for Post ID: " . $post_id;

        // Вставляем данные в базу данных с user_id и post_id
        $stmt = $conn->prepare("INSERT INTO reports (report_name, date_created, reason, user_id, post_id) VALUES (?, NOW(), ?, ?, ?)");
        $stmt->bind_param("ssii", $report_name, $reason, $user_id, $post_id);

        if ($stmt->execute()) {
            echo "<script>alert('Report successfully submitted!');</script>";
        } else {
            echo "<script>alert('Error submitting report.');</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Please fill all fields.');</script>";
    }
}

// Получение общего количества отчетов
$reportCountResult = $conn->query("SELECT COUNT(*) as total FROM reports");
if ($reportCountResult) {
    $reportCount = $reportCountResult->fetch_assoc()['total'];
} else {
    $reportCount = 0; // Handle the error gracefully
}

// Получение списка отчетов с user_id и post_id
$reportsResult = $conn->query("SELECT id, report_name, date_created, reason, user_id, post_id FROM reports ORDER BY date_created DESC");

// Check for errors in the query
if (!$reportsResult) {
    die("Query failed: " . $conn->error);
}

include 'headeradmin.php';

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f7f9fc;
        }
        .container {
            margin-top: 50px;
        }
        h1 {
            color: #343a40;
            text-align: center;
            margin-bottom: 30px;
        }
        table {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
            border-radius: 8px;
        }
        .table th {
            background-color: #3498db;
            color: white;
            text-align: center;
        }
        .table td {
            text-align: center;
        }
        .btn-approve {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .btn-approve:hover {
            background-color: #218838;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .btn-delete:hover {
            background-color: #c82333;
        }
        .btn-action {
            margin-right: 10px;
        }
        .card-header {
            background-color: #3498db;
            color: white;
            font-size: 24px;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>



<div class="container">
    <div class="card">
        <div class="card-header">
            Reports List
        </div>
        <div class="card-body">
            <?php
            // Получение отчетов из базы данных
            if ($reportsResult && $reportsResult->num_rows > 0): ?>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Report Name</th>
                        <th>Date Created</th>
                        <th>Reason</th>
                        <th>User ID</th> <!-- Добавляем колонку User ID -->
                        <th>Post ID</th> <!-- Добавляем колонку Post ID -->
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($report = $reportsResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $report['id']; ?></td>
                        <td><?php echo $report['report_name']; ?></td>
                        <td><?php echo $report['date_created']; ?></td>
                        <td><?php echo $report['reason']; ?></td>
                        <td><?php echo $report['user_id']; ?></td> <!-- Выводим User ID -->
                        <td><?php echo $report['post_id']; ?></td> <!-- Выводим Post ID -->
                        <td>
                            <!-- Approve Button -->
                            <button class="btn-approve btn-action" onclick="approveReport(<?php echo $report['id']; ?>)">Approve</button>

                            <!-- Delete Button -->
                            <button class="btn-delete btn-action" onclick="deleteReport(<?php echo $report['id']; ?>)">Delete</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p>No reports found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Approve/Delete Report using AJAX -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function approveReport(reportId) {
    if (confirm('Are you sure you want to approve this report?')) {
        $.ajax({
            url: 'approve_report.php', // Файл, который активирует урок
            type: 'POST',
            data: { id: reportId }, // Отправляем ID отчета
            success: function(response) {
                if (response === "success") {
                    alert('Lesson activated successfully');
                    location.reload(); // После активации обновляем страницу
                } else {
                    alert('An error occurred: ' + response); // Показываем ошибку, если она есть
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('An error occurred while approving the report: ' + textStatus + " - " + errorThrown);
            }
        });
    }
}
function deleteReport(reportId) {
    if (confirm('Are you sure you want to delete this report?')) {
        $.ajax({
            url: 'delete_report.php',
            type: 'POST',
            data: { id: reportId },
            success: function(response) {
                alert('Report deleted successfully');
                location.reload(); // Reload the page after success
            },
            error: function() {
                alert('An error occurred while deleting the report.');
            }
        });
    }
}
</script>

</body>
</html>

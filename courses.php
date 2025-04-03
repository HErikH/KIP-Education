<?php
session_start();
include 'db_connect.php';

// Check if the session is valid
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}

// Get the total number of lessons
$lessonCountResult = $conn->query("SELECT COUNT(*) as total FROM lessons");
if ($lessonCountResult) {
    $lessonCount = $lessonCountResult->fetch_assoc()['total'];
} else {
    $lessonCount = 0; // Handle the error gracefully
}

// Get the list of lessons
$lessonsResult = $conn->query("SELECT id, lesson_name, image, tag, date_created FROM lessons ORDER BY date_created DESC");

$conn->close();

include 'sidebar.php'; // Include the sidebar
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Lessons</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
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
        .navbar {
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .add-lesson-btn {
            background-color: #28a745;
            color: white;
            margin-bottom: 20px;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        .add-lesson-btn:hover {
            background-color: #218838;
        }

        /* Mobile Styles */
        @media (max-width: 768px) {
            .content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="content">
        <div class="navbar d-block d-md-none">
            <div class="menu-icon-container">
                <img src="resource/img/logo.png" alt="Logo">
                <div class="menu-icon" id="menuIcon"></div>
            </div>
        </div>
        <div class="content-header">
            <h1>Lessons</h1>
            <p>Manage and view lessons here.</p>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Lessons List</div>
                    <div class="card-body">
                        <button class="add-lesson-btn" onclick="window.location.href='add_lesson.php'">+ Add Lesson</button>

                        <?php if ($lessonsResult && $lessonsResult->num_rows > 0): ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Image</th>
                                    <th scope="col">Title</th>
                                    <th scope="col">Tag</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($lesson = $lessonsResult->fetch_assoc()): ?>
                                <tr>
                                    <th scope="row"><?php echo $lesson['id']; ?></th>
                                    <td><img src="<?php echo $lesson['image']; ?>" alt="Lesson Image" style="width: 50px; height: 50px;"></td>
                                    <td><?php echo $lesson['lesson_name']; ?></td>
                                    <td><?php echo $lesson['tag']; ?></td>
                                    <td>
                                        <a href="edit_lesson.php?id=<?php echo $lesson['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="delete_lesson.php?id=<?php echo $lesson['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this lesson?')">Delete</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                            <p>No lessons found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (Optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
session_start();
include 'db_connect.php';

// Include the admin header
include 'headeradmin.php';

// Get the post ID from the query string
$post_id = $_GET['id'];

// Prepare and execute the SQL statement to fetch post details
$stmt = $conn->prepare("SELECT title, content, image_url, created_at FROM blog_posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

// Check if post exists
if (!$post) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Post not found.</div></div>";
    exit();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> | Blog Post</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            color: #343a40;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .post-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 40px;
        }
        .post-title {
            font-size: 32px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 20px;
        }
        .post-content {
            font-size: 18px;
            line-height: 1.8;
            margin-top: 20px;
        }
        .post-image {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-top: 20px;
        }
        .post-meta {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 20px;
        }
        .container {
            max-width: 800px;
        }
        /* CSS for resizing images inside post content */
        .post-content img {
            max-width: 70%; /* Փոքրացնում ենք բովանդակության ներսում նկարների լայնությունը */
            height: auto;
            display: block;
            margin: 20px auto; /* Նկարները կենտրոնացնելու համար */
        }
    </style>
</head>
<body>

<div class="container">
    <div class="post-container">
        <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
        <div class="post-meta">Published on: <?php echo date('F j, Y', strtotime($post['created_at'])); ?></div>

        <!-- Display post image if available, before content -->
        <?php if (!empty($post['image_url'])): ?>
            <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="post-image">
        <?php endif; ?>

        <!-- Display post content -->
        <div class="post-content">
            <?php echo nl2br($post['content']); ?>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

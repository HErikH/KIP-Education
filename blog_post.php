<?php
session_start();
include 'db_connect.php'; // Include database connection

// Check if the database connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the blog post ID from the query string
$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Prepare and execute the query to fetch the post
$stmt = $conn->prepare("SELECT title, content, image_url, created_at FROM blog_posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Post not found.");
}

$post = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?> - Kipeducation.am</title>
    <link rel="icon" href="resource/img/favicon.png" type="image/png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
body {
    background: linear-gradient(90deg, #4b6cb7 0%, #182848 100%);
    color: white;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 0;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: center; /* Center vertically */
}

.post-container {
    max-width: 1200px; /* Increase the width of the content */
    margin: 100px auto; /* Reduce space at the top */
    padding: 20px; /* Padding inside the container */
    background-color: rgba(255, 255, 255, 0.1); /* Slight background for contrast */
    border-radius: 10px; /* Rounded corners */
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3); /* Shadow effect */
}

.post-title {
    font-size: 32px;
    margin-bottom: 10px; /* Less margin below the title */
}

.post-date {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.6);
    margin-bottom: 20px; /* More space below the date */
}

.post-content {
    font-size: 18px; /* Slightly larger font size for readability */
    line-height: 1.6; /* More line height for better readability */
    color: rgba(255, 255, 255, 0.9); /* Lighter text color */
}

.post-content img {
    max-width: 60%; /* Smaller size for images */
    height: auto; /* Maintain aspect ratio */
    margin-bottom: 20px; /* Space below the image */
    display: block;
    margin-left: auto;
    margin-right: auto; /* Center the image */
}

/* Style for embedded videos inside content */
.post-content iframe {
    width: 80%; /* Make sure the video takes full width */
    height: 500px; /* Set a specific height for videos */
    border-radius: 10px; /* Rounded corners for videos */
    margin-bottom: 20px; /* Space below the video */
    display: block;
    margin-left: auto;
    margin-right: auto; /* Center the video */
}

    </style>
</head>
<body>

<?php
// Include the header
include 'header.php';
?>

<div class="post-container">
    <h1 class="post-title"><?= htmlspecialchars($post['title']) ?></h1>
    <p class="post-date"><?= date('F j, Y', strtotime($post['created_at'])) ?></p>

    <div class="post-content">
        <?= nl2br($post['content']) ?> <!-- Convert newlines to <br> -->
    </div>
</div>

<?php
// Include the footer
include 'footer.php';
?>

</body>
</html>

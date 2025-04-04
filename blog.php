<?php
session_start();
include 'db_connect.php'; // Include database connection

// Check if the database connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve all blog posts ordered by creation date in descending order
$stmt = $conn->prepare("SELECT id, title, content, image_url, created_at FROM blog_posts ORDER BY created_at DESC");
if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    die("Query execution failed: " . $stmt->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - kipeducationid.com</title>
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
        }

        /* Adding space for footer */
        body::after {
            content: "";
            display: block;
            height: 100px;
        }

        .blog-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-grow: 1;
            margin-top: 180px;
        }

        .blog-container h1 {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 40px;
            text-align: center;
        }

        .blog-posts {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
        }

        .blog-post {
            background-color: rgba(255, 255, 255, 0.15);
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            width: 350px;
            height: 450px; /* Set standard height for all cards */
            overflow: hidden;
            text-align: left;
            display: flex;
            flex-direction: column;
            justify-content: space-between; /* Ensures the button stays at the bottom */
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .blog-post:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.3);
        }

        .blog-post img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 5px solid #3498db;
        }

        .blog-post h3 {
            font-size: 24px;
            margin: 20px 15px 10px;
            color: #fff;
        }

        .blog-post p.date {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
            margin: 5px 15px;
            text-align: right;
        }

        /* Ensure button is always at the bottom */
        .blog-post .btn {
            background-color: #3498db;
            color: white;
            border-radius: 30px;
            margin: 10px 15px 20px; /* Ensure some space at the bottom */
            padding: 10px 20px;
            font-weight: bold;
            text-align: center;
            display: block;
            text-transform: uppercase;
            transition: background-color 0.3s ease;
            align-self: flex-end; /* Align button to the bottom */
        }

        .blog-post .btn:hover {
            background-color: #2980b9;
        }

    </style>
</head>
<body>

<?php
// Include the header
include 'header.php';
?>

<!-- Blog Content -->
<div class="blog-container">
    <h1>Blog - kipeducationid.com</h1>
    <div class="blog-posts">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '
                <div class="blog-post">
                    <img src="' . htmlspecialchars($row['image_url']) . '" alt="Blog Image">
                    <h3>' . htmlspecialchars($row['title']) . '</h3>
                    <p class="date">' . date('F j, Y', strtotime($row['created_at'])) . '</p>
                    <a href="blog_post.php?id=' . $row['id'] . '" class="btn">Read More</a>
                </div>';
            }
        } else {
            echo '<p>No posts available yet. Stay tuned for future updates!</p>';
        }
        ?>
    </div>
</div>

<?php
// Include the footer
include 'footer.php';
?>

</body>
</html>

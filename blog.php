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

// Blog posts per page
$limit = 5;

// Current page number
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Check for specific post_id search
if (isset($_GET['post_id']) && is_numeric($_GET['post_id'])) {
    $post_id = (int)$_GET['post_id'];

    // Query to fetch specific post by ID
    $blogQuery = "SELECT id, title, image_url, created_at FROM blog_posts WHERE id = $post_id";
    $result = $conn->query($blogQuery);

    // Override pagination if searching by ID
    $totalPages = 1;
} else {
    // Query to fetch paginated posts
    $blogQuery = "SELECT id, title, image_url, created_at 
                  FROM blog_posts 
                  ORDER BY id DESC 
                  LIMIT $limit OFFSET $offset";
    $result = $conn->query($blogQuery);

    // Get total count for pagination
    $countQuery = "SELECT COUNT(*) AS total FROM blog_posts";
    $countResult = $conn->query($countQuery);
    $totalPosts = $countResult->fetch_assoc()['total'];
    $totalPages = ceil($totalPosts / $limit);
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
            margin-top: 130px;
        }

        .blog-container h1 {
            font-size: 48px;
            font-weight: bold;
            margin: 90px 90px 50px 90px;
            text-align: center;
        }

        .pagination-wrapper-top {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin: 20px 0 50px 0;
        }

        .search-container {
            display: flex;
            align-items: center;
            padding: 0;
        }

        .search-by-id {
            position: relative;
            display: flex;
            width: auto;
        }

        .search-by-id #post_id_remove {
            position: absolute;
            top: 50%;
            right: 65%;
            transform: translateY(-50%);
            z-index: 10;
            transition: 0.3s linear;
            color: #3498db;
            cursor: pointer;
        }

        .search-by-id #post_id_remove:hover {
            color: #023e86;
        }

        .search-by-id input {
            width: 150px;
            padding: 8px;
            border-radius: 8px 0 0 8px;
            border: 1px solid #ccc;
            font-size: 1rem;
            box-sizing: border-box;
        }

        .search-by-id button {
            width: auto;
            padding: 8px 15px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 0 8px 8px 0;
            cursor: pointer;
            transition: background-color 0.3s ease;
            box-sizing: border-box;
        }

        .search-by-id button:hover {
            background-color: #2980b9;
        }

        .pagination {
            display: flex;
            gap: 5px;
        }

        .pagination a {
            padding: 8px 16px;
            text-decoration: none;
            color: white;
            background-color: #3498db;
            border-radius: 50px;
            transition: background-color 0.3s ease;
            font-weight: bold;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .pagination a.active {
            background-color: #023e86;
        }

        .pagination a:hover {
            background-color: #2980b9;
        }

        .pagination-wrapper-bottom {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
        }

        .pagination a.btn-view-all {
            background-color: #3498db;
            width: 250px;
            padding: 12px 25px;
            font-size: 1.1rem;
            border-radius: 50px;
        }

        @media (max-width: 768px) {
            .pagination a:not(.btn-primary) {
                display: none;
            }

            .pagination-wrapper-bottom {
                width: 100%;
            }

            .pagination a {
                padding: 6px 10px;
            }

            .pagination a.btn-view-all {
                width: 250px;
                padding: 12px 25px;
                font-size: 1.1rem;
                border-radius: 50px;
            }

            .pagination a.btn-view-all:hover {
                background-color: #2980b9;
            }
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
            min-height: 450px;
            height: fit-content;
            overflow: hidden;
            text-align: left;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            /* Ensures the button stays at the bottom */
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
            margin: 10px 15px 20px;
            /* Ensure some space at the bottom */
            padding: 10px 20px;
            font-weight: bold;
            text-align: center;
            display: block;
            text-transform: uppercase;
            transition: background-color 0.3s ease;
            align-self: flex-end;
            /* Align button to the bottom */
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

        <!-- Pagination and Search at the top -->
        <div class="pagination-wrapper-top">
            <!-- Search input and button container -->
            <div class="search-container">
                <form method="GET" action="" class="search-by-id">
                    <div class="position-relative">
                        <?php if (isset($_GET['post_id'])): ?>
                        <i onclick="removePostId()" id="post_id_remove" class="fas fa-times"></i>
                        <?php endif; ?>
                        <input type="number" name="post_id" id="post_id" placeholder="ID" min="1" max="64"
                            value="<?= isset($_GET['post_id']) ? htmlspecialchars($_GET['post_id']) : '' ?>"
                            class="form-control-lg" required />
                    </div>
                    <button type="submit" class="btn btn-secondary">Search</button>
                </form>
            </div>

            <?php if (!isset($_GET['post_id'])): ?>
            <div class="pagination pagination-top">
                <!-- Previous Button -->
                <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>" class="btn btn-primary">Previous</a>
                <?php endif; ?>

                <!-- Page Numbers for desktop only -->
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>" class="btn <?= ($i == $page) ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
                <?php endfor; ?>

                <!-- Next Button -->
                <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>" class="btn btn-primary">Next</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="blog-posts">
            <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '
                <div class="blog-post">
                    <img src="' . htmlspecialchars($row['image_url']) . '" alt="Blog Image">
                    <h3>' . htmlspecialchars($row['title']) . '</h3>
                    <p class="date">' . date('F j, Y', strtotime($row['created_at'])) . '</p>
                    <a href="blog_post?id=' . $row['id'] . '" class="btn">Read More</a>
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

    <script>
        // Input validation for post_id field
        document.getElementById('post_id').addEventListener('input', function () {
            var inputValue = parseInt(this.value);
            // Limit input value to a maximum of 64
            if (inputValue > 64) {
                this.value = 64;
            }
        });

        function removePostId() {
            const url = new URL(window.location.href);

            console.log(url.searchParams.has('post_id'))

            url.searchParams.delete('post_id'); // Remove post_id
            url.searchParams.set('page', '1');  // Set page to 1

            // Redirect to the new URL (actually navigates)
            window.location.href = url.toString();

            return false;
        }
    </script>

</body>

</html>
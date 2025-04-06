<?php
session_start();
include 'db_connect.php';

// Ստուգում ենք՝ արդյոք ադմինիստրատորն է մուտք գործել
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header("Location: index");
    exit();
}

// Ստանում ենք բոլոր բլոգային գրառումները
$posts = $conn->query("SELECT id, title, content, image_url, created_at FROM blog_posts ORDER BY id DESC");

include 'headeradmin.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Blog</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .content-header {
            text-align: center;
            margin-bottom: 30px;
        }
        h1 {
            font-size: 32px;
            color: #007bff;
            font-weight: bold;
        }
        .post-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            height: 420px; /* Ֆիքսված բարձրություն */
            transition: box-shadow 0.3s ease-in-out;
        }
        .post-container:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        .post-image {
            width: 100%;
            height: 150px; /* Ֆիքսված բարձրություն */
            object-fit: cover;
            border-radius: 10px;
        }
        .post-title {
            font-size: 18px;
            font-weight: bold;
            margin-top: 15px;
            color: #333;
        }
        .post-date {
            font-size: 12px;
            color: #888;
            margin-bottom: 10px;
        }
        .post-excerpt {
            font-size: 14px;
            color: #555;
            margin-bottom: 10px;
            flex-grow: 1; /* Որպեսզի բովանդակությունը լինի հարմարվող */
        }
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 10px; /* Միջակայք կոճակների միջև */
            margin-top: auto;
        }
        .action-buttons a, .action-buttons button {
            font-size: 14px;
            padding: 8px 12px;
            border: none;
            background-color: #007bff;
            color: white;
            border-radius: 50px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px; /* Միջակայք պատկերակի ու տեքստի միջև */
        }
        .action-buttons a i, .action-buttons button i {
            font-size: 16px; /* Պատկերակի չափ */
        }
        .action-buttons a:hover, .action-buttons button:hover {
            background-color: #0056b3;
        }
        .btn-view {
            background-color: #28a745;
        }
        .btn-view:hover {
            background-color: #218838;
        }
        .btn-edit {
            background-color: #ffc107;
            color: black;
        }
        .btn-edit:hover {
            background-color: #e0a800;
        }
        .btn-delete {
            background-color: #dc3545;
        }
        .btn-delete:hover {
            background-color: #c82333;
        }
        .add-post-btn {
            display: inline-block;
            margin-bottom: 20px;
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        .add-post-btn:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <!-- Add New Post Button -->
            <a href="create_new_post.php" class="add-post-btn">
                <i class="fas fa-plus-circle"></i> Add New Post
            </a>

            <h3>Existing Blog Posts</h3>
            <div class="row" id="blogPostsContainer">
                <?php while ($post = $posts->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="post-container">
                        <img src="<?php echo $post['image_url']; ?>" alt="<?php echo $post['title']; ?>" class="post-image">
                        <p class="post-title"><?php echo $post['title']; ?></p>
                        <p class="post-excerpt">
                            <?php echo substr(strip_tags($post['content']), 0, 100); ?>...
                        </p>
                        <p class="post-date"><?php echo date("F j, Y", strtotime($post['created_at'])); ?></p>
                       <div class="action-buttons">
    <a href="view_post.php?id=<?php echo $post['id']; ?>" class="btn-view">
        <i class="fas fa-eye"></i> View
    </a>
    <!-- Edit կոճակը տանում է դեպի edit_new_post.php էջը, փոխանցելով գրառման ID-ն -->
    <a href="edit_new_post.php?id=<?php echo $post['id']; ?>" class="btn-edit">
        <i class="fas fa-edit"></i> Edit
    </a>
    <button class="btn-delete" onclick="openDeleteModal('<?php echo $post['id']; ?>')">
        <i class="fas fa-trash-alt"></i> Delete
    </button>
</div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this post?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="deleteConfirmBtn">Yes, delete</button>
            </div>
        </div>
    </div>
</div>


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function openDeleteModal(postId) {
        document.getElementById('deleteConfirmBtn').setAttribute('data-id', postId);
        var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }

    function openEditModal(postId, title, content, imageUrl) {
        var plainTextContent = content.replace(/<[^>]+>/g, ''); // Մաքրում ենք HTML պիտակները
        document.getElementById('editPostId').value = postId;
        document.getElementById('editTitle').value = title;
        document.getElementById('editContent').value = plainTextContent;
        document.getElementById('editImage').value = ''; // Սահմանում ենք նկարի համար դատարկ արժեք

        var editModal = new bootstrap.Modal(document.getElementById('editModal'));
        editModal.show();
    }

    // Հեռացում
    document.getElementById('deleteConfirmBtn').addEventListener('click', function () {
        var postId = this.getAttribute('data-id');
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "delete_post.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                if (xhr.responseText.trim() === 'success') {
                    window.location.reload();
                } else {
                    alert('Failed to delete post.');
                }
            }
        };

        xhr.send("id=" + postId);
    });

    // Թարմացում
    document.getElementById('editForm').onsubmit = function(event) {
        event.preventDefault();

        var formData = new FormData(this);
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "edit_post.php", true);

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                if (xhr.responseText.trim() === 'success') {
                    window.location.reload();
                } else {
                    alert('Failed to update post. Please try again.');
                }
            }
        };

        xhr.send(formData);
    };
</script>
</body>
</html>

<?php
$conn->close();
?>

<?php
include 'db_connect.php';
include 'headeradmin.php';
require_once 'constants.php';

// Ստուգում ենք՝ արդյոք ID-ն տրամադրված է
if (isset($_GET['id'])) {
    $post_id = $_GET['id'];

    // Ստանում ենք գրառման տվյալները տվյալների բազայից
    $stmt = $conn->prepare("SELECT title, content, image_url FROM blog_posts WHERE id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();

    // Եթե գրառումը չի գտնվել
    if (!$post) {
        echo "Post not found!";
        exit();
    }
} else {
    echo "Invalid request!";
    exit();
}

$stmt->close();

// Եթե ձևը ուղարկվում է (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_title = $_POST['title'];
    $new_content = $_POST['content'];

    // Նոր նկարի վերբեռնում
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = UPLOAD_DIR . "resource/img/posts/";
        $image_name = time() . '-' . basename($_FILES["image"]["name"]);
        $save_path = $target_dir . $image_name;
        $image_path = MEDIA_BASE_URL_FOR_DB . "resource/img/posts/" . $image_name;

        move_uploaded_file($_FILES["image"]["tmp_name"], $save_path);

        // Թարմացնում ենք նկարը
        $stmt = $conn->prepare("UPDATE blog_posts SET title = ?, content = ?, image_url = ?, save_path = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $new_title, $new_content, $image_path, $save_path, $post_id);
    } else {
        // Առանց նոր նկարի
        $stmt = $conn->prepare("UPDATE blog_posts SET title = ?, content = ? WHERE id = ?");
        $stmt->bind_param("ssi", $new_title, $new_content, $post_id);
    }

    if ($stmt->execute()) {
        // Վերափոխում դեպի blogadmin.php
        header("Location: blogadmin.php");
        exit();
    } else {
        echo "Error updating post!";
    }

    $stmt->close();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Blog Post</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Quill CSS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            margin-top: 50px;
        }

        #editor {
            height: 400px;
        }

        .preview-container {
            position: relative;
            display: inline-block;
        }

        .preview-container img {
            max-width: 150px;
            /* Փոքրացնում ենք նկարի չափը */
            max-height: 150px;
            margin-top: 10px;
        }

        .remove-preview {
            position: absolute;
            top: 0;
            right: 0;
            background: red;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2 class="text-center mb-4">Edit Blog Post</h2>

        <form action="edit_new_post.php?id=<?php echo $post_id; ?>" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="title" class="form-label">Blog Title</label>
                <input type="text" class="form-control" id="title" name="title"
                    value="<?php echo htmlspecialchars($post['title']); ?>" required>
            </div>

            <!-- Blog Image Upload -->
            <div class="mb-3">
                <label for="image" class="form-label">Blog Image</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                <div id="previewContainer" class="preview-container"
                    style="<?php echo ($post['image_url']) ? 'display:inline-block;' : 'display:none;'; ?>">
                    <img id="imagePreview" src="<?php echo htmlspecialchars($post['image_url']); ?>"
                        alt="Image Preview">
                    <button type="button" class="remove-preview" onclick="removeImage()">x</button>
                </div>
            </div>

            <div class="mb-3">
                <label for="content" class="form-label">Content</label>
                <!-- Quill-ի խմբագրիչի տարր -->
                <div id="editor">
                    <?php echo $post['content']; ?>
                </div>
                <!-- Hidden input field to store the editor content -->
                <input type="hidden" id="content" name="content">
            </div>

            <button type="submit" class="btn btn-primary">Update Post</button>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Quill JS -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

    <!-- Quill Initialization -->
    <script>
        var quill = new Quill('#editor', {
            theme: 'snow',
            placeholder: 'Edit your blog content here...',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, false] }],
                    ['bold', 'italic', 'underline'],
                    ['link', 'image', 'video'],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    ['clean']
                ]
            }
        });

        // Store the editor content in a hidden input field before form submission
        document.querySelector('form').onsubmit = function () {
            document.querySelector('#content').value = quill.root.innerHTML;
        };
    </script>

    <!-- Image Preview and Remove Functionality -->
    <script>
        document.getElementById('image').addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('imagePreview').src = e.target.result;
                    document.getElementById('previewContainer').style.display = 'inline-block';
                };
                reader.readAsDataURL(file);
            }
        });

        function removeImage() {
            // Clear the file input value
            document.getElementById('image').value = '';
            // Hide the preview container
            document.getElementById('previewContainer').style.display = 'none';
            // Remove the preview image src
            document.getElementById('imagePreview').src = '';
        }
    </script>

</body>

</html>
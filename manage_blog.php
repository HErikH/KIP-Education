<?php
session_start();
include 'db_connect.php';  // Կապը տվյալների բազայի հետ
require_once 'constants.php';

// Ստուգում ենք՝ արդյոք ադմինիստրատորն է մուտք գործել
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Եթե ֆորման ուղարկվել է POST մեթոդով
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];

    // Նկարի ներբեռնում
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_name = $_FILES['image']['name'];
        $save_path = UPLOAD_DIR . 'resource/img/posts/' . $image_name;
        $image_path = MEDIA_BASE_URL_FOR_DB . "resource/img/posts/" . $image_name;

        // Ստուգում ենք ֆայլի ձևաչափը
        $allowed_image_exts = ['jpg', 'jpeg', 'png', 'gif'];
        $image_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

        if (in_array($image_ext, $allowed_image_exts)) {
            // Պահպանում ենք նկարը սերվերի նշված թղթապանակում
            move_uploaded_file($image_tmp, $save_path);
        } else {
            $message = "Only JPG, JPEG, PNG, and GIF formats are allowed for images.";
            $image_path = null;
        }
    }

    // Գրառման ավելացում տվյալների բազայում
    if ($image_path) {
        $stmt = $conn->prepare("INSERT INTO blog_posts (title, content, image_url, save_path) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $title, $content, $image_path, $save_path);
        if ($stmt->execute()) {
            $message = "Blog post added successfully!";
        } else {
            $message = "Failed to add blog post.";
        }
    }
}

include 'headeradmin.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Blog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4">
                <h3>Add New Blog Post</h3>

                <!-- Success Message -->
                <?php if (isset($message)): ?>
                <div class="alert alert-success text-center">
                    <?php echo $message; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
$conn->close();
?>
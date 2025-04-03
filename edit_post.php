<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = $_POST['post_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_name = $_FILES['image']['name'];
        $image_path = 'resource/img/posts/' . $image_name;
        move_uploaded_file($image_tmp, $image_path);
        $stmt = $conn->prepare("UPDATE blog_posts SET title = ?, content = ?, image_url = ? WHERE id = ?");
        $stmt->bind_param("sssi", $title, $content, $image_path, $post_id);
    } else {
        $stmt = $conn->prepare("UPDATE blog_posts SET title = ?, content = ? WHERE id = ?");
        $stmt->bind_param("ssi", $title, $content, $post_id);
    }
    
    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }
}
?>

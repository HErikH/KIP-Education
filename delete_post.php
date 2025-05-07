<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        echo 'error';
        exit();
    }

    $post_id = $_POST['id'];

    // Prepare the SQL statement to check if the post exists
    $stmt = $conn->prepare("SELECT save_path FROM blog_posts WHERE id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        // If no post is found with the given ID
        echo 'not_found';
        exit();
    }

    // Fetch the image URL if it exists (to delete the image file later if needed)
    $stmt->bind_result($save_path);
    $stmt->fetch();
    $stmt->close();

    // Now delete the post
    $delete_stmt = $conn->prepare("DELETE FROM blog_posts WHERE id = ?");
    $delete_stmt->bind_param("i", $post_id);

    if ($delete_stmt->execute()) {
        // If there's an image file associated with the post, delete it
        if (!empty($save_path) && file_exists($save_path)) {
            unlink($save_path); // Delete the image file from the server
        }

        echo 'success';
    } else {
        echo 'error';
    }

    $delete_stmt->close();
}
?>

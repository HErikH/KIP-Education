<?php
// Ստուգում ենք, արդյոք ֆայլը բեռնվել է
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    // Բեռնումների պանակը
    $uploadDir = 'uploads/images/'; // Բեռնումների ֆոլդերը

    // Ստուգում ենք, արդյոք ֆոլդերը գոյություն ունի, եթե ոչ՝ ստեղծում ենք այն
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Ստեղծում ենք ֆոլդերը, եթե այն գոյություն չունի
    }

    // Ֆայլի ամբողջական ուղին
    $fileName = basename($_FILES['image']['name']);
    $uploadFile = $uploadDir . $fileName;

    // Սահմանում ենք թույլատրելի ֆայլերի տիպերը
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

    // Ստուգում ենք ֆայլի տիպը
    $fileType = $_FILES['image']['type'];
    if (in_array($fileType, $allowedTypes)) {
        // Փոխանցում ենք ֆայլը բեռնումների պանակ
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            echo json_encode(['status' => 'success', 'message' => 'Image uploaded successfully!', 'imagePath' => $uploadFile]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error uploading file!']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid file type!']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded or error in uploading!']);
}
?>

<?php
require_once 'constants.php';

if ($_FILES['upload']) {
    $file = $_FILES['upload'];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

    // Check for valid image file
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (in_array($ext, $allowed_extensions)) {
        // Move the uploaded file to the desired location
        $uploadDir = UPLOAD_DIR . 'resource/img/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
        }
        $fileName = uniqid() . '.' . $ext;
        $savePath = $uploadDir . $fileName;
        $imagePath = IMAGE_URL_BASE_FOR_DB . "resource/img/uploads/" . $fileName

        if (move_uploaded_file($file['tmp_name'], $savePath)) {
            // Return a JSON response with the image URL
            $response = [
                'url' => $imagePath // Return the uploaded image URL
            ];
            echo json_encode($response);
            exit;
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to move uploaded file.']);
            exit;
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid file extension.']);
        exit;
    }
}
?>

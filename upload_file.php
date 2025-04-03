<?php
// Include database connection
include 'db_connect.php';

$uploadDir = 'uploads/images/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true); // Create directory if not exists
}

$response = [];
$imagePaths = [];

if ($_FILES) {
    foreach ($_FILES['file']['tmp_name'] as $key => $tmp_name) {
        $fileName = basename($_FILES['file']['name'][$key]);
        $targetFilePath = $uploadDir . $fileName;

        // Move the uploaded file to the target directory
        if (move_uploaded_file($tmp_name, $targetFilePath)) {
            $imagePaths[] = $targetFilePath; // Collect the image path
            $response[] = [
                'status' => 'success',
                'file_path' => $targetFilePath
            ];
        } else {
            $response[] = [
                'status' => 'error',
                'message' => 'File upload failed: ' . $fileName
            ];
        }
    }
}

// Return JSON response including the image paths
header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'image_paths' => $imagePaths]);
?>

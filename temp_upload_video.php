<?php
require_once 'constants.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tempDir = UPLOAD_DIR . 'uploads/temp/';
    
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0777, true);
    }

    if (!empty($_FILES['video']['name'])) {
        $videoName = str_replace(' ', '_', basename($_FILES['video']['name']));
        $savePath = $tempDir . $videoName;
        $videoPath = MEDIA_BASE_URL_FOR_DB . "uploads/temp/" . $videoName;

        if (move_uploaded_file($_FILES['video']['tmp_name'], $savePath)) {
            echo $videoPath; // Վերադարձնում ենք ժամանակավոր ուղին
        } else {
            http_response_code(500);
            echo "Video upload failed!";
        }
    }
}
?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tempDir = 'uploads/temp/';
    
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0777, true);
    }

    if (!empty($_FILES['video']['name'])) {
        $videoName = str_replace(' ', '_', basename($_FILES['video']['name']));
        $videoPath = $tempDir . $videoName;

        if (move_uploaded_file($_FILES['video']['tmp_name'], $videoPath)) {
            echo $videoPath; // Վերադարձնում ենք ժամանակավոր ուղին
        } else {
            http_response_code(500);
            echo "Video upload failed!";
        }
    }
}
?>

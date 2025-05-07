<?php
session_start();
include 'db_connect.php'; // Տվյալների բազայի կապի ներառում
require_once 'constants.php';

// Սխալների ցուցադրում
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ստուգում ենք, արդյոք POST հարցում է ստացվել
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ստուգում ենք, արդյոք 'id' բանալին փոխանցվել է
    if (isset($_POST['id'])) {
        $lessonId = $_POST['id'];
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ID is missing']);
        exit();
    }

    // Ստուգում ենք, արդյոք ֆայլը բեռնվել է
    if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = UPLOAD_DIR . 'uploads/videos/';
        $videoName = str_replace(' ', '_', basename($_FILES['video']['name']));
        $uploadFile = $uploadDir . $videoName;
        $videoPath = MEDIA_BASE_URL_FOR_DB . "uploads/videos/" . $videoName;

        // Ստեղծում ենք պանակը, եթե այն չկա
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Փոխանցում ենք ֆայլը վերջնական ուղին
        if (move_uploaded_file($_FILES['video']['tmp_name'], $uploadFile)) {
            // Տվյալների բազայի կապի ստուգում
            if (!$conn) {
                die("Տվյալների բազայի կապը ձախողվեց: " . mysqli_connect_error());
            }

            // Վիդեոյի վերջնական պահպանման ուղին պահում ենք տվյալների բազայում
            $stmt = $conn->prepare("UPDATE lessons SET video = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("si", $videoPath, $lessonId);
                $stmt->execute();
                $stmt->close();
                echo json_encode(['status' => 'success', 'path' => $videoPath]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Database query failed']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Video upload failed']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No video file uploaded']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

$conn->close();
?>
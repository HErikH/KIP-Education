<?php
session_start();
include 'db_connect.php';

// Սխալների ցուցադրում (արտադրական միջավայրում անհրաժեշտ է անջատել)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Սեսիայի ստուգում
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}

// Ստուգում ենք, արդյոք ձևը ուղարկվել է
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $tag = trim($_POST['tag']);
    $tempVideoPath = isset($_POST['temp_video']) ? $_POST['temp_video'] : ''; // Վիդեոյի ժամանակավոր ուղին

    // Պաշտպանություն տվյալների մուտքագրման ժամանակ
    if (empty($title) || empty($tag)) {
        echo "Title և Tag դաշտերը պարտադիր են:";
        exit();
    }

    // Ստեղծում ենք դասը (ID-ի ստացում)
    $stmt = $conn->prepare("INSERT INTO lessons (title, tag, date_created) VALUES (?, ?, NOW())");
    if (!$stmt) {
        echo "Սխալ հարցման մեջ: " . $conn->error;
        exit();
    }
    $stmt->bind_param("ss", $title, $tag);
    $stmt->execute();
    $lessonId = $stmt->insert_id; // Ստանում ենք ID-ն
    $stmt->close();

    // Բոլոր ֆայլերի բեռնման պանակը
    $uploadDir = 'uploads/lessons/' . $lessonId . '/'; // Ստեղծում ենք պանակը ըստ ID-ի

    // Ստեղծում ենք պանակը, եթե այն դեռ չկա
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Վիդեո ֆայլի տեղափոխում վերջնական պանակ
    if (!empty($tempVideoPath)) {
        $finalVideoPath = $uploadDir . basename($tempVideoPath); // Ստեղծում ենք վերջնական ուղին
        if (rename($tempVideoPath, $finalVideoPath)) {
            // Վիդեոյի վերջնական պահպանման ուղին պահում ենք տվյալների բազայում
            $stmt = $conn->prepare("UPDATE lessons SET video = ? WHERE id = ?");
            if (!$stmt) {
                echo "Սխալ հարցման մեջ: " . $conn->error;
                exit();
            }
            $stmt->bind_param("si", $finalVideoPath, $lessonId);
            $stmt->execute();
            $stmt->close();
        } else {
            echo "Video move failed!";
        }
    }

    // Պատկերի ներբեռնում
    if (!empty($_FILES['image']['name'])) {
        $imageName = str_replace(' ', '_', basename($_FILES['image']['name']));
        $imagePath = $uploadDir . $imageName;

        // Ստուգում ենք ֆայլի տիպը, որպեսզի թույլատրենք միայն պատկերների ներբեռնումը
        $imageFileType = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($imageFileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                // Ներբեռնված պատկերը պահում ենք տվյալների բազայում
                $stmt = $conn->prepare("UPDATE lessons SET image = ? WHERE id = ?");
                if (!$stmt) {
                    echo "Սխալ հարցման մեջ: " . $conn->error;
                    exit();
                }
                $stmt->bind_param("si", $imagePath, $lessonId);
                $stmt->execute();
                $stmt->close();
            } else {
                echo "Image upload failed!";
            }
        } else {
            echo "Անթույլատրելի ֆայլի ձևաչափ:";
        }
    }

    // Այլ ֆայլերի ներբեռնում (փաստաթղթեր)
    $files = [];
    for ($i = 1; $i <= 4; $i++) {
        if (!empty($_FILES['file' . $i]['name'])) {
            $fileName = str_replace(' ', '_', basename($_FILES['file' . $i]['name']));
            $filePath = $uploadDir . $fileName;

            // Ստուգում ենք ֆայլի տիպը, որպեսզի թույլատրենք միայն որոշակի ֆայլեր
            $fileType = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $allowedFileTypes = ['pdf', 'pptx', 'docx'];

            if (in_array($fileType, $allowedFileTypes)) {
                if (move_uploaded_file($_FILES['file' . $i]['tmp_name'], $filePath)) {
                    $files[] = $filePath;
                } else {
                    echo "File upload failed for " . $_FILES['file' . $i]['name'];
                }
            } else {
                echo "Անթույլատրելի ֆայլի ձևաչափ: " . $_FILES['file' . $i]['name'];
            }
        }
    }

    // Ֆայլերի JSON ձևաչափով պահպանում տվյալների բազայում
    if (!empty($files)) {
        $filesJson = json_encode($files);
        $stmt = $conn->prepare("UPDATE lessons SET files = ? WHERE id = ?");
        if (!$stmt) {
            echo "Սխալ հարցման մեջ: " . $conn->error;
            exit();
        }
        $stmt->bind_param("si", $filesJson, $lessonId);
        $stmt->execute();
        $stmt->close();
    }

    // Ուղղորդում դասերի ցուցակ էջ
    header("Location: lessons.php");
    exit();
}

$conn->close();
?>

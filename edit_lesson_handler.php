<?php
session_start();
include 'db_connect.php';

// Սխալների ցուցադրում
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ստուգեք, արդյոք սեսիան վավեր է
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}

// Ստուգեք, արդյոք ձևը ուղարկվել է
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $title = $_POST['title'] ?? null;
    $tag = $_POST['tag'] ?? null;

    // Ստեղծել ֆոլդեր դասի ID-ի հիման վրա, եթե այն գոյություն չունի
    $lessonFolder = "uploads/lessons/$id";
    if (!is_dir($lessonFolder)) {
        mkdir($lessonFolder, 0777, true);
    }

    // Ստեղծել թարմացվող դաշտերի ցուցակը
    $updateFields = [];
    $params = [];
    $types = "";

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $imagePath = $lessonFolder . '/' . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            echo "Image uploaded successfully!";
            $updateFields[] = "image = ?";
            $params[] = $imagePath;
            $types .= "s";
        } else {
            echo "Image upload failed!";
        }
    }

    // Handle main video upload
    if (!empty($_FILES['video']['name'])) {
        $videoName = str_replace(' ', '_', basename($_FILES['video']['name']));
        $videoPath = $lessonFolder . '/' . $videoName;

        // Ստուգեք, արդյոք վիդեոն հաջողությամբ բեռնվել է
        if (move_uploaded_file($_FILES['video']['tmp_name'], $videoPath)) {
            echo "Video uploaded successfully!";
            $updateFields[] = "video = ?";
            $params[] = $videoPath;
            $types .= "s";
        } else {
            echo "Video upload failed!";
        }
    }

    // Handle small videos upload
    $smallVideos = [];
    if (!empty(array_filter($_FILES['small_videos']['name']))) {
        foreach ($_FILES['small_videos']['tmp_name'] as $key => $tmp_name) {
            $fileName = basename($_FILES['small_videos']['name'][$key]);
            $filePath = $lessonFolder . '/' . $fileName;
            if (move_uploaded_file($tmp_name, $filePath)) {
                $smallVideos[] = $filePath;
            } else {
                echo "Small video upload failed for $fileName!";
            }
        }
        $smallVideosJson = json_encode($smallVideos);
        $updateFields[] = "small_videos = ?";
        $params[] = $smallVideosJson;
        $types .= "s";
    }

    // Handle files upload
    $files = [];
    if (!empty(array_filter($_FILES['files']['name']))) {
        foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
            $fileName = basename($_FILES['files']['name'][$key]);
            $filePath = $lessonFolder . '/' . $fileName;
            if (move_uploaded_file($tmp_name, $filePath)) {
                $files[] = $filePath;
            } else {
                echo "File upload failed for $fileName!";
            }
        }
        $filesJson = json_encode($files);
        $updateFields[] = "files = ?";
        $params[] = $filesJson;
        $types .= "s";
    }

    // Թարմացնել անունը, եթե այն մատչելի է
    if (!empty($title)) {
        $updateFields[] = "title = ?";
        $params[] = $title;
        $types .= "s";
    }

    // Թարմացնել տեգը, եթե այն մատչելի է
    if (!empty($tag)) {
        $updateFields[] = "tag = ?";
        $params[] = $tag;
        $types .= "s";
    }

    // Include the id in the where clause
    $params[] = $id;
    $types .= "i";

    // Combine fields into the query
    if (!empty($updateFields)) {
        $query = "UPDATE lessons SET " . implode(", ", $updateFields) . " WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            // Redirect to the lessons list page after successful update
            header("Location: lessons.php");
            exit();
        } else {
            echo "Error updating record: " . $conn->error;
        }

        $stmt->close();
    } else {
        echo "No fields to update.";
    }
}

$conn->close();
?>

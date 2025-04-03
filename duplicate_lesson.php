<?php
include 'db_connect.php';

if (isset($_GET['id'])) {
    $lessonId = $_GET['id'];

    // Fetch the original lesson data
    $query = $conn->prepare("SELECT title, image, tag, video, files FROM lessons WHERE id = ?");
    $query->bind_param("i", $lessonId);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $lesson = $result->fetch_assoc();

        // Insert the duplicated lesson into the database
        $stmt = $conn->prepare("INSERT INTO lessons (title, image, tag, video, files, date_created) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssss", $lesson['title'], $lesson['image'], $lesson['tag'], $lesson['video'], $lesson['files']);

        if ($stmt->execute()) {
            header("Location: lessons.php?success=Lesson duplicated successfully!");
            exit();
        } else {
            echo "Error duplicating lesson: " . $conn->error;
        }
    } else {
        echo "Lesson not found!";
    }

    $query->close();
    $stmt->close();
}

$conn->close();
?>

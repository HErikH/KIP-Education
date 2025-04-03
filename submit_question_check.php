<?php
// Include database connection
include 'db_connect.php';
$conn->set_charset("utf8mb4");

// Retrieve quiz ID from URL
$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

// Check if quiz ID is valid
if ($quiz_id <= 0) {
    die("Invalid Quiz ID");
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $question_title = isset($_POST['questionTitle']) ? trim($_POST['questionTitle']) : '';

    // File uploads (default to NULL if not provided)
    $image = NULL;
    $video = NULL;
    $audio = NULL;

    // Handling image upload
    if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != '') {
        $target_dir = "uploads/images/";
        $image = basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image;
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
    }

    // Handling video upload
    if (isset($_FILES['video']['name']) && $_FILES['video']['name'] != '') {
        $target_dir = "uploads/videos/";
        $video = basename($_FILES["video"]["name"]);
        $target_file = $target_dir . $video;
        move_uploaded_file($_FILES["video"]["tmp_name"], $target_file);
    }

    // Handling audio upload
    if (isset($_FILES['audio']['name']) && $_FILES['audio']['name'] != '') {
        $target_dir = "uploads/audios/";
        $audio = basename($_FILES["audio"]["name"]);
        $target_file = $target_dir . $audio;
        move_uploaded_file($_FILES["audio"]["tmp_name"], $target_file);
    }

    // Retrieve options and answers
    $options = isset($_POST['options']) ? $_POST['options'] : [];
    $checkStatus = isset($_POST['checkStatus']) ? $_POST['checkStatus'] : [];

    // Prepare options as JSON (in case of empty options array, save an empty array)
    $options_json = json_encode($options);

    // Find true answer index
    $true_answer_index = array_search("true", $checkStatus);
    $true_answer_index = ($true_answer_index === false) ? NULL : $true_answer_index;

    // Set the question type to "check"
    $type = 'check';

    // Prepare the SQL statement for inserting the question
    $stmt = $conn->prepare("INSERT INTO questionssmart (quiz_id, question_title, image, video, audio, options, true_answer, type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Bind parameters (null if files are not uploaded)
    $stmt->bind_param("isssssis", $quiz_id, $question_title, $image, $video, $audio, $options_json, $true_answer_index, $type);

    // Execute the query
    if ($stmt->execute()) {
        // Redirect to success page or back to the quiz page
        header("Location: create_question_template.php?quiz_id=" . $quiz_id . "&status=success");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}

// Close the database connection
$conn->close();
?>

<?php
// Include the database connection
include 'db_connect.php';
require_once 'constants.php';

// Set the content type to JSON
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the required data is received
if (isset($_POST['question_id'], $_POST['question_title'], $_POST['answers'], $_POST['true_answer'], $_POST['false_answer'], $_POST['ratio'])) {
    $questionId = $_POST['question_id'];
    $questionTitle = $_POST['question_title'];
    $answers = json_decode($_POST['answers'], true); // Decode the JSON string into an array
    $trueAnswer = $_POST['true_answer'];
    $falseAnswer = $_POST['false_answer'];
    $ratio = $_POST['ratio'];
    $mediaToDelete = isset($_POST['media_to_delete']) && $_POST['media_to_delete'] === 'true';

    // Handle file uploads
    $imagePath = null;
    $videoPath = null;
    $audioPath = null;

    // Check if an image, video, or audio file was uploaded
    if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['media']['tmp_name'];
        $fileName = $_FILES['media']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Set the destination path based on the file type
        $allowedImageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $allowedVideoExtensions = ['mp4', 'avi', 'mov'];
        $allowedAudioExtensions = ['mp3', 'wav'];

        // Define the unique file name
        $uniqueFileName =  UPLOAD_DIR . "resource/questions/" . uniqid() . '.' . $fileExtension;

        // Create the resource/questions directory if it doesn't exist
        if (!file_exists(UPLOAD_DIR . 'resource/questions')) {
            mkdir(UPLOAD_DIR . 'resource/questions', 0777, true);
        }

        // Move the file to the appropriate location
        if (move_uploaded_file($fileTmpPath, $uniqueFileName)) {
            if (in_array($fileExtension, $allowedImageExtensions)) {
                $imagePath = $uniqueFileName;
            } elseif (in_array($fileExtension, $allowedVideoExtensions)) {
                $videoPath = $uniqueFileName;
            } elseif (in_array($fileExtension, $allowedAudioExtensions)) {
                $audioPath = $uniqueFileName;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload the file.']);
            exit;
        }
    }

    // Check if the question ID exists in the database
    $checkQuery = "SELECT question_title FROM questions WHERE id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("i", $questionId);
    $checkStmt->execute();
    $checkStmt->bind_result($currentTitle);
    if (!$checkStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'The question ID does not exist in the database.']);
        exit;
    }
    $checkStmt->close();

    // Prepare the update query for question title, answers, true_answer, false_answer, ratio, image, video, and audio
    $query = "UPDATE questions SET question_title = ?, answer_1 = ?, answer_2 = ?, answer_3 = ?, answer_4 = ?, 
              answer_5 = ?, answer_6 = ?, answer_7 = ?, answer_8 = ?, answer_9 = ?, answer_10 = ?, true_answer = ?, 
              false_answer = ?, ratio = ?, image = COALESCE(?, image), video = COALESCE(?, video), audio = COALESCE(?, audio) WHERE id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare the SQL statement: ' . $conn->error]);
        exit;
    }

    // Bind the parameters (answers array should contain up to 10 elements)
    $answerParams = array_pad($answers, 10, null); // Ensure the array has 10 elements, filling with null if necessary
    $stmt->bind_param(
        "sssssssssssssssssi",
        $questionTitle,
        $answerParams[0], $answerParams[1], $answerParams[2], $answerParams[3],
        $answerParams[4], $answerParams[5], $answerParams[6], $answerParams[7],
        $answerParams[8], $answerParams[9],
        $trueAnswer, $falseAnswer, $ratio,
        $imagePath, $videoPath, $audioPath,
        $questionId
    );

    // If media is marked for deletion
    if ($mediaToDelete) {
        // Remove existing media file if exists
        $deleteQuery = "SELECT image, video, audio FROM questions WHERE id = ?";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bind_param("i", $questionId);
        $deleteStmt->execute();
        $deleteStmt->bind_result($existingImage, $existingVideo, $existingAudio);
        if ($deleteStmt->fetch()) {
            // Delete files if they exist
            if ($existingImage && file_exists($existingImage)) {
                unlink($existingImage);
            }
            if ($existingVideo && file_exists($existingVideo)) {
                unlink($existingVideo);
            }
            if ($existingAudio && file_exists($existingAudio)) {
                unlink($existingAudio);
            }
        }
        $deleteStmt->close();

        // Clear image, video, and audio fields in the database
        $clearMediaQuery = "UPDATE questions SET image = NULL, video = NULL, audio = NULL WHERE id = ?";
        $clearMediaStmt = $conn->prepare($clearMediaQuery);
        $clearMediaStmt->bind_param("i", $questionId);
        $clearMediaStmt->execute();
        $clearMediaStmt->close();
    }

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Question and answers saved successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update the question. Error: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request. Missing required parameters.']);
}
?>

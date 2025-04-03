<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db_connect.php'; // Include database connection

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the lesson duration was posted
    if (isset($_POST['lesson_duration'])) {
        $lesson_duration = $_POST['lesson_duration'];

        // Define valid durations
        $valid_durations = ['10_seconds', '1_minute', '10_minutes', '60_minutes', '3_hours', '12_hours', '48_hours'];

        // Validate the selected lesson duration
        if (!in_array($lesson_duration, $valid_durations)) {
            echo "Invalid lesson duration selected.";
            exit();
        }

        // Update the lesson duration in the settings table
        $stmt = $conn->prepare("UPDATE settings SET lesson_duration = ? WHERE id = 1");
        if ($stmt) {
            $stmt->bind_param("s", $lesson_duration);
            
            // Execute the statement and check if it was successful
            if ($stmt->execute()) {
                echo "<script>alert('Lesson duration updated successfully.');</script>";
                header("Location: settings.php?success=Lesson duration updated");
                exit();
            } else {
                echo "<script>alert('Error updating lesson duration.');</script>";
            }

            // Close the statement
            $stmt->close();
        } else {
            echo "Database error: " . $conn->error;
        }
    } else {
        echo "No lesson duration selected.";
    }
} else {
    echo "Invalid request method.";
}

// Close the database connection
$conn->close();
?>

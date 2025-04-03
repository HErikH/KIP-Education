<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ստուգում ենք մուտքագրված տվյալները
    if (isset($_POST['lesson_id']) && isset($_POST['user_id'])) {
        $lesson_id = $_POST['lesson_id'];
        $user_id = $_POST['user_id'];

        // Ստուգում ենք, արդյոք այդ user_id-ն արդեն ունի այդ lesson_id-ն
        $stmt = $conn->prepare("SELECT * FROM user_lessons WHERE student_id = ? AND lesson_id = ?");
        $stmt->bind_param("ii", $user_id, $lesson_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "This user already has this lesson.";
        } else {
            // Եթե տվյալը գոյություն չունի, ավելացնենք
            $stmt = $conn->prepare("INSERT INTO user_lessons (student_id, lesson_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $user_id, $lesson_id);

            if ($stmt->execute()) {
                echo "Lesson successfully assigned to the user!";
            } else {
                echo "Error: " . $stmt->error;
            }
        }

        $stmt->close();
    } else {
        echo "Invalid input!";
    }
}

$conn->close();
?>

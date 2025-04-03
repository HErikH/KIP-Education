<?php
session_start();
include 'db_connect.php';

// Display errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. Get all students from the database
$studentsResult = $conn->query("SELECT id FROM students");
if (!$studentsResult) {
    die("Error fetching students: " . $conn->error);
}
$students = [];
while ($student = $studentsResult->fetch_assoc()) {
    $students[] = $student['id'];
}

// 2. Get all lessons from the database
$lessonsResult = $conn->query("SELECT id FROM lessons");
if (!$lessonsResult) {
    die("Error fetching lessons: " . $conn->error);
}
$lessons = [];
while ($lesson = $lessonsResult->fetch_assoc()) {
    $lessons[] = $lesson['id'];
}

// 3. Get all user_lessons from the database
$userLessonsResult = $conn->query("SELECT student_id, lesson_id FROM user_lessons");
if (!$userLessonsResult) {
    die("Error fetching user_lessons: " . $conn->error);
}
$userLessons = [];
while ($userLesson = $userLessonsResult->fetch_assoc()) {
    $userLessons[] = ['student_id' => $userLesson['student_id'], 'lesson_id' => $userLesson['lesson_id']];
}

// 4. Check if a student has been deleted and remove their rows from user_lessons
foreach ($userLessons as $userLesson) {
    if (!in_array($userLesson['student_id'], $students)) {
        // If student does not exist, delete their lessons
        $stmt = $conn->prepare("DELETE FROM user_lessons WHERE student_id = ?");
        $stmt->bind_param("i", $userLesson['student_id']);
        $stmt->execute();
        $stmt->close();
    }
}

// 5. Check if new students have been added and give them all lessons
foreach ($students as $studentId) {
    foreach ($lessons as $lessonId) {
        // Check if this student already has this lesson
        $stmt = $conn->prepare("SELECT COUNT(*) FROM user_lessons WHERE student_id = ? AND lesson_id = ?");
        $stmt->bind_param("ii", $studentId, $lessonId);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count == 0) {
            // If student does not have this lesson, insert it
            $stmt = $conn->prepare("INSERT INTO user_lessons (student_id, lesson_id, created_at, updated_at, active) VALUES (?, ?, NOW(), NOW(), 1)");
            $stmt->bind_param("ii", $studentId, $lessonId);
            $stmt->execute();
            $stmt->close();
        }
    }
}

$conn->close();
header("Location: lessons.php");
?>

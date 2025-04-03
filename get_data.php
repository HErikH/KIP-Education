<?php
include 'db_connect.php'; // Համոզվեք, որ սա կապվում է ձեր տվյալների բազային

// Lessons-ի ստացումը
$lessonsResult = $conn->query("SELECT id, title, end_time FROM lessons WHERE active = 1");

$lessons = [];

while ($lesson = $lessonsResult->fetch_assoc()) {
    $lessons[] = $lesson;
}

// JSON-ով վերադարձնում ենք տվյալները
header('Content-Type: application/json');
echo json_encode($lessons);
?>

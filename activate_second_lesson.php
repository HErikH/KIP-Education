<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lesson_id = $_POST['lesson_id'];

    if ($lesson_id == 2) {
        $start_time = date('Y-m-d H:i:s');
        $end_time = date('Y-m-d H:i:s', strtotime($start_time . ' + 30 seconds'));

        // Activate the second lesson
        $conn->query("UPDATE lessons SET active = 1, start_time = '$start_time', end_time = '$end_time' WHERE id = 2");
        echo 'Success';
    } else {
        echo 'Error';
    }
}

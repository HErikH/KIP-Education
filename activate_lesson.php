<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lesson_id = $_POST['lesson_id'];
    
    $start_time = date('Y-m-d H:i:s');
    $end_time = date('Y-m-d H:i:s', strtotime($start_time . ' + 30 seconds'));

    $sql = "UPDATE lessons SET active = 1, start_time = '$start_time', end_time = '$end_time' WHERE id = $lesson_id";
    $conn->query($sql);
}
?>

<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $child_id = $_POST['child_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $company_name = $_POST['company_name'];
    $phone_number = $_POST['phone_number'];
    $points = $_POST['points'];

    // SQL to update the child details in the database
    $sql = "UPDATE children SET first_name = '$first_name', last_name = '$last_name', company_name = '$company_name', phone_number = '$phone_number', points = '$points' WHERE id = '$child_id'";

    if ($conn->query($sql) === TRUE) {
        echo 'success';
    } else {
        echo 'error';
    }

    $conn->close();
}
?>

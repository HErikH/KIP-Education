<?php
include 'db_connect.php'; // Տվյալների բազայի կապի ֆայլը

// Ստանալ դասերի տվյալները
$lessons = $conn->query("SELECT id, title, end_time, active FROM lessons ORDER BY id ASC");

$response = [];

if ($lessons) {
    while ($lesson = $lessons->fetch_assoc()) {
        $response[] = [
            'id' => $lesson['id'],
            'title' => $lesson['title'],
            'end_time' => $lesson['end_time'],
            'active' => $lesson['active']
        ];
    }
}

// Վերադարձնել JSON արդյունքը
header('Content-Type: application/json');
echo json_encode($response);
?>

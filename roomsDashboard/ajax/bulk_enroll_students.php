<?php
// ajax/bulk_enroll_students.php
require_once '../../pdo_connect.php';
require_once '../classes/roomManager.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['room_id']) || !isset($input['student_ids']) || !is_array($input['student_ids'])) {
        echo json_encode(['success' => false, 'message' => 'Room ID and student IDs array are required']);
        exit;
    }
    
    $roomManager = new RoomManager($pdo);
    $added_count = 0;
    $errors = [];
    
    foreach ($input['student_ids'] as $student_id) {
        $result = $roomManager->enrollStudent($input['room_id'], $student_id);
        if ($result['success']) {
            $added_count++;
        } else {
            $errors[] = $result['message'];
        }
    }
    
    if ($added_count > 0) {
        $message = $added_count . ' students added successfully';
        if (count($errors) > 0) {
            $message .= '. Some errors: ' . implode(', ', array_unique($errors));
        }
        echo json_encode(['success' => true, 'added_count' => $added_count, 'message' => $message]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No students were added. Errors: ' . implode(', ', $errors)]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
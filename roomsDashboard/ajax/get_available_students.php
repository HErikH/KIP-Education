<?php
// ajax/get_available_students.php
require_once '../../pdo_connect.php';
require_once '../classes/roomManager.php';

header('Content-Type: application/json');

if (!isset($_GET['room_id']) || !is_numeric($_GET['room_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid room ID']);
    exit;
}

try {
    $roomManager = new RoomManager($pdo);
    $search = $_GET['search'] ?? '';
    $students = $roomManager->getAvailableStudents($_GET['room_id'], $search);
    
    echo json_encode(['success' => true, 'students' => $students]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
<?php
// ajax/get_teachers.php
require_once '../../pdo_connect.php';
require_once '../classes/roomManager.php';

header('Content-Type: application/json');

try {
    $roomManager = new RoomManager($pdo);
    $teachers = $roomManager->getTeachers();
    echo json_encode(['success' => true, 'teachers' => $teachers]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
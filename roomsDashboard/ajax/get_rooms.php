<?php
// ajax/get_rooms.php
require_once '../../pdo_connect.php';
require_once '../classes/roomManager.php';

header('Content-Type: application/json');

try {
    $roomManager = new RoomManager($pdo);
    
    $filters = [];
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $filters['status'] = $_GET['status'];
    }
    if (isset($_GET['teacher_id']) && !empty($_GET['teacher_id'])) {
        $filters['teacher_id'] = $_GET['teacher_id'];
    }
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $filters['search'] = $_GET['search'];
    }
    
    $rooms = $roomManager->listRooms($filters);
    echo json_encode(['success' => true, 'rooms' => $rooms]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
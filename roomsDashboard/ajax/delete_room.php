<?php
// ajax/delete_room.php
require_once '../../pdo_connect.php';
require_once '../classes/roomManager.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['room_id']) || !is_numeric($input['room_id'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid room ID']);
        exit;
    }
    
    $roomManager = new RoomManager($pdo);
    $result = $roomManager->deleteRoom($input['room_id']);
    
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
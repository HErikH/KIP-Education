<?php
// ajax/get_room_details.php
require_once '../../pdo_connect.php';
require_once '../classes/roomManager.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid room ID']);
    exit;
}

try {
    $roomManager = new RoomManager($pdo);
    $room = $roomManager->getRoomDetails($_GET['id']);
    
    if ($room) {
        echo json_encode(['success' => true, 'room' => $room]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Room not found']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
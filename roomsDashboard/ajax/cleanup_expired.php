<?php
// ajax/cleanup_expired.php
require_once '../../pdo_connect.php';
require_once '../classes/roomManager.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $roomManager = new RoomManager($pdo);
    $result = $roomManager->cleanupExpiredRooms();
    
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
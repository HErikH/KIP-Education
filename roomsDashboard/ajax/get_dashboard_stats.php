<?php
// ajax/get_dashboard_stats.php
require_once '../../pdo_connect.php';
require_once '../classes/roomManager.php';

header('Content-Type: application/json');

try {
    $roomManager = new RoomManager($pdo); // Adjust to your DB connection
    $stats = $roomManager->getDashboardStats();
    echo json_encode($stats);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
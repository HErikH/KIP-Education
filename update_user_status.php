<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $package = $_POST['package']; // Ստուգեք և ստացեք փաթեթի ինֆորմացիան

    // Թարմացրեք օգտատիրոջ կարգավիճակը (օրինակ՝ փաթեթ)
    $query = "UPDATE users SET package_status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('si', $package, $userId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating status']);
    }
}
?>

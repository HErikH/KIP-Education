<?php
// Include the database connection
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id']; // Get the user ID from the session or another method
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];

    // Fetch the current password hash from the database
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($storedPassword);
    $stmt->fetch();
    $stmt->close();

    // Verify the current password
    if (!password_verify($currentPassword, $storedPassword)) {
        echo "Current password is incorrect.";
        exit;
    }

    // Hash the new password
    $newPasswordHash = password_hash($newPassword, PASSWORD_BCRYPT);

    // Update the new password in the database
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $newPasswordHash, $userId);

    if ($stmt->execute()) {
        echo "Password changed successfully!";
    } else {
        echo "Error updating password.";
    }

    $stmt->close();
}
?>

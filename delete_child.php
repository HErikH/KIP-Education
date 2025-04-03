<?php
include 'db_connect.php'; // Կապը տվյալների բազայի հետ

if (isset($_POST['id'])) {
    $childId = intval($_POST['id']); // Ստանում ենք երեխայի ID-ն

    // Ջնջում ենք երեխայի տվյալները բազայից
    $stmt = $conn->prepare("DELETE FROM children WHERE id = ?");
    $stmt->bind_param("i", $childId);

    if ($stmt->execute()) {
        // Վերադարձնում ենք հաջողության հաղորդագրություն (որպես JSON կամ պարզ տեքստ)
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "No ID provided."]);
}
?>

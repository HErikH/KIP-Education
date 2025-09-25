<?php
// ajax/update_room.php
require_once "../../pdo_connect.php";
require_once "../classes/roomManager.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  echo json_encode(["success" => false, "message" => "Invalid request method"]);
  exit();
}

try {
  $input = json_decode(file_get_contents("php://input"), true);

  // Validate required fields
  if (
    empty($input["room_id"]) ||
    empty($input["room_name"]) ||
    empty($input["teacher_id"])
  ) {
    echo json_encode([
      "success" => false,
      "message" => "Room ID, name and teacher are required",
    ]);
    exit();
  }

  $roomManager = new RoomManager($pdo);
  $result = $roomManager->updateRoom($input["room_id"], $input);

  echo json_encode($result);
} catch (Exception $e) {
  echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>

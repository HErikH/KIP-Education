<?php
class RoomManager
{
  private $db;

  public function __construct($database_connection)
  {
    $this->db = $database_connection;
  }
 
  /**
   * Generate unique class ID
   */
  private function generateClassId()
  {
    $timestamp = date("Ymd");
    $random = strtoupper(
      substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 6)
    );

    return "classId-{$timestamp}-{$random}";
  }

  /**
   * Create new room
   */
  public function createRoom($data)
  {
    try {
      // Generate unique class ID
      do {
        $class_id = $this->generateClassId();
        $stmt = $this->db->prepare(
          "SELECT class_id FROM rooms WHERE class_id = ?"
        );
        $stmt->execute([$class_id]);
      } while ($stmt->rowCount() > 0);

      // Prepare expiration date
      $expires_at = !empty($data["expires_at"]) ? $data["expires_at"] : null;

      $stmt = $this->db->prepare("
                INSERT INTO rooms (class_id, room_name, description, teacher_id, max_students, expires_at, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

      $stmt->execute([
        $class_id,
        $data["room_name"],
        $data["description"] ?? null,
        $data["teacher_id"],
        $data["max_students"] ?? 30,
        $expires_at,
        $data["status"] ?? "active",
      ]);

      $room_id = $this->db->lastInsertId();

      return [
        "success" => true,
        "room_id" => $room_id,
        "class_id" => $class_id,
        "message" => "Room created successfully",
      ];
    } catch (Exception $e) {
      return [
        "success" => false,
        "message" => "Error creating room: " . $e->getMessage(),
      ];
    }
  }

  /**
   * Update room
   */
  public function updateRoom($room_id, $data)
  {
    try {
      $stmt = $this->db->prepare("
                UPDATE rooms 
                SET room_name = ?, description = ?, teacher_id = ?, max_students = ?, expires_at = ?, status = ?
                WHERE room_id = ?
            ");

      $expires_at = !empty($data["expires_at"]) ? $data["expires_at"] : null;

      $stmt->execute([
        $data["room_name"],
        $data["description"] ?? null,
        $data["teacher_id"],
        $data["max_students"] ?? 30,
        $expires_at,
        $data["status"] ?? "active",
        $room_id,
      ]);

      return [
        "success" => true,
        "message" => "Room updated successfully",
      ];
    } catch (Exception $e) {
      return [
        "success" => false,
        "message" => "Error updating room: " . $e->getMessage(),
      ];
    }
  }

  /**
   * Delete room
   */
  public function deleteRoom($room_id)
  {
    try {
      $stmt = $this->db->prepare("DELETE FROM rooms WHERE room_id = ?");
      $stmt->execute([$room_id]);

      return [
        "success" => true,
        "message" => "Room deleted successfully",
      ];
    } catch (Exception $e) {
      return [
        "success" => false,
        "message" => "Error deleting room: " . $e->getMessage(),
      ];
    }
  }

  /**
   * Get room details
   */
  public function getRoomDetails($room_id)
  {
    $stmt = $this->db->prepare("
            SELECT r.*, u.first_last_name as teacher_name, u.email as teacher_email,
                   (SELECT COUNT(*) FROM room_enrollments re WHERE re.room_id = r.room_id AND re.status = 'active') as current_students
            FROM rooms r
            LEFT JOIN users u ON r.teacher_id = u.id
            WHERE r.room_id = ?
        ");
    $stmt->execute([$room_id]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  /**
   * List rooms with filters
   */
  public function listRooms($filters = [])
  {
    $where_conditions = [];
    $params = [];

    if (!empty($filters["status"])) {
      $where_conditions[] = "r.status = ?";
      $params[] = $filters["status"];
    }

    if (!empty($filters["teacher_id"])) {
      $where_conditions[] = "r.teacher_id = ?";
      $params[] = $filters["teacher_id"];
    }

    if (!empty($filters["search"])) {
      $where_conditions[] =
        "(r.room_name LIKE ? OR r.class_id LIKE ? OR u.first_last_name LIKE ?)";
      $search_term = "%" . $filters["search"] . "%";
      $params[] = $search_term;
      $params[] = $search_term;
      $params[] = $search_term;
    }

    $where_clause = !empty($where_conditions)
      ? "WHERE " . implode(" AND ", $where_conditions)
      : "";
    $order_clause = $filters["order"] ?? "ORDER BY r.created_at DESC";
    $limit_clause = !empty($filters["limit"])
      ? "LIMIT " . intval($filters["limit"])
      : "";

    $stmt = $this->db->prepare("
            SELECT r.*, u.first_last_name as teacher_name,
                   (SELECT COUNT(*) FROM room_enrollments re WHERE re.room_id = r.room_id AND re.status = 'active') as current_students
            FROM rooms r
            LEFT JOIN users u ON r.teacher_id = u.id
            {$where_clause}
            {$order_clause}
            {$limit_clause}
        ");

    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Enroll student in room
   */
  public function enrollStudent($room_id, $student_id)
  {
    try {
      // Check if room exists and has space
      $room = $this->getRoomDetails($room_id);
      if (!$room) {
        return ["success" => false, "message" => "Room not found"];
      }

      if ($room["current_students"] >= $room["max_students"]) {
        return ["success" => false, "message" => "Room is full"];
      }

      // Check if student exists and is not blocked
      $stmt = $this->db->prepare(
        "SELECT id, first_last_name, role, blocked FROM users WHERE id = ? AND role = 'student'"
      );
      $stmt->execute([$student_id]);
      $student = $stmt->fetch(PDO::FETCH_ASSOC);

      if (!$student) {
        return ["success" => false, "message" => "Student not found"];
      }

      if ($student["blocked"] === "Yes") {
        return ["success" => false, "message" => "Student is blocked"];
      }

      // Check if already enrolled
      $stmt = $this->db->prepare(
        "SELECT enrollment_id FROM room_enrollments WHERE room_id = ? AND student_id = ? AND status = 'active'"
      );
      $stmt->execute([$room_id, $student_id]);

      if ($stmt->rowCount() > 0) {
        return ["success" => false, "message" => "Student already enrolled"];
      }

      // Enroll student
      $stmt = $this->db->prepare("
                INSERT INTO room_enrollments (room_id, student_id) 
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE status = 'active', enrolled_at = CURRENT_TIMESTAMP
            ");
      $stmt->execute([$room_id, $student_id]);

      return [
        "success" => true,
        "message" => "Student enrolled successfully",
      ];
    } catch (Exception $e) {
      return [
        "success" => false,
        "message" => "Error enrolling student: " . $e->getMessage(),
      ];
    }
  }

  /**
   * Remove student from room
   */
  public function removeStudent($room_id, $student_id)
  {
    try {
      $stmt = $this->db->prepare("
                UPDATE room_enrollments 
                SET status = 'removed' 
                WHERE room_id = ? AND student_id = ?
            ");
      $stmt->execute([$room_id, $student_id]);

      return [
        "success" => true,
        "message" => "Student removed successfully",
      ];
    } catch (Exception $e) {
      return [
        "success" => false,
        "message" => "Error removing student: " . $e->getMessage(),
      ];
    }
  }

  /**
   * Get room students
   */
  public function getRoomStudents($room_id)
  {
    $stmt = $this->db->prepare("
            SELECT u.id, u.first_last_name, u.email, re.enrolled_at, re.status
            FROM room_enrollments re
            JOIN users u ON re.student_id = u.id
            WHERE re.room_id = ? AND re.status = 'active'
            ORDER BY re.enrolled_at DESC
        ");
    $stmt->execute([$room_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Get available students (not in this room)
   */
  public function getAvailableStudents($room_id, $search = "")
  {
    $search_condition = !empty($search)
      ? "AND (u.first_last_name LIKE ? OR u.email LIKE ?)"
      : "";
    $params = [$room_id];

    if (!empty($search)) {
      $search_term = "%" . $search . "%";
      $params[] = $search_term;
      $params[] = $search_term;
    }

    $stmt = $this->db->prepare("
            SELECT u.id, u.first_last_name, u.email
            FROM users u
            WHERE u.role = 'student' 
            AND u.blocked = 'No'
            AND u.id NOT IN (
                SELECT re.student_id
                FROM room_enrollments re 
                WHERE re.room_id = ? AND re.status = 'active'
            )
            {$search_condition}
            ORDER BY u.first_last_name
        ");
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Get teachers list
   */
  public function getTeachers()
  {
    $stmt = $this->db->prepare("
            SELECT id, first_last_name, email
            FROM users
            WHERE role = 'teacher' AND blocked = 'No'
            ORDER BY first_last_name
        ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Get dashboard statistics
   */
  public function getDashboardStats()
  {
    $stats = [];

    // Total rooms by status
    $stmt = $this->db->prepare("
            SELECT status, COUNT(*) as count
            FROM rooms
            GROUP BY status
        ");
    $stmt->execute();
    $stats["rooms_by_status"] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Total active enrollments
    $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM room_enrollments
            WHERE status = 'active'
        ");
    $stmt->execute();
    $stats["total_enrollments"] = $stmt->fetch(PDO::FETCH_COLUMN);

    // Rooms expiring soon (next 7 days)
    $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM rooms
            WHERE expires_at IS NOT NULL 
            AND expires_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
            AND status = 'active'
        ");
    $stmt->execute();
    $stats["expiring_soon"] = $stmt->fetch(PDO::FETCH_COLUMN);

    return $stats;
  }

  /**
   * Clean up expired rooms
   */
  public function cleanupExpiredRooms()
  {
    try {
      $stmt = $this->db->prepare("
                UPDATE rooms 
                SET status = 'expired' 
                WHERE expires_at IS NOT NULL 
                AND expires_at < NOW() 
                AND status = 'active'
            ");
      $stmt->execute();

      return [
        "success" => true,
        "expired_count" => $stmt->rowCount(),
        "message" => $stmt->rowCount() . " rooms marked as expired",
      ];
    } catch (Exception $e) {
      return [
        "success" => false,
        "message" => "Error cleaning up expired rooms: " . $e->getMessage(),
      ];
    }
  }
}
?>

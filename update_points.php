<?php
include 'db_connect.php';

// Ստուգեք եթե բալը և quiz_id-ն հասանելի են POST-ի միջոցով
if (isset($_POST['finalScore']) && isset($_POST['quiz_id'])) {
    $finalScore = floatval($_POST['finalScore']);
    $quiz_id = intval($_POST['quiz_id']);

    // Ստացեք օգտատիրոջ ID-ն session-ից
    session_name('quiz_session');
    session_start();

    // Ստուգեք, արդյոք օգտատիրոջ ID-ն առկա է session-ում
    if (isset($_SESSION['quiz_user_id'])) {
        $user_id = $_SESSION['quiz_user_id'];

        // Ստացեք օգտատիրոջ ընթացիկ բալերը
        $query = "SELECT points FROM children WHERE id = ?";
        $stmt = $conn->prepare($query);
        if ($stmt === false) {
            echo json_encode(['status' => 'error', 'message' => 'Database query preparation failed.']);
            exit();
        }

        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($currentPoints);
        $stmt->fetch();
        $stmt->close();

        // Թարմացրեք օգտատիրոջ բալերը
        $updatedPoints = $currentPoints + $finalScore;
        $updateQuery = "UPDATE children SET points = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        if ($stmt === false) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to prepare update query.']);
            exit();
        }

        $stmt->bind_param("di", $updatedPoints, $user_id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'updatedPoints' => $updatedPoints]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update points.']);
        }

        $stmt->close();
    } else {
        // Եթե session-ում օգտատիրոջ ID չկա, վերադարձնել սխալի հաղորդագրություն
        echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
?>

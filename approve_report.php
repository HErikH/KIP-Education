<?php
include 'db_connect.php'; // Կապ տվյալների բազայի հետ

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Function to get lesson duration from settings
function getLessonDuration($conn) {
    // Ստանում ենք lesson_duration-ը settings աղյուսակից
    $durationQuery = $conn->query("SELECT lesson_duration FROM settings WHERE id = 1");
    
    if ($durationQuery && $durationQuery->num_rows > 0) {
        $durationResult = $durationQuery->fetch_assoc();
        $lesson_duration = $durationResult['lesson_duration'];

        // Ստուգում ենք ստացված արժեքը և վերադարձնում համապատասխան ժամկետը վայրկյաններով
        switch ($lesson_duration) {
            case '10_seconds':
                return 10;
            case '1_minute':
                return 60;
            case '10_minutes':
                return 600;
            case '60_minutes':
                return 3600;
            case '3_hours':
                return 10800;
            case '12_hours':
                return 43200;
            case '48_hours':
                return 172800;
            default:
                return 3600; // Վերադարձնում ենք 1 ժամ՝ որպես կանխորոշված, եթե արժեքը սխալ է
        }
    } else {
        // Եթե հարցումը ձախողվում է կամ արդյունք չկա, վերադարձնում ենք կանխորոշված արժեք
        return 3600; // Կանխորոշված՝ 1 ժամ
    }
}

// Ստուգում ենք՝ արդյոք հարցումը POST մեթոդով է
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $report_id = $_POST['id']; // Ստանում ենք report-ի ID-ն

    // Ստանում ենք report-ից համապատասխան post_id և user_id
    $reportQuery = $conn->prepare("SELECT post_id, user_id FROM reports WHERE id = ?");
    $reportQuery->bind_param("i", $report_id);
    $reportQuery->execute();
    $reportResult = $reportQuery->get_result();

    if ($reportResult->num_rows > 0) {
        $reportData = $reportResult->fetch_assoc();
        $post_id = $reportData['post_id'];
        $user_id = $reportData['user_id'];

        // Ստանում ենք տևողությունը settings աղյուսակից
        $duration_in_seconds = getLessonDuration($conn);

        // Հաշվարկում ենք start_time և end_time՝ օգտագործելով settings-ից ստացված տևողությունը
        $start_time = date('Y-m-d H:i:s');
        $end_time = date('Y-m-d H:i:s', strtotime($start_time . " + $duration_in_seconds seconds"));

        // Ստուգում ենք՝ արդյոք օգտատերը արդեն ունի այս դասընթացը ակտիվ
        $checkLessonQuery = $conn->prepare("SELECT id FROM user_lessons WHERE student_id = ? AND lesson_id = ?");
        $checkLessonQuery->bind_param("ii", $user_id, $post_id);
        $checkLessonQuery->execute();
        $checkLessonQuery->store_result();

        // Եթե օգտատերը արդեն ունի դասընթացը, թարմացնում ենք տվյալները
        if ($checkLessonQuery->num_rows > 0) {
            $updateLessonQuery = $conn->prepare("UPDATE user_lessons SET active = 1, start_time = ?, end_time = ? WHERE student_id = ? AND lesson_id = ?");
            $updateLessonQuery->bind_param("ssii", $start_time, $end_time, $user_id, $post_id);
            $updateLessonQuery->execute();
            $updateLessonQuery->close();
        } else {
            // Եթե օգտատերը չունի այս դասընթացը, նոր ռեկորդ ենք ավելացնում
            $insertLessonQuery = $conn->prepare("INSERT INTO user_lessons (lesson_id, student_id, active, start_time, end_time) VALUES (?, ?, 1, ?, ?)");
            $insertLessonQuery->bind_param("iiss", $post_id, $user_id, $start_time, $end_time);
            $insertLessonQuery->execute();
            $insertLessonQuery->close();
        }

        // Եթե ամեն ինչ հաջողությամբ ավարտվեց, վերադարձնում ենք "success"
        echo "success";
    } else {
        echo "Error: Report not found.";
    }

    $reportQuery->close();
} else {
    echo "Invalid request.";
}
$conn->close();
?>

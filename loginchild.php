<?php
session_start();
include 'db_connect.php';

// Ստանում ենք quiz ID-ն URL-ից
$quiz_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['login_phone_number'])) {
    $name = trim($_POST['name']);
    $phone_number = trim($_POST['login_phone_number']);

    // Ստուգում ենք՝ արդյոք օգտատերը կա տվյալների բազայում
    $stmt = $conn->prepare("SELECT id, first_name, last_name FROM children WHERE CONCAT(TRIM(first_name), ' ', TRIM(last_name)) = ? AND TRIM(phone_number) = ?");
    $stmt->bind_param("ss", $name, $phone_number);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Օգտատերը գտնվեց, կատարում ենք մուտք
        $stmt->bind_result($user_id, $first_name, $last_name);
        $stmt->fetch();
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $first_name . ' ' . $last_name;
        $_SESSION['user_points'] = 0; // Կարող եք այստեղ լցնել իրական միավորներ
        $_SESSION['login_success'] = "Դուք հաջողությամբ մուտք եք գործել:"; // Հաջողության հաղորդագրություն
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $quiz_id);
        exit();
    } else {
        // Օգտատերը չգտնվեց
        $_SESSION['login_error'] = "Սխալ անուն կամ հեռախոսահամար:";
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $quiz_id);
        exit();
    }
    $stmt->close();
}
?>

<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $firstLastName = htmlspecialchars(trim($_POST['first_last_name']));
    $phoneNumber = htmlspecialchars(trim($_POST['phone_number']));
    $role = htmlspecialchars(trim($_POST['role']));
    $blocked = htmlspecialchars(trim($_POST['blocked']));
    $productName = htmlspecialchars(trim($_POST['product_name']));
    $productId = htmlspecialchars(trim($_POST['product_id']));
    $balance = floatval($_POST['balance']);
    $country = htmlspecialchars(trim($_POST['country']));

    // Проверка на корректность email
    if (!$email) {
        echo "Invalid email";
        exit;
    }

    // Подготовка SQL-запроса
    $stmt = $conn->prepare("INSERT INTO users (email, password, first_last_name, phone_number, role, blocked, product_name, product_id, balance, country, date_register) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    
    if ($stmt) {
        $stmt->bind_param('sssssssdss', $email, $password, $firstLastName, $phoneNumber, $role, $blocked, $productName, $productId, $balance, $country);

        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "Failed to add user. Please try again.";
        }

        $stmt->close();
    } else {
        echo "Failed to prepare SQL statement.";
    }
    
    $conn->close();
} else {
    echo "Invalid request.";
}
?>

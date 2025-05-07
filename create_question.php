<?php
// Подключение к базе данных
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db_connect.php';
require_once 'constants.php';

// Устанавливаем кодировку символов для корректной работы с UTF-8
$conn->set_charset("utf8mb4");

// Проверяем, был ли отправлен запрос методом POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Получаем данные из формы
    $quiz_id = isset($_POST['quiz_id']) ? (int)$_POST['quiz_id'] : 0;
    $question_title = isset($_POST['question_title']) ? $_POST['question_title'] : '';
    $question_type = isset($_POST['question_type']) ? $_POST['question_type'] : '';

    // Инициализация значений для файлов (изображения, видео, аудио)
    $image = '';
    $video = '';
    $audio = '';
    $false_answers_string = '';  // Явно определяем переменную

    // Функция для загрузки файлов
    function uploadFile($file, $uploadDir) {
        $savePath = UPLOAD_DIR . $uploadDir;

        if (!empty($file['name'])) {
            if (!is_dir($savePath)) {
                mkdir($savePath 0777, true); // Создаем директорию, если ее нет
            }

            $fileName = basename($file['name']);
            $targetFilePath = savePath . $fileName;

            if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
                return MEDIA_BASE_URL_FOR_DB . $uploadDir . $fileName;
            }
        }
        return '';
    }

    // Обработка загрузки изображения, видео или аудио в зависимости от типа вопроса
    if ($question_type === 'image_select') {
        $image = uploadFile($_FILES['media'], 'uploads/images/');
    } elseif ($question_type === 'video') {
        $video = uploadFile($_FILES['media'], 'uploads/videos/');
    } elseif ($question_type === 'audio') {
        $audio = uploadFile($_FILES['media'], 'uploads/audio/');
    }

    // Массивы для ответов
    $answer_1 = isset($_POST['answer']) ? $_POST['answer'] : '';
    $true_answers_string = isset($_POST['true_answer']) ? implode(',', $_POST['true_answer']) : '';

    // Подготовка SQL-запроса для вставки вопроса в таблицу questions
    $stmt = $conn->prepare("
        INSERT INTO questions 
        (quiz_id, question_title, type, image, video, audio, answer_1, true_answer, false_answer) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    if ($stmt === false) {
        die('Ошибка подготовки запроса: ' . htmlspecialchars($conn->error));
    }

    // Привязываем параметры к запросу
    if ($stmt->bind_param(
        "issssssss",  // 9 параметров: 1 int и 8 string
        $quiz_id, $question_title, $question_type, $image, $video, $audio, 
        $answer_1, $true_answers_string, $false_answers_string
    ) === false) {
        die('Ошибка привязки параметров: ' . htmlspecialchars($stmt->error));
    }

    // Выполняем запрос
    if ($stmt->execute()) {
        // Если вопрос успешно добавлен, перенаправляем обратно на страницу вопросов
        header("Location: questions.php?id=" . $quiz_id);
        exit();
    } else {
        // Если произошла ошибка, выводим сообщение
        echo "Ошибка выполнения запроса: " . htmlspecialchars($stmt->error) . "<br>";
    }

    // Закрываем подготовленный запрос
    $stmt->close();
}

// Закрываем соединение с базой данных
$conn->close();
?>
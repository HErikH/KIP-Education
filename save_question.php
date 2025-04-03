<?php
// Подключение к базе данных
include 'db_connect.php';

// Проверка, была ли отправлена форма
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Получение данных из формы
    $quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0; // Получаем quiz_id из URL
    $question_title = isset($_POST['question_title']) ? $_POST['question_title'] : ''; // Название вопроса
    $type = isset($_POST['question_type']) ? $_POST['question_type'] : ''; // Тип вопроса (check, radio, select)

    // Обработка загружаемых файлов
    $media = null;
    if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/images/'; // Папка для загрузки изображений
        $mediaPath = $uploadDir . basename($_FILES['media']['name']);

        // Проверяем, существует ли папка, если нет, создаем
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Создаем директорию, если она не существует
        }

        // Перемещаем загруженный файл в указанную директорию
        if (move_uploaded_file($_FILES['media']['tmp_name'], $mediaPath)) {
            // Сохраняем полный путь к файлу
            $media = 'uploads/images/' . basename($_FILES['media']['name']); // Save just the relative path
        } else {
            echo "Ошибка при загрузке файла: " . $_FILES['media']['error']; // Обработка ошибки загрузки
            exit(); // Прерываем выполнение, если произошла ошибка
        }
    }

    // Получаем варианты ответов и правильные ответы
    $options = isset($_POST['option_value']) ? $_POST['option_value'] : []; // Все варианты ответов
    $true_answers = isset($_POST['true_answer']) ? $_POST['true_answer'] : []; // Тексты правильных ответов

    // Убедимся, что значения правильных ответов приходят корректно
    foreach ($true_answers as $key => $value) {
        if (empty($value)) {
            unset($true_answers[$key]);
        }
    }

    // Преобразуем варианты ответов в строку (JSON с поддержкой Unicode)
    $options_serialized = json_encode($options, JSON_UNESCAPED_UNICODE);

    // Преобразование правильных ответов в массив, если это строка
    if (is_string($true_answers)) {
        $true_answers = explode(", ", $true_answers); // Преобразуем строку обратно в массив
    }

    // Преобразование правильных ответов в строку JSON для хранения в базе данных
    $true_answers_serialized = json_encode($true_answers, JSON_UNESCAPED_UNICODE); 

    // Проверяем, есть ли хотя бы один вариант ответа
    if (!empty($options)) {
        // Запрос для вставки данных в базу данных
        $stmt = $conn->prepare("INSERT INTO questions_new (quiz_id, question_title, type, media, answers, true_answer) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $quiz_id, $question_title, $type, $media, $options_serialized, $true_answers_serialized);

        if ($stmt->execute()) {
            // Если данные успешно сохранены, перенаправляем пользователя обратно на страницу вопросов
            header("Location: questionssmart.php?id=" . $quiz_id);
            exit();
        } else {
            echo "Ошибка при сохранении данных: " . $stmt->error; // Вывод ошибки при неудачном выполнении запроса
        }

        $stmt->close();
    } else {
        echo "Необходимо ввести хотя бы один вариант ответа.";
    }
}

// Закрываем подключение к базе данных
$conn->close();
?>

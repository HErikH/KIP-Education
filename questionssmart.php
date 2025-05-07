<?php
// Подключение к базе данных
include 'db_connect.php';
include 'headeradmin.php';
require_once 'constants.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Получаем ID викторины из URL
$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Если запрос на удаление вопроса
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $question_id = (int)$_GET['delete'];

    // Удаляем вопрос по его ID
    $stmt = $conn->prepare("DELETE FROM questions_new WHERE id = ?");
    $stmt->bind_param("i", $question_id);
    if ($stmt->execute()) {
        // Успешное удаление, перенаправляем обратно
        header("Location: questionssmart.php?id=" . $quiz_id);
        exit();
    } else {
        echo "Ошибка при удалении вопроса: " . $conn->error;
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Получение данных из формы
    $question_title = $_POST['question_title'] ?? '';
    $question_type = $_POST['question_type'] ?? '';
    $media = null;
    $answers = [];
    $true_answers = [];

    // Обработка загружаемых файлов для image_select
    if ($question_type === 'image_select') {
        $imageUploads = [];
        if (!empty($_FILES['option_value']['name'][0])) {
            $uploadDir = UPLOAD_DIR . 'uploads/images/'; // Папка для загрузки изображений

            // Проверяем, существует ли папка, если нет, создаем
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true); // Создаем директорию, если она не существует
            }

            // Обрабатываем каждое загружаемое изображение
            foreach ($_FILES['option_value']['name'] as $key => $value) {
                if ($_FILES['option_value']['error'][$key] === UPLOAD_ERR_OK) {
                    $fileName = basename($_FILES['option_value']['name'][$key]);
                    $savePath = $uploadDir . $fileName;
                    $mediaPath = MEDIA_BASE_URL_FOR_DB . "uploads/images/" . $fileName;

                    if (move_uploaded_file($_FILES['option_value']['tmp_name'][$key], $savePath)) {
                        $imageUploads[] = $mediaPath; // Сохраняем относительный путь к файлу
                    } else {
                        echo "Ошибка при загрузке файла: " . $_FILES['option_value']['error'][$key];
                    }
                }
            }
        }
        // Сохраняем массив изображений как ответы
        $answers = $imageUploads;
        $true_answers = $_POST['true_answer'] ?? []; // Правильные ответы из формы
    } else {
        // Обработка медиафайлов для других типов вопросов
        if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = UPLOAD_DIR . 'uploads/images/'; // Папка для загрузки изображений
            $fileName = basename($_FILES['media']['name']);
            $savePath = $uploadDir . $fileName;

            // Проверяем, существует ли папка, если нет, создаем
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true); // Создаем директорию, если она не существует
            }

            // Перемещаем загруженный файл в указанную директорию
            if (move_uploaded_file($_FILES['media']['tmp_name'], $savePath)) {
                $media = MEDIA_BASE_URL_FOR_DB . "uploads/images/" . $fileName; // Сохраняем путь к файлу
            } else {
                echo "Ошибка при загрузке файла: " . $_FILES['media']['error'];
            }
        }

        // Получаем варианты ответов и правильные ответы для других типов вопросов
        $answers = $_POST['option_value'] ?? [];
        $true_answers = $_POST['true_answer'] ?? [];
    }

    // Убедимся, что правильные ответы это массив
    if (!is_array($true_answers)) {
        $true_answers = [$true_answers];
    }

    // Преобразуем ответы и правильные ответы в JSON-строки
    $answers_serialized = json_encode($answers, JSON_UNESCAPED_UNICODE);
    $true_answers_serialized = json_encode($true_answers, JSON_UNESCAPED_UNICODE);

    // Сохраняем вопрос в базу данных
    $stmt = $conn->prepare("INSERT INTO questions_new (quiz_id, question_title, type, media, answers, true_answer) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $quiz_id, $question_title, $question_type, $media, $answers_serialized, $true_answers_serialized);

    if ($stmt->execute()) {
        header("Location: questionssmart.php?id=" . $quiz_id);
        exit();
    } else {
        echo "Ошибка при сохранении данных: " . $stmt->error;
    }
    $stmt->close();
}

// Запрос на получение вопросов для текущей викторины
if ($quiz_id > 0) {
    $stmt = $conn->prepare("SELECT id, question_title, type, answers, true_answer FROM questions_new WHERE quiz_id = ?");
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $stmt->bind_result($question_id, $question_title, $type, $answers, $true_answer);
    
    $questions = [];
    while ($stmt->fetch()) {
        $answers = json_decode($answers, true);
        $true_answer = json_decode($true_answer, true);

        // Убедимся, что true_answer это массив
        if (!is_array($true_answer)) {
            $true_answer = [];
        }

        $questions[] = [
            'id' => $question_id,
            'question_title' => $question_title,
            'type' => $type,
            'answers' => $answers,
            'true_answer' => $true_answer
        ];
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Questions</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
.title-container { 
        text-align: center; 
        margin-top: 50px; 
        margin-bottom: 30px; 
        padding: 20px; 
        background-color: #f0f8ff; 
        border-radius: 10px; 
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1); 
    }
    .title-container h1 { 
        font-size: 2.5rem; 
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        color: #007bff; 
        font-weight: bold; 
    }
.container-section {
    display: flex;
    justify-content: center; /* Center align all items */
    margin-top: 50px; 
    flex-wrap: wrap; /* Allow wrapping for smaller screens */
}
    .box {
        width: 54%; /* Adjust width for both sections to fit well together */
        background-color: #f8f9fa; 
        padding: 20px; 
        border-radius: 10px; 
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1); 
        min-height: 400px; /* Optional: Adjust height as necessary */
        margin-right: 20px; /* Add margin to create space */
    }
    .box:last-child {
        margin-right: 0; /* Remove right margin for the last box */
    }
    .box h2 { 
        font-size: 1.75rem; 
        color: #007bff; 
        font-family: Arial, sans-serif; 
        margin-bottom: 20px; 
        text-align: center; 
    }
    .form-group { margin-bottom: 20px; }
    .btn-custom { 
        background-color: #007bff; 
        color: #fff; 
        border-radius: 50px; 
        padding: 10px 20px; 
        font-size: 1rem; 
        text-align: center; 
        display: block; 
        margin: 20px auto; 
        transition: all 0.3s ease; 
    }
    .btn-custom:hover { 
        background-color: #0056b3; 
        transform: scale(1.05); 
    }
        .media-preview { margin-top: 15px; position: relative; width: 100%; }
        .media-preview img, .media-preview video, .media-preview audio { width: 100%; height: auto; border-radius: 10px; box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1); }
        .remove-media { position: absolute; top: -10px; right: -10px; background-color: #ff0000; color: #fff; border-radius: 50%; width: 25px; height: 25px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 14px; }
        .media-label { font-size: 1rem; color: #333; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .question-buttons { display: flex; justify-content: space-around; margin-top: 20px; }
        .question-buttons .btn { width: 30%; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; padding: 10px; transition: all 0.3s ease; cursor: pointer; margin: 0 2px; /* Add horizontal margin for spacing */
 }
        .question-buttons .btn i { margin-right: 10px; }
        .question-buttons .btn.active { background-color: #007bff; color: #fff; border: 2px solid #007bff; }
        .question-buttons .btn:hover { transform: scale(1.1); }
        .add-field-group { display: flex; align-items: center; margin-bottom: 10px; }
        .add-field-group input { flex: 1; margin-right: 10px; }
        .add-field-group .checkbox-container { margin-left: 10px; }
        .add-field-group .btn-add, .add-field-group .btn-remove { background-color: #28a745; color: white; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease; }
        .add-field-group .btn-remove { background-color: #dc3545; }
        .add-field-group .btn:hover { transform: scale(1.1); }
        .btn-remove { background-color: #dc3545; /* Կարմիր ֆոն */
    color: white; /* Սպիտակ տեքստ */
    border-radius: 50%; /* Կլորացված կոճակ */
    width: 40px; /* Կոճակի լայնություն */
    height: 40px; /* Կոճակի բարձրություն */
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer; /* Ցույց է տալիս ձեռքի նշանը */
    transition: all 0.3s ease; /* Փափուկ անցում */
}
 .btn-remove:hover {
    transform: scale(1.1); /* Տրամագծի մեծացում */
}
.questions-container img {
    width: 100px; /* Փոքր չափս */
    height: auto; /* Բարձրությունը ավտոմատ */
    margin: 5px; /* Միջքայլ */
    border: 2px solid transparent; /* Սկզբնական վիճակ */
    transition: border-color 0.3s; /* Անցումի ազդեցություն */
}

.questions-container img.correct-answer {
    border-color: blue; /* Կապույտ եզրագիծ ճիշտ պատասխանի համար */
}

        .dynamic-fields .form-group { margin-bottom: 10px; }
        .correct-answer { color: green; margin-left: 10px; }
    </style>
</head>
<body>

<div class="container mt-5">
    <!-- Title Container -->
    <div class="title-container">
        <h1>Fun and Engaging EFL Quiz for Young Learners</h1>
    </div>

    <div class="container-section">
        <!-- Create New Question Section -->
        <div class="box">
            <h2>Create New Question</h2>

            <form action="questionssmart.php?id=<?php echo $quiz_id; ?>" method="POST" enctype="multipart/form-data">
                <!-- Hidden input to store question type -->
                <input type="hidden" id="questionType" name="question_type" value="">

                <!-- Question Title Field -->
                <div class="form-group">
                    <label for="questionTitle" class="media-label">Question Title</label>
                    <input type="text" class="form-control" id="questionTitle" name="question_title" placeholder="Enter question title" required>
                </div>

                <!-- File Upload for Image, Audio, or Video -->
                <div class="form-group">
                    <label for="mediaUpload" class="media-label">Image, Audio, or Video (optional)</label>
                    <input type="file" class="form-control" id="mediaUpload" name="media" accept="image/*,video/*,audio/*">
                    
                    <!-- Media Preview -->
                    <div class="media-preview" id="mediaPreview" style="display:none;">
                        <img src="#" alt="Image Preview" id="imagePreview" style="display:none; max-width: 100%; height: auto;">
                        <video controls id="videoPreview" style="display:none; max-width: 100%; height: auto;"></video>
                        <audio controls id="audioPreview" style="display:none; width: 100%;"></audio>
                        <div class="remove-media" onclick="removeMedia()">x</div>
                    </div>
                </div>

                <!-- Buttons for Question Type -->
                <div class="question-buttons">
                    <button type="button" class="btn btn-outline-info" onclick="selectType('check', this)">
                        <i class="fas fa-check-square"></i> Checking
                    </button>
                    <button type="button" class="btn btn-outline-warning" onclick="selectType('radio', this)">
                        <i class="fas fa-dot-circle"></i> Radio
                    </button>
                    <button type="button" class="btn btn-outline-success" onclick="selectType('select', this)">
                        <i class="fas fa-list-ul"></i> Select
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="selectType('image_select', this)">
                        <i class="fas fa-image"></i> Image Select
                    </button>
                </div>

                <!-- Dynamic Add/Remove Fields with Checkbox -->
                <div class="dynamic-fields" id="dynamicFields">
                    <div class="add-field-group">
                        <input type="text" class="form-control" name="option_value[]" placeholder="Enter option" oninput="updateValue(this)">
                        
                        <div class="checkbox-container">
                            <input type="checkbox" name="true_answer[]" value="" class="form-check-input">
                        </div>
                        
                        <div class="btn-add" onclick="addField()">
                            <i class="fas fa-plus"></i>
                        </div>
                    </div>
                </div>



                <!-- Submit Button -->
                <button type="submit" class="btn btn-custom">Submit</button>
            </form>
        </div>


<div class="container mt-5">
    <!-- Title Container -->
    <div class="title-container text-center mb-4">
        <h1>Quiz Questions</h1>
    </div>

    <div class="questions-container">
        <?php if (!empty($questions)): ?>
            <?php foreach ($questions as $index => $question): ?>
                <div class="question-box mb-4 p-3 border rounded shadow-sm d-flex justify-content-between align-items-center">
                    <div>
                        <h4><?php echo "Question " . ($index + 1) . ": " . htmlspecialchars($question['question_title']); ?></h4>
                        
                        <!-- Radio type question -->
                        <?php if ($question['type'] == 'radio'): ?>
                            <?php 
                            $true_answers = is_array($question['true_answer']) ? $question['true_answer'] : [$question['true_answer']];
                            ?>
                            <?php foreach ($question['answers'] as $answer): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="question_<?php echo $index; ?>" value="<?php echo htmlspecialchars($answer); ?>">
                                    <label class="form-check-label">
                                        <?php echo htmlspecialchars($answer); ?>
                                        <?php if (in_array(MEDIA_BASE_URL_FOR_DB ."uploads/images/" . $answer, $true_answers)): ?>
                                            <span class="correct-answer" style="color: green;">
                                                <i class="fas fa-check"></i>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        
                        <!-- Checkbox type question -->
                        <?php elseif ($question['type'] == 'check'): ?>
                            <?php 
                            $true_answers = is_array($question['true_answer']) ? $question['true_answer'] : [$question['true_answer']];
                            ?>
                            <?php foreach ($question['answers'] as $answer): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="question_<?php echo $index; ?>[]" value="<?php echo htmlspecialchars($answer); ?>">
                                    <label class="form-check-label">
                                        <?php echo htmlspecialchars($answer); ?>
                                        <?php if (in_array(MEDIA_BASE_URL_FOR_DB ."uploads/images/" . $answer, $true_answers)): ?>
                                            <span class="correct-answer" style="color: green;">
                                                <i class="fas fa-check"></i>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>

                        <!-- Select type question -->
                        <?php elseif ($question['type'] == 'select'): ?>
                            <select class="form-select" name="question_<?php echo $index; ?>">
                                <?php 
                                $true_answers = is_array($question['true_answer']) ? $question['true_answer'] : [$question['true_answer']];
                                ?>
                                <?php foreach ($question['answers'] as $answer): ?>
                                    <option value="<?php echo htmlspecialchars($answer); ?>" 
                                            <?php if (in_array(MEDIA_BASE_URL_FOR_DB ."uploads/images/" . $answer, $true_answers)) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($answer); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                        <!-- Image Select type question -->
                        <?php elseif ($question['type'] == 'image_select'): ?>
                            <div class="image-select-answers">
                                <?php foreach ($question['answers'] as $answer): ?>
                                    <img 
                                        src="<?php echo htmlspecialchars($answer); ?>" 
                                        class="img-thumbnail" 
                                        style="width: 100px; height: auto; margin-right: 10px; 
                                               <?php echo in_array($answer, $question['true_answer']) ? 'border: 3px solid blue;' : 'border: 2px solid transparent;'; ?>" 
                                        alt="Answer Image" />
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <!-- Remove Button -->
                        <a href="questionssmart.php?id=<?php echo $quiz_id; ?>&delete=<?php echo $question['id']; ?>" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i> Remove
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No questions available for this quiz.</p>
        <?php endif; ?>
    </div>
</div>

    </div>
</div>


<!-- JavaScript for Selecting Type, Dynamic Fields, and Media Preview -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set default type as "check" and initialize the field
    selectType('check', document.querySelector('.btn-outline-info')); // Simulate clicking the Checking button
});

// Element references
const mediaUpload = document.getElementById('mediaUpload');
const mediaPreview = document.getElementById('mediaPreview');
const imagePreview = document.getElementById('imagePreview');
const videoPreview = document.getElementById('videoPreview');
const audioPreview = document.getElementById('audioPreview');

// Preview media when file is uploaded
if (mediaUpload) {
    mediaUpload.addEventListener('change', function (event) {
        const file = event.target.files[0];
        if (file) {
            const fileType = file.type;

            // Reset previews
            imagePreview.style.display = 'none';
            videoPreview.style.display = 'none';
            audioPreview.style.display = 'none';
            mediaPreview.style.display = 'block'; // Show the preview container

            if (fileType.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else if (fileType.startsWith('video/')) {
                videoPreview.src = URL.createObjectURL(file);
                videoPreview.style.display = 'block';
            } else if (fileType.startsWith('audio/')) {
                audioPreview.src = URL.createObjectURL(file);
                audioPreview.style.display = 'block';
            }
        }
    });
}


function updateValue(inputField) {
    // Get the checkbox associated with the text input and update its value
    const checkbox = inputField.nextElementSibling.firstElementChild;
    checkbox.value = inputField.value;
}

function removeMedia() {
    // Hide the preview and reset the file input
    mediaPreview.style.display = 'none';
    imagePreview.style.display = 'none';
    videoPreview.style.display = 'none';
    audioPreview.style.display = 'none';
    mediaUpload.value = ''; // Reset file input
}

function addRadioField(isFirst = false) {
    const dynamicFields = document.getElementById('dynamicFields');

    const radioFieldGroup = document.createElement('div');
    radioFieldGroup.classList.add('add-field-group');

    const textInput = document.createElement('input');
    textInput.type = 'text';
    textInput.classList.add('form-control');
    textInput.name = 'option_value[]';
    textInput.placeholder = 'Enter option';

    // Radio button for single selection
    const newRadio = document.createElement('input');
    newRadio.type = 'radio';
    newRadio.classList.add('form-check-input');
    newRadio.name = 'true_answer'; 
    newRadio.value = ''; // This will be changed

    // Hidden input field
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'hidden_true_answer[]';
    hiddenInput.value = ''; // Will get the corresponding value

    // Update radio and hidden input values based on text input
    textInput.oninput = function() {
        newRadio.value = this.value; // Update radio value
        hiddenInput.value = this.value; // Update hidden input value
    };

    const radioContainer = document.createElement('div');
    radioContainer.classList.add('radio-container');
    radioContainer.appendChild(newRadio);
    radioContainer.appendChild(hiddenInput); // Add the hidden field

    const addButton = document.createElement('div');
    addButton.classList.add('btn-add');
    addButton.innerHTML = isFirst ? '<i class="fas fa-plus"></i>' : '<i class="fas fa-minus"></i>';
    if (!isFirst) {
        addButton.classList.add('btn-remove');
    }

    addButton.onclick = function () {
        if (isFirst) {
            addRadioField();
        } else {
            dynamicFields.removeChild(radioFieldGroup);
        }
    };

    // Add everything to the field group
    radioFieldGroup.appendChild(textInput);
    radioFieldGroup.appendChild(radioContainer);
    radioFieldGroup.appendChild(addButton);

    dynamicFields.appendChild(radioFieldGroup);
}


function selectType(type, element) {
    const dynamicFields = document.getElementById('dynamicFields');

    // Prevent action if the button is already active
    const buttons = document.querySelectorAll('.question-buttons .btn');
    if (element.classList.contains('active')) {
        return; // Do nothing if this button is already active
    }

    // Set the question type in the hidden input
    document.getElementById('questionType').value = type;

    // Remove active class from all buttons
    buttons.forEach(button => { button.classList.remove('active'); });

    // Add active class to the clicked button
    element.classList.add('active');

    // Clear current fields
    dynamicFields.innerHTML = ''; 

    // Handle image_select type (no need to check for imageUploadSection anymore)
    if (type === 'image_select') {
        addImageField(true); // Call addImageField for image select type
    } else {
        addField(true); // For other types, add text input fields
    }
}


function addImageField(isFirst = false) {
    const dynamicFields = document.getElementById('dynamicFields');

    // Image upload field group
    const imageFieldGroup = document.createElement('div');
    imageFieldGroup.classList.add('add-field-group');

    // Image input field
    const imageInput = document.createElement('input');
    imageInput.type = 'file';
    imageInput.name = 'option_value[]';
    imageInput.accept = 'image/*';
    imageInput.classList.add('form-control');

    // Image preview container
    const imagePreview = document.createElement('img');
    imagePreview.style.maxWidth = '100px'; // Small preview size
    imagePreview.style.marginTop = '10px';
    imagePreview.style.display = 'none'; // Hide initially

    // Checkbox to mark the correct answer
    const newCheckbox = document.createElement('input');
    newCheckbox.type = 'checkbox';
    newCheckbox.classList.add('form-check-input');
    newCheckbox.name = 'true_answer[]';

    // Hidden input to store the filename
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'option_value[]';
    hiddenInput.value = ''; // Store filename

    // Event listener for image upload and preview display
    imageInput.addEventListener('change', function (event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block'; // Show the image preview
            };
            reader.readAsDataURL(file);

            // Update checkbox and hidden input with the full file path
            const filePath = 'https://media.kipeducationid.com/uploads/images/' + file.name; // Full file path
            newCheckbox.value = filePath;
            hiddenInput.value = filePath; // Save the full path in the hidden input
        }
    });

    // Create a container for checkbox and hidden input
    const checkboxContainer = document.createElement('div');
    checkboxContainer.classList.add('checkbox-container');
    checkboxContainer.appendChild(newCheckbox);
    checkboxContainer.appendChild(hiddenInput);

    // Add and remove buttons for dynamic fields
    const addButton = document.createElement('div');
    addButton.classList.add('btn-add');
    addButton.innerHTML = isFirst ? '<i class="fas fa-plus"></i>' : '<i class="fas fa-minus"></i>';
    if (!isFirst) {
        addButton.classList.add('btn-remove');
    }

    // Add or remove field group
    addButton.onclick = function () {
        if (isFirst) {
            addImageField();
        } else {
            dynamicFields.removeChild(imageFieldGroup);
        }
    };

    // Append all elements to the imageFieldGroup
    imageFieldGroup.appendChild(imageInput);
    imageFieldGroup.appendChild(imagePreview);
    imageFieldGroup.appendChild(checkboxContainer);
    imageFieldGroup.appendChild(addButton);

    // Add the new field group to dynamic fields
    dynamicFields.appendChild(imageFieldGroup);
}

function addField(isFirst = false) {
    const dynamicFields = document.getElementById('dynamicFields');

    // Create a new field group for each option
    const newFieldGroup = document.createElement('div');
    newFieldGroup.classList.add('add-field-group');

    const newInput = document.createElement('input');
    newInput.type = 'text';
    newInput.classList.add('form-control');
    newInput.name = 'option_value[]';
    newInput.placeholder = 'Enter option';

    const newCheckbox = document.createElement('input');
    newCheckbox.type = 'checkbox';
    newCheckbox.classList.add('form-check-input');
    newCheckbox.name = 'true_answer[]';
    newCheckbox.value = '';

    newInput.oninput = function () {
        newCheckbox.value = this.value;
    };

    const checkboxContainer = document.createElement('div');
    checkboxContainer.classList.add('checkbox-container');
    checkboxContainer.appendChild(newCheckbox);

    const addButton = document.createElement('div');
    addButton.classList.add('btn-add');
addButton.innerHTML = isFirst ? '<i class="fas fa-plus"></i>' : '<i class="fas fa-minus"></i>';
    if (!isFirst) {
        addButton.classList.add('btn-remove');
    }

    addButton.onclick = function () {
        if (isFirst) {
            addField();
        } else {
            dynamicFields.removeChild(newFieldGroup);
        }
    };

    newFieldGroup.appendChild(newInput);
    newFieldGroup.appendChild(checkboxContainer);
    newFieldGroup.appendChild(addButton);

    dynamicFields.appendChild(newFieldGroup);
}


</script>

<!-- Bootstrap JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Include the database connection
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'db_connect.php';

// Default title
$quizTitle = "Quiz Title Not Available"; // Fallback title if not found

// Array to store questions
$questions = [];

// Проверяем, был ли передан ID в URL
if (isset($_GET['id'])) {
    $quizId = $_GET['id'];

    // Получаем название викторины по ID
    $query = "SELECT title FROM quizzes WHERE id = ?";
    $stmt = $conn->prepare($query);

    // Добавляем проверку на успешную подготовку запроса
    if ($stmt === false) {
        die('Ошибка подготовки запроса: ' . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("i", $quizId);
    $stmt->execute();
    $stmt->bind_result($retrievedTitle);
    if ($stmt->fetch()) {
        $quizTitle = $retrievedTitle;
    }
    $stmt->close();

    // Получаем вопросы для викторины
    $query = "SELECT id, question_title, image, video, audio, answer_1, true_answer, false_answer FROM questions WHERE quiz_id = ?";
    $stmt = $conn->prepare($query);

    // Добавляем проверку на успешную подготовку запроса
    if ($stmt === false) {
        die('Ошибка подготовки запроса: ' . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("i", $quizId);
    $stmt->execute();
    $result = $stmt->get_result();
    $questions = [];
    while ($row = $result->fetch_assoc()) {
        // Преобразуем true_answer и false_answer в массивы
        $row['true_answer'] = explode(',', $row['true_answer']);
        $row['false_answer'] = explode(',', $row['false_answer']);
        $questions[] = $row;
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quizTitle); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f0f4f8, #d9e2ec);
            font-family: 'Arial', sans-serif;
        }
        .page-title {
            background-color: #007bff;
            color: #fff;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            margin-top: 20px;
            box-shadow: 0px 6px 12px rgba(0, 0, 0, 0.1);
        }
        .form-container, .questions-container {
            background: #fff;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
            border: 1px solid #e0e0e0;
        }
        .form-container {
            max-width: 500px;
            width: 100%;
        }
        .questions-container {
            margin-top: 0;
            max-width: 550px;
            width: 100%;
        }
        .question-item {
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        .question-title {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .answer-item {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 5px;
        }
        .answer-buttons {
            display: flex;
            gap: 5px;
        }
        .answer-buttons button {
            width: 28px;
            height: 28px;
            padding: 0;
            border: none;
            background-color: #f5f5f5;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .answer-buttons button:hover {
            background-color: #e0e0e0;
        }
        .icon {
            font-size: 1rem;
        }
        .media-preview {
            margin-top: 10px;
        }
        .media-preview img, .media-preview video {
            max-width: 120px;
            max-height: 100px;
            border-radius: 8px;
        }
        .media-preview audio {
            width: 120px;
        }
          .remove-media-button {
        position: absolute;
        top: 50%;
        right: -20px;
        cursor: pointer;
        color: red;
        font-size: 20px;
        transform: translateY(-50%);
        display: none;
    }
 .custom-button {
        background-color: transparent; /* No background color by default */
        color: #007bff; /* Text color */
        border: 1px solid transparent; /* No border by default */
        padding: 5px 10px; /* Adjust padding for closer buttons */
        border-radius: 5px;
        margin-right: 3px; /* Reduce space between buttons */
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
    }

    .custom-button i {
        margin-right: 5px; /* Reduced spacing between icon and text */
    }

    /* Active (clicked) state */
    .custom-button.active {
        background-color: #007bff; /* Blue background when active */
        color: #ffffff; /* White text color when active */
        border: 1px solid #007bff; /* Blue border when active */
    }

    /* Ensures all buttons are the same width */
    .button-group {
        display: flex;
        justify-content: flex-start; /* Align buttons next to each other */
        gap: 3px; /* Smaller gap between buttons */
    }
    </style>
</head>
<body>

<?php include 'headeradmin.php'; ?>

<div class="container">
    <div class="page-title">
        <h1><?php echo htmlspecialchars($quizTitle); ?></h1>
    </div>

    <div class="row">
        <div class="col-md-6">
            <!-- Edit Question Container -->
            <div class="edit-question-container form-container" style="display: none; margin-bottom: 20px;">
                <h3>Edit Question</h3>
                <form id="editQuestionForm" action="update_question.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="question_id" id="editQuestionId">

                    <!-- Question Title -->
                    <div class="form-group">
                        <label for="editQuestionTitle">Question Title</label>
                        <input type="text" class="form-control" id="editQuestionTitle" name="question_title" required>
                    </div>

                    <!-- Upload Image, File, or Video -->
                    <div class="form-group">
                        <label for="editMedia">Upload Image/File/Video</label>
                        <div style="position: relative; display: inline-block;">
                            <input type="file" class="form-control" id="editMedia" name="media" accept="image/*,video/*,audio/*">
                            <!-- X Button for removing the media -->
                            <span class="remove-media" style="position: absolute; top: 50%; right: -20px; cursor: pointer; color: red; font-size: 20px; transform: translateY(-50%);">&times;</span>
                        </div>
                        <!-- Media Preview Section -->
                        <div class="media-preview" id="editMediaPreview" style="position: relative; margin-top: 10px;"></div>
                    </div>

                    <!-- Answers Input -->
                    <div class="form-group">
                        <label>Answers</label>
                        <div id="editAnswersContainer"></div>
                    </div>

                    <!-- Save and Close Buttons -->
                    <button type="button" class="btn btn-primary" id="saveEditedQuestion">Save</button>
                    <button type="button" class="btn btn-secondary" id="closeEditContainer">Close</button>
                </form>
            </div>

            <div class="form-container">
                <h3>Create New Question</h3>
                <form action="create_question.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="quiz_id" value="<?php echo htmlspecialchars($quizId); ?>">

                    <!-- Question Title -->
                    <div class="form-group">
                        <label for="questionTitle">Question Title</label>
                        <input type="text" class="form-control" id="questionTitle" name="question_title" required>
                    </div>

                    <!-- Upload Image, File, or Video -->
                    <div class="form-group">
                        <label for="createMedia">Upload Image/File/Video</label>
                        <div style="position: relative; display: inline-block;">
                            <input type="file" class="form-control" id="createMedia" name="media" accept="image/*,video/*,audio/*">
                            <!-- X Button for removing the media -->
                            <span class="remove-media-button" style="display:none;">&times;</span>
                        </div>
                        <!-- Media Preview Section -->
                        <div class="media-preview" id="createMediaPreview" style="position: relative; margin-top: 10px;"></div>
                    </div>

                    <!-- Answers Input (for Checking) -->
                    <div class="form-group" id="answersInputContainer">
                        <label>Answers</label>
                        <div id="answersContainer">
                            <div class="answer-item d-flex align-items-center mb-2">
                                <input type="text" class="form-control" name="answer[]" placeholder="Answer 1" required>
                                <input type="hidden" name="answer_checkbox[]" class="answer-checkbox" value="false">
                                <div class="answer-buttons ms-2">
                                    <button type="button" class="toggle-status">
                                        <i class="icon bi bi-square"></i>
                                    </button>
                                    <button type="button" class="add-answer">
                                        <i class="icon bi bi-plus"></i>
                                    </button>
                                    <button type="button" class="delete-answer">
                                        <i class="icon bi bi-trash"></i>
                                    </button>
                                    <button type="button" class="move-up">
                                        <i class="icon bi bi-arrow-up"></i>
                                    </button>
                                    <button type="button" class="move-down">
                                        <i class="icon bi bi-arrow-down"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary w-100">Create New Question</button>
                </form>
            </div>
        </div>

        <div class="col-md-6">
            <div class="questions-container">
                <h3>Questions</h3>
                <?php if (!empty($questions)) : ?>
                    <?php foreach ($questions as $question) : ?>
                        <div class="question-item" data-question-id="<?php echo $question['id']; ?>" style="position: relative; padding-bottom: 40px;">
                            <div class="question-title">
                                <?php echo htmlspecialchars($question['question_title']); ?>
                            </div>

                            <!-- Display image if available -->
                            <?php if (!empty($question['image'])) : ?>
                                <div class="media-content">
                                    <img src="<?php echo htmlspecialchars($question['image']); ?>" alt="Question Image" style="max-width: 100%; max-height: 200px; margin-top: 10px; border-radius: 8px;">
                                </div>
                            <?php endif; ?>

                            <!-- Display video if available -->
                            <?php if (!empty($question['video']) && preg_match('/\.(mp4|avi|mov)$/i', $question['video'])) : ?>
                                <div class="media-content">
                                    <video controls style="max-width: 100%; max-height: 200px; margin-top: 10px; border-radius: 8px;">
                                        <source src="<?php echo htmlspecialchars($question['video']); ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                </div>
                            <?php endif; ?>

                            <!-- Display audio if available -->
                            <?php if (!empty($question['audio']) && preg_match('/\.(mp3|wav)$/i', $question['audio'])) : ?>
                                <div class="media-content">
                                    <audio controls style="width: 100%; margin-top: 10px;">
                                        <source src="<?php echo htmlspecialchars($question['audio']); ?>" type="audio/mpeg">
                                        Your browser does not support the audio element.
                                    </audio>
                                </div>
                            <?php endif; ?>

                            <!-- Display answers -->
                            <div class="answers-list">
                                <?php for ($i = 1; $i <= 10; $i++) : ?>
                                    <?php if (!empty($question["answer_$i"])) : ?>
                                        <div class="answer-item">
                                            <i class="icon bi <?php echo (in_array($i, explode(',', $question['true_answer']))) ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger'; ?>"></i>
                                            <?php echo htmlspecialchars($question["answer_$i"]); ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>

                            <!-- Edit and Delete Icons -->
                            <div class="question-actions" style="position: absolute; bottom: 10px; right: 10px;">
                                <button class="edit-question btn btn-sm btn-outline-primary" style="margin-right: 5px;">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="delete-question btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p>No questions available for this quiz.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>



<script>
   // Get all the buttons
const buttons = document.querySelectorAll('.custom-button');

// Add click event listener to each button
buttons.forEach(button => {
    button.addEventListener('click', () => {
        // Remove the 'active' class from all buttons
        buttons.forEach(btn => btn.classList.remove('active'));

        // Add the 'active' class to the clicked button
        button.classList.add('active');
    });
});

document.addEventListener("DOMContentLoaded", function () {
    document.getElementById('answersInputContainer').style.display = 'block'; // Show by default for Checking

    // Ensure only the required inputs are enabled for the active section
    updateRequiredFields('checking');

    // Add event listeners for buttons
    document.getElementById('checkingButton').addEventListener('click', function () {
        activateButton('checkingButton');
        document.getElementById('answersInputContainer').style.display = 'block'; // Show Answers input
        document.getElementById('radioInputContainer').style.display = 'none'; // Hide Radio input
        document.getElementById('selectInputContainer').style.display = 'none'; // Hide Select input
        document.getElementById('imageSelectInputContainer').style.display = 'none'; // Hide Image Select
        updateRequiredFields('checking');
    });

    document.getElementById('radioButton').addEventListener('click', function () {
        activateButton('radioButton');
        document.getElementById('answersInputContainer').style.display = 'none'; // Hide Answers input
        document.getElementById('radioInputContainer').style.display = 'block'; // Show Radio input
        document.getElementById('selectInputContainer').style.display = 'none'; // Hide Select input
        document.getElementById('imageSelectInputContainer').style.display = 'none'; // Hide Image Select
        updateRequiredFields('radio');
    });

    document.getElementById('selectButton').addEventListener('click', function () {
        activateButton('selectButton');
        document.getElementById('answersInputContainer').style.display = 'none'; // Hide Answers input
        document.getElementById('radioInputContainer').style.display = 'none'; // Hide Radio input
        document.getElementById('selectInputContainer').style.display = 'block'; // Show Select input
        document.getElementById('imageSelectInputContainer').style.display = 'none'; // Hide Image Select
        updateRequiredFields('select');
    });

    document.getElementById('imageSelectButton').addEventListener('click', function () {
        activateButton('imageSelectButton');
        document.getElementById('answersInputContainer').style.display = 'none'; // Hide Answers input
        document.getElementById('radioInputContainer').style.display = 'none'; // Hide Radio input
        document.getElementById('selectInputContainer').style.display = 'none'; // Hide Select input
        document.getElementById('imageSelectInputContainer').style.display = 'block'; // Show Image Select input
        updateRequiredFields('image');
    });
});
// Function to add and remove 'required' attribute based on selected type
function updateRequiredFields(activeType) {
    const checkingInputs = document.querySelectorAll('#answersInputContainer input');
    const radioInputs = document.querySelectorAll('#radioInputContainer input');
    const selectInputs = document.querySelectorAll('#selectInputContainer input');
    const imageSelectInputs = document.querySelectorAll('#imageSelectInputContainer input');

    checkingInputs.forEach(input => input.required = (activeType === 'checking'));
    radioInputs.forEach(input => input.required = (activeType === 'radio'));
    selectInputs.forEach(input => input.required = (activeType === 'select'));
    imageSelectInputs.forEach(input => input.required = (activeType === 'image'));
}

// Helper function to activate the clicked button and deactivate others
function activateButton(activeButtonId) {
    const buttons = ['checkingButton', 'radioButton', 'selectButton', 'imageSelectButton'];
    buttons.forEach(function (buttonId) {
        const button = document.getElementById(buttonId);
        if (buttonId === activeButtonId) {
            button.classList.add('active');  // Add 'active' class to the clicked button
        } else {
            button.classList.remove('active');  // Remove 'active' class from other buttons
        }
    });
}

// Function to handle image preview after selection
function handleImagePreview(fileInput) {
    const previewContainer = fileInput.nextElementSibling;
    const file = fileInput.files[0];

    // Clear any previous preview
    previewContainer.innerHTML = '';

    if (file) {
        const fileURL = URL.createObjectURL(file);
        const img = document.createElement('img');
        img.src = fileURL;
        img.style.maxWidth = '80px';
        img.style.maxHeight = '80px';
        img.style.borderRadius = '8px';
        img.style.cursor = 'pointer'; // Add pointer cursor
        img.classList.add('selectable-image'); // Add class for selection
        previewContainer.appendChild(img);

        // Add click event to toggle blue border for multiple selections
        img.addEventListener('click', function () {
            toggleImageSelection(this);
        });
    }
}

// Function to toggle blue border for selected image (Allow Multiple Selections)
function toggleImageSelection(imgElement) {
    // Toggle the border for the clicked image
    if (imgElement.style.border) {
        imgElement.style.border = ''; // Remove border if it's already selected
    } else {
        imgElement.style.border = '3px solid #007bff'; // Add blue border if not selected
    }
}

// Function to add new Image Select answer field
document.querySelector('.add-image-select-answer').addEventListener('click', function () {
    const imageSelectAnswersContainer = document.getElementById('imageSelectAnswersContainer');
    const newAnswer = document.createElement('div');
    newAnswer.classList.add('answer-item', 'd-flex', 'align-items-center', 'mb-2');
    newAnswer.innerHTML = `
        <input type="file" class="form-control image-upload" name="image_select[]" accept="image/*" required>
        <div class="preview-container ms-2"></div> <!-- Preview will be shown here -->
        <div class="answer-buttons ms-2">
            <button type="button" class="add-image-select-answer">
                <i class="icon bi bi-plus"></i>
            </button>
            <button type="button" class="delete-image-select-answer">
                <i class="icon bi bi-trash"></i>
            </button>
            <button type="button" class="move-image-select-up">
                <i class="icon bi bi-arrow-up"></i>
            </button>
            <button type="button" class="move-image-select-down">
                <i class="icon bi bi-arrow-down"></i>
            </button>
        </div>
    `;
    imageSelectAnswersContainer.appendChild(newAnswer);

    // Initialize image preview and answer buttons for the new answer item
    initializeImageSelectAnswerButtons(newAnswer);
});

// Initialize image preview and answer buttons for existing answer items
document.querySelectorAll('.image-upload').forEach(input => {
    input.addEventListener('change', function () {
        handleImagePreview(this);
    });
});

// Function to initialize Image Select answer buttons and preview
function initializeImageSelectAnswerButtons(answerItem) {
    const fileInput = answerItem.querySelector('.image-upload');
    const addButton = answerItem.querySelector('.add-image-select-answer');
    const deleteButton = answerItem.querySelector('.delete-image-select-answer');
    const moveUpButton = answerItem.querySelector('.move-image-select-up');
    const moveDownButton = answerItem.querySelector('.move-image-select-down');

    // Ensure that adding new answer field works
    addButton.addEventListener('click', function () {
        addNewImageSelectAnswer(); // Call the function to add a new answer field
    });

    fileInput.addEventListener('change', function () {
        handleImagePreview(this);
    });

    deleteButton.addEventListener('click', () => answerItem.remove());

    moveUpButton.addEventListener('click', () => {
        const previousSibling = answerItem.previousElementSibling;
        if (previousSibling) {
            imageSelectAnswersContainer.insertBefore(answerItem, previousSibling);
        }
    });

    moveDownButton.addEventListener('click', () => {
        const nextSibling = answerItem.nextElementSibling;
        if (nextSibling) {
            imageSelectAnswersContainer.insertBefore(nextSibling, answerItem);
        }
    });
}

// Function to add a new Image Select answer field (for '+' button functionality)
function addNewImageSelectAnswer() {
    const imageSelectAnswersContainer = document.getElementById('imageSelectAnswersContainer');
    const newAnswer = document.createElement('div');
    newAnswer.classList.add('answer-item', 'd-flex', 'align-items-center', 'mb-2');
    newAnswer.innerHTML = `
        <input type="file" class="form-control image-upload" name="image_select[]" accept="image/*" required>
        <div class="preview-container ms-2"></div>
        <div class="answer-buttons ms-2">
            <button type="button" class="add-image-select-answer">
                <i class="icon bi bi-plus"></i>
            </button>
            <button type="button" class="delete-image-select-answer">
                <i class="icon bi bi-trash"></i>
            </button>
            <button type="button" class="move-image-select-up">
                <i class="icon bi bi-arrow-up"></i>
            </button>
            <button type="button" class="move-image-select-down">
                <i class="icon bi bi-arrow-down"></i>
            </button>
        </div>
    `;
    imageSelectAnswersContainer.appendChild(newAnswer);

    // Re-initialize event listeners for the newly added answer field
    initializeImageSelectAnswerButtons(newAnswer);
}

// Function to add new Select answer field (Radio)
document.querySelector('.add-select-answer').addEventListener('click', function () {
    const selectAnswersContainer = document.getElementById('selectAnswersContainer');
    const newAnswer = document.createElement('div');
    newAnswer.classList.add('answer-item', 'd-flex', 'align-items-center', 'mb-2');
    newAnswer.innerHTML = `
        <input type="text" class="form-control" name="select_answer[]" placeholder="New Answer" required>
        <input type="radio" name="select_radio" class="select-radio ms-2"> <!-- Radio instead of Checkbox -->
        <div class="answer-buttons ms-2">
            <button type="button" class="add-select-answer">
                <i class="icon bi bi-plus"></i>
            </button>
            <button type="button" class="delete-select-answer">
                <i class="icon bi bi-trash"></i>
            </button>
            <button type="button" class="move-select-up">
                <i class="icon bi bi-arrow-up"></i>
            </button>
            <button type="button" class="move-select-down">
                <i class="icon bi bi-arrow-down"></i>
            </button>
        </div>
    `;
    selectAnswersContainer.appendChild(newAnswer);

    // Initialize newly added answer item buttons
    initializeSelectAnswerButtons(newAnswer);
});

// Function to initialize Select answer buttons
function initializeSelectAnswerButtons(answerItem) {
    const addButton = answerItem.querySelector('.add-select-answer');
    const deleteButton = answerItem.querySelector('.delete-select-answer');
    const moveUpButton = answerItem.querySelector('.move-select-up');
    const moveDownButton = answerItem.querySelector('.move-select-down');

    addButton.addEventListener('click', function () {
        addNewSelectAnswer(); // Call function to add new Select answer field
    });

    deleteButton.addEventListener('click', () => answerItem.remove());

    moveUpButton.addEventListener('click', () => {
        const previousSibling = answerItem.previousElementSibling;
        if (previousSibling) {
            selectAnswersContainer.insertBefore(answerItem, previousSibling);
        }
    });

    moveDownButton.addEventListener('click', () => {
        const nextSibling = answerItem.nextElementSibling;
        if (nextSibling) {
            selectAnswersContainer.insertBefore(nextSibling, answerItem);
        }
    });
}

// Function to add a new Select answer field (for '+' button functionality)
function addNewSelectAnswer() {
    const selectAnswersContainer = document.getElementById('selectAnswersContainer');
    const newAnswer = document.createElement('div');
    newAnswer.classList.add('answer-item', 'd-flex', 'align-items-center', 'mb-2');
    newAnswer.innerHTML = `
        <input type="text" class="form-control" name="select_answer[]" placeholder="New Answer" required>
        <input type="radio" name="select_radio" class="select-radio ms-2">
        <div class="answer-buttons ms-2">
            <button type="button" class="add-select-answer">
                <i class="icon bi bi-plus"></i>
            </button>
            <button type="button" class="delete-select-answer">
                <i class="icon bi bi-trash"></i>
            </button>
            <button type="button" class="move-select-up">
                <i class="icon bi bi-arrow-up"></i>
            </button>
            <button type="button" class="move-select-down">
                <i class="icon bi bi-arrow-down"></i>
            </button>
        </div>
    `;
    selectAnswersContainer.appendChild(newAnswer);

    // Re-initialize event listeners for the newly added answer field
    initializeSelectAnswerButtons(newAnswer);
}

// Function to add new Radio answer field
document.querySelector('.add-radio-answer').addEventListener('click', function () {
    const radioAnswersContainer = document.getElementById('radioAnswersContainer');
    const newAnswer = document.createElement('div');
    newAnswer.classList.add('answer-item', 'd-flex', 'align-items-center', 'mb-2');
    newAnswer.innerHTML = `
        <input type="text" class="form-control" name="radio_answer[]" placeholder="New Answer" required>
        <input type="radio" name="radio_select" class="radio-select ms-2" value="false">
        <div class="answer-buttons ms-2">
            <button type="button" class="add-radio-answer">
                <i class="icon bi bi-plus"></i>
            </button>
            <button type="button" class="delete-radio-answer">
                <i class="icon bi bi-trash"></i>
            </button>
            <button type="button" class="move-radio-up">
                <i class="icon bi bi-arrow-up"></i>
            </button>
            <button type="button" class="move-radio-down">
                <i class="icon bi bi-arrow-down"></i>
            </button>
        </div>
    `;
    radioAnswersContainer.appendChild(newAnswer);

    // Initialize newly added answer item buttons
    initializeRadioAnswerButtons(newAnswer);
});

// Function to initialize Radio answer buttons
function initializeRadioAnswerButtons(answerItem) {
    const addButton = answerItem.querySelector('.add-radio-answer');
    const deleteButton = answerItem.querySelector('.delete-radio-answer');
    const moveUpButton = answerItem.querySelector('.move-radio-up');
    const moveDownButton = answerItem.querySelector('.move-radio-down');

    addButton.addEventListener('click', function () {
        addNewRadioAnswer(); // Call function to add new Radio answer field
    });

    deleteButton.addEventListener('click', () => answerItem.remove());

    moveUpButton.addEventListener('click', () => {
        const previousSibling = answerItem.previousElementSibling;
        if (previousSibling) {
            radioAnswersContainer.insertBefore(answerItem, previousSibling);
        }
    });

    moveDownButton.addEventListener('click', () => {
        const nextSibling = answerItem.nextElementSibling;
        if (nextSibling) {
            radioAnswersContainer.insertBefore(nextSibling, answerItem);
        }
    });
}

// Function to add a new Radio answer field (for '+' button functionality)
function addNewRadioAnswer() {
    const radioAnswersContainer = document.getElementById('radioAnswersContainer');
    const newAnswer = document.createElement('div');
    newAnswer.classList.add('answer-item', 'd-flex', 'align-items-center', 'mb-2');
    newAnswer.innerHTML = `
        <input type="text" class="form-control" name="radio_answer[]" placeholder="New Answer" required>
        <input type="radio" name="radio_select" class="radio-select ms-2">
        <div class="answer-buttons ms-2">
            <button type="button" class="add-radio-answer">
                <i class="icon bi bi-plus"></i>
            </button>
            <button type="button" class="delete-radio-answer">
                <i class="icon bi bi-trash"></i>
            </button>
            <button type="button" class="move-radio-up">
                <i class="icon bi bi-arrow-up"></i>
            </button>
            <button type="button" class="move-radio-down">
                <i class="icon bi bi-arrow-down"></i>
            </button>
        </div>
    `;
    radioAnswersContainer.appendChild(newAnswer);

    // Re-initialize event listeners for the newly added answer field
    initializeRadioAnswerButtons(newAnswer);
}

</script>



<script>
const answersContainer = document.getElementById('answersContainer');
let mediaToDelete = false; // Ստեղծեք փոփոխական՝ մեդիան ջնջելու համար


// Function to delete an answer item
function deleteAnswer(answerItem) {
    answerItem.remove();
}

// Function to move an answer item up
function moveAnswerUp(answerItem) {
    const previousSibling = answerItem.previousElementSibling;
    if (previousSibling) {
        answersContainer.insertBefore(answerItem, previousSibling);
    }
}

// Function to move an answer item down
function moveAnswerDown(answerItem) {
    const nextSibling = answerItem.nextElementSibling;
    if (nextSibling) {
        answersContainer.insertBefore(nextSibling, answerItem);
    }
}

document.getElementById('saveEditedQuestion').addEventListener('click', function () {
    const formData = new FormData();
    const questionId = document.getElementById('editQuestionId').value;
    const questionTitle = document.getElementById('editQuestionTitle').value;
    const mediaFile = document.getElementById('editMedia').files[0];

    // Ստուգեք, որ հարցման ID և վերնագիր գոյություն ունեն
    if (!questionId || !questionTitle) {
        alert('Question ID and title are required.');
        return;
    }

    // Ավելացրեք տվյալները `FormData`-ում
    formData.append('question_id', questionId);
    formData.append('question_title', questionTitle);
    formData.append('media_to_delete', mediaToDelete); // Ավելացրեք mediaToDelete նշումը

    // Ավելացրեք մեդիա ֆայլը, եթե այն ընտրված է
    if (mediaFile) {
        formData.append('media', mediaFile);
    }

    // Հավաքեք պատասխանները և checkbox-ի ստատուսները
    const answers = [];
    const trueAnswer = [];
    const falseAnswer = [];

    document.querySelectorAll('#editAnswersContainer .answer-item').forEach((answerItem, index) => {
        const answerText = answerItem.querySelector('input[type="text"]').value;
        const checkbox = answerItem.querySelector('.answer-checkbox');

        // Ավելացրեք պատասխանը answers զանգվածում
        if (answerText) {
            answers.push(answerText);
        }

        // Որոշեք, արդյոք այս պատասխանն ընտրված է որպես ճիշտ, թե սխալ
        if (checkbox && checkbox.value === 'true') {
            trueAnswer.push(index + 1);
        } else {
            falseAnswer.push(index + 1);
        }
    });

    formData.append('answers', JSON.stringify(answers));
    formData.append('true_answer', trueAnswer.join(','));
    formData.append('false_answer', falseAnswer.join(','));
    formData.append('ratio', document.querySelector('input[name="answer_confirmation"]:checked').value);

    // Ուղարկեք սերվերին
    fetch('update_question.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Թարմացրեք էջը առանց "alert"-ի
            location.reload();
        } else {
            console.error('Failed to update the question: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
});

// Function to update media preview when a new file is selected
document.getElementById('createMedia').addEventListener('change', function () {
    const mediaPreview = document.getElementById('createMediaPreview');
    const removeMediaBtn = document.querySelector('.remove-media-button');
    const file = this.files[0];

    // Clear the previous preview
    mediaPreview.innerHTML = '';

    if (file) {
        const fileURL = URL.createObjectURL(file);
        let mediaElement;

        // Check the file type and create the corresponding element
        if (file.type.startsWith('image/')) {
            mediaElement = document.createElement('img');
            mediaElement.style.maxWidth = '100px'; // Small preview size
            mediaElement.style.maxHeight = '80px';
            mediaElement.style.borderRadius = '8px';
            mediaElement.src = fileURL;
        } else if (file.type.startsWith('video/')) {
            mediaElement = document.createElement('video');
            mediaElement.style.maxWidth = '100px'; // Small preview size
            mediaElement.style.maxHeight = '80px';
            mediaElement.controls = true;
            mediaElement.src = fileURL;
        } else if (file.type.startsWith('audio/')) {
            mediaElement = document.createElement('audio');
            mediaElement.controls = true;
            mediaElement.style.width = '100px'; // Small preview size
            mediaElement.src = fileURL;
        } else {
            const message = document.createElement('p');
            message.textContent = 'File type not supported for preview';
            mediaElement = message;
        }

        // Add the media element to the preview container
        mediaPreview.appendChild(mediaElement);
        // Display the remove button
        removeMediaBtn.style.display = 'block';
    }
});

// Function to handle removing the media preview
document.querySelector('.remove-media-button').addEventListener('click', function () {
    const createMediaInput = document.getElementById('createMedia');
    const mediaPreview = document.getElementById('createMediaPreview');

    // Clear the file input and preview
    createMediaInput.value = ''; // Clear the input
    mediaPreview.innerHTML = ''; // Clear the preview
    this.style.display = 'none'; // Hide the "x" button
});

// Close the edit container
document.getElementById('closeEditContainer').addEventListener('click', function () {
    document.querySelector('.edit-question-container').style.display = 'none';
});

// Add event listener to the delete button
document.querySelectorAll('.delete-question').forEach(button => {
    button.addEventListener('click', function () {
        const questionItem = this.closest('.question-item');
        const questionId = questionItem.getAttribute('data-question-id');

        // Confirm before deleting
        if (confirm('Are you sure you want to delete this question?')) {
            // Send an AJAX request to delete the question
            fetch('delete_question.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: questionId }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    questionItem.remove();
                } else {
                    alert('Failed to delete the question.');
                }
            })
            .catch(error => console.error('Error:', error));
        }
    });
});

// Function to initialize answer item buttons
function initializeAnswerButtons(answerItem) {
    const toggleStatusBtn = answerItem.querySelector('.toggle-status');
    const addAnswerBtn = answerItem.querySelector('.add-answer');
    const deleteAnswerBtn = answerItem.querySelector('.delete-answer');
    const moveUpBtn = answerItem.querySelector('.move-up');
    const moveDownBtn = answerItem.querySelector('.move-down');
    const answerCheckbox = answerItem.querySelector('.answer-checkbox');

    // Check if answerCheckbox exists before accessing its value
    if (answerCheckbox) {
        // Set the initial value of the checkbox, if not already set
        if (!answerCheckbox.value) {
            answerCheckbox.value = 'false'; // Default to false
        }

        // Update the visual state based on the checkbox value
        updateVisualState(toggleStatusBtn, answerCheckbox.value);
    } else {
        if (toggleStatusBtn) {
            updateVisualState(toggleStatusBtn, 'false');
        }
    }

    // Add click event to toggle status button
    if (toggleStatusBtn) {
        toggleStatusBtn.addEventListener('click', () => {
            const icon = toggleStatusBtn.querySelector('.icon');
            if (icon.classList.contains('bi-square')) {
                icon.classList.remove('bi-square');
                icon.classList.add('bi-check-square-fill');
                icon.style.color = 'green';
                if (answerCheckbox) {
                    answerCheckbox.value = 'true';
                }
            } else if (icon.classList.contains('bi-check-square-fill')) {
                icon.classList.remove('bi-check-square-fill');
                icon.classList.add('bi-x-square-fill');
                icon.style.color = 'red';
                if (answerCheckbox) {
                    answerCheckbox.value = 'false';
                }
            } else {
                icon.classList.remove('bi-x-square-fill');
                icon.classList.add('bi-check-square-fill');
                icon.style.color = 'green';
                if (answerCheckbox) {
                    answerCheckbox.value = 'true';
                }
            }
        });
    }

    // Add event listener to the add button
    if (addAnswerBtn) {
        addAnswerBtn.addEventListener('click', () => {
            duplicateAnswer(answerItem);
        });
    }

    // Add event listener to the delete button
    if (deleteAnswerBtn) {
        deleteAnswerBtn.addEventListener('click', () => {
            deleteAnswer(answerItem);
        });
    }

    // Add event listener to the move up button
    if (moveUpBtn) {
        moveUpBtn.addEventListener('click', () => {
            moveAnswerUp(answerItem);
        });
    }

    // Add event listener to the move down button
    if (moveDownBtn) {
        moveDownBtn.addEventListener('click', () => {
            moveAnswerDown(answerItem);
        });
    }
}

// Function to update the visual state of the toggle button
function updateVisualState(toggleStatusBtn, value) {
    const icon = toggleStatusBtn.querySelector('.icon');
    if (value === 'true') {
        icon.classList.remove('bi-square', 'bi-x-square-fill');
        icon.classList.add('bi-check-square-fill');
        icon.style.color = 'green';
    } else {
        icon.classList.remove('bi-check-square-fill');
        icon.classList.add('bi-square');
        icon.style.color = 'initial';
    }
}

// Function to duplicate an answer item
function duplicateAnswer(answerItem) {
    const clone = answerItem.cloneNode(true);
    answersContainer.appendChild(clone);
    initializeAnswerButtons(clone); // Initialize the new cloned element
}

// Initialize event listeners for the initial answer item
document.querySelectorAll('.answer-item').forEach(initializeAnswerButtons);

// Function to add answer controls to a given container for the Edit Question
function addEditAnswerControls(container) {
    const answerItem = document.createElement('div');
    answerItem.classList.add('answer-item', 'd-flex', 'align-items-center', 'mb-2');

    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'form-control';
    input.name = 'edit_answer[]';
    input.placeholder = 'Enter Answer';
    input.required = true;

    // Create the hidden input to store the correct/incorrect state
    const checkbox = document.createElement('input');
    checkbox.type = 'hidden';
    checkbox.name = 'edit_answer_checkbox[]';
    checkbox.classList.add('answer-checkbox');
    checkbox.value = 'false'; // Default value

    // Create the button to toggle the checkbox state
    const toggleButton = document.createElement('button');
    toggleButton.type = 'button';
    toggleButton.className = 'toggle-status btn btn-sm';
    const icon = document.createElement('i');
    icon.className = 'bi bi-x-square-fill'; // Default to "incorrect" state
    icon.style.color = 'red';
    toggleButton.appendChild(icon);

    // Add event listener to toggle between "correct" and "incorrect"
    toggleButton.addEventListener('click', () => {
        if (checkbox.value === 'false') {
            checkbox.value = 'true';
            icon.className = 'bi bi-check-square-fill';
            icon.style.color = 'green';
        } else {
            checkbox.value = 'false';
            icon.className = 'bi bi-x-square-fill';
            icon.style.color = 'red';
        }
    });

    // Button to add another answer
    const addButton = document.createElement('button');
    addButton.type = 'button';
    addButton.className = 'add-answer btn btn-sm';
    const addIcon = document.createElement('i');
    addIcon.className = 'bi bi-plus';
    addButton.appendChild(addIcon);

    addButton.addEventListener('click', () => {
        addEditAnswerControls(container);
    });

    // Button to delete the answer
    const deleteButton = document.createElement('button');
    deleteButton.type = 'button';
    deleteButton.className = 'delete-answer btn btn-sm';
    const deleteIcon = document.createElement('i');
    deleteIcon.className = 'bi bi-trash';
    deleteButton.appendChild(deleteIcon);

    deleteButton.addEventListener('click', () => {
        answerItem.remove();
    });

    // Button to move the answer up
    const moveUpButton = document.createElement('button');
    moveUpButton.type = 'button';
    moveUpButton.className = 'move-up btn btn-sm';
    const upIcon = document.createElement('i');
    upIcon.className = 'bi bi-arrow-up';
    moveUpButton.appendChild(upIcon);

    moveUpButton.addEventListener('click', () => {
        const previous = answerItem.previousElementSibling;
        if (previous) {
            container.insertBefore(answerItem, previous);
        }
    });

    // Button to move the answer down
    const moveDownButton = document.createElement('button');
    moveDownButton.type = 'button';
    moveDownButton.className = 'move-down btn btn-sm';
    const downIcon = document.createElement('i');
    downIcon.className = 'bi bi-arrow-down';
    moveDownButton.appendChild(downIcon);

    moveDownButton.addEventListener('click', () => {
        const next = answerItem.nextElementSibling;
        if (next) {
            container.insertBefore(next, answerItem);
        }
    });

    // Create a container for the buttons
    const buttonsContainer = document.createElement('div');
    buttonsContainer.classList.add('answer-buttons', 'ms-2');
    buttonsContainer.appendChild(toggleButton);
    buttonsContainer.appendChild(addButton);
    buttonsContainer.appendChild(deleteButton);
    buttonsContainer.appendChild(moveUpButton);
    buttonsContainer.appendChild(moveDownButton);

    // Add the input, hidden checkbox, and buttons to the answer item
    answerItem.appendChild(input);
    answerItem.appendChild(checkbox);
    answerItem.appendChild(buttonsContainer);
    container.appendChild(answerItem);
}



function initializeEditAnswers(answers, trueAnswers) {
    const container = document.getElementById('editAnswersContainer');
    container.innerHTML = ''; // Clear previous answers
    trueAnswers = trueAnswers ? trueAnswers.split(',') : [];

    answers.forEach((answer, index) => {
        addEditAnswerControls(container);
        const answerItem = container.lastChild;
        const textInput = answerItem.querySelector('input[type="text"]');
        const checkbox = answerItem.querySelector('.answer-checkbox');
        const icon = answerItem.querySelector('.toggle-status i');

        // Set the text for the answer
        if (textInput) {
            textInput.value = answer;
        }

        // Set the checkbox state
        if (checkbox) {
            checkbox.value = trueAnswers.includes(String(index + 1)) ? 'true' : 'false';
        }

        // Set the icon state based on the checkbox value
        if (icon) {
            if (trueAnswers.includes(String(index + 1))) {
                icon.className = 'bi bi-check-square-fill';
                icon.style.color = 'green';
            } else {
                icon.className = 'bi bi-x-square-fill';
                icon.style.color = 'red';
            }
        }
    });
}



document.querySelectorAll('.edit-question').forEach(button => {
    button.addEventListener('click', function () {
        const questionItem = this.closest('.question-item');
        const questionId = questionItem.getAttribute('data-question-id');

        // Set the question ID in the hidden input field
        document.getElementById('editQuestionId').value = questionId;

        // Fetch question details from the server
        fetch(`get_question.php?id=${questionId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Set the question title
                    document.getElementById('editQuestionTitle').value = data.question.question_title;

                    // Set the media preview if available
                    const mediaPreview = document.getElementById('editMediaPreview');
                    mediaPreview.innerHTML = ''; // Clear previous media
                    if (data.question.image) {
                        const img = document.createElement('img');
                        img.src = `${data.question.image}`;
                        img.style.maxWidth = '120px';
                        img.style.maxHeight = '100px';
                        img.style.borderRadius = '8px';
                        mediaPreview.appendChild(img);
                    } else if (data.question.video) {
                        const mediaElement = document.createElement(/\.(mp4|avi|mov)$/i.test(data.question.video) ? 'video' : 'audio');
                        mediaElement.src = `${data.question.video}`;
                        mediaElement.controls = true;
                        mediaElement.style.maxWidth = '120px';
                        mediaElement.style.maxHeight = '100px';
                        mediaPreview.appendChild(mediaElement);
                    }

                    // Set the answers
                    const answers = [];
                    for (let i = 1; i <= 10; i++) {
                        if (data.question[`answer_${i}`]) {
                            answers.push(data.question[`answer_${i}`]);
                        }
                    }
                    initializeEditAnswers(answers, data.question.true_answer);

                    // Set the ratio (correct/wrong)
                    if (data.question.ratio === 'correct') {
                        document.getElementById('editConfirmCorrect').checked = true;
                    } else {
                        document.getElementById('editConfirmWrong').checked = true;
                    }

                    // Display the edit container
                    document.querySelector('.edit-question-container').style.display = 'block';
                } else {
                    alert('Failed to load question data.');
                }
            })
            .catch(error => console.error('Error:', error));
    });
});


// Function to update media preview when a new file is selected
document.getElementById('editMedia').addEventListener('change', function () {
    const mediaPreview = document.getElementById('editMediaPreview');
    const file = this.files[0];

    // Clear the previous preview
    mediaPreview.innerHTML = '';

    if (file) {
        const fileURL = URL.createObjectURL(file);
        let mediaElement;

        // Check the file type and create the corresponding element
        if (file.type.startsWith('image/')) {
            mediaElement = document.createElement('img');
            mediaElement.style.maxWidth = '100px'; // Small preview size
            mediaElement.style.maxHeight = '80px';
            mediaElement.style.borderRadius = '8px';
            mediaElement.src = fileURL;
        } else if (file.type.startsWith('video/')) {
            mediaElement = document.createElement('video');
            mediaElement.style.maxWidth = '100px'; // Small preview size
            mediaElement.style.maxHeight = '80px';
            mediaElement.controls = true;
            mediaElement.src = fileURL;
        } else if (file.type.startsWith('audio/')) {
            mediaElement = document.createElement('audio');
            mediaElement.controls = true;
            mediaElement.style.width = '100px'; // Small preview size
            mediaElement.src = fileURL;
        } else {
            const message = document.createElement('p');
            message.textContent = 'File type not supported for preview';
            mediaElement = message;
        }

        // Add the media element to the preview container
        mediaPreview.appendChild(mediaElement);
    }
});

// Function to handle removing the existing media preview
document.querySelector('.remove-media-preview').addEventListener('click', function () {
    const mediaPreview = document.getElementById('editMediaPreview');
    mediaPreview.innerHTML = ''; // Clear the preview
    mediaToDelete = true; // Set the flag to delete the media
});

// Clear the file input and preview when the main "x" button is clicked
document.querySelector('.remove-media').addEventListener('click', function () {
    const editMediaInput = document.getElementById('editMedia');
    const mediaPreview = document.getElementById('editMediaPreview');

    editMediaInput.value = ''; // Clear the file input
    mediaPreview.innerHTML = ''; // Clear the preview
    mediaToDelete = true; // Set the flag to delete the media
});

function loadExistingMedia(mediaUrl) {
    const mediaPreview = document.getElementById('editMediaPreview');
    const removeMediaBtn = document.querySelector('.remove-media-preview');
    mediaPreview.innerHTML = ''; // Clear previous content

    if (mediaUrl) {
        let mediaElement;

        if (mediaUrl.match(/\.(jpg|jpeg|png|gif)$/i)) {
            mediaElement = document.createElement('img');
            mediaElement.style.maxWidth = '100px'; // Small preview size
            mediaElement.style.maxHeight = '80px';
            mediaElement.style.borderRadius = '8px';
            mediaElement.src = mediaUrl;
        } else if (mediaUrl.match(/\.(mp4|avi|mov)$/i)) {
            mediaElement = document.createElement('video');
            mediaElement.style.maxWidth = '100px'; // Small preview size
            mediaElement.style.maxHeight = '80px';
            mediaElement.controls = true;
            mediaElement.src = mediaUrl;
        } else if (mediaUrl.match(/\.(mp3|wav)$/i)) {
            mediaElement = document.createElement('audio');
            mediaElement.controls = true;
            mediaElement.style.width = '100px'; // Small preview size
            mediaElement.src = mediaUrl;
        }

        if (mediaElement) {
            mediaPreview.appendChild(mediaElement);
            removeMediaBtn.style.display = 'block';
        }
    }
}

// File input and remove button elements
const editMediaInput = document.getElementById('editMedia');
const removeMediaBtn = document.querySelector('.remove-media');

// Show the "x" button when a file is selected
editMediaInput.addEventListener('change', function () {
    if (this.files.length > 0) {
        removeMediaBtn.style.display = 'block';
    } else {
        removeMediaBtn.style.display = 'none';
    }
});

// Clear the file input and hide the "x" button when clicked
removeMediaBtn.addEventListener('click', function () {
    editMediaInput.value = ''; // Clear the input
    this.style.display = 'none'; // Hide the "x" button
});

</script>




<!-- Bootstrap Icons -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Start a separate session for the quiz
session_name('quiz_session');
session_start();
include 'db_connect.php';

// Get the quiz ID from the URL
$quiz_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Check if the user is logged in for the quiz session
if (!isset($_SESSION['quiz_user_id'])) {
    // Redirect to the quiz details page if not logged in
    header("Location: https://kipeducationlessons.am/quiz_details.php?id=" . $quiz_id);
    exit();
}

// Fetch the current user ID from the session
$user_id = $_SESSION['quiz_user_id'];

// Check if this user has already completed the quiz by checking the end_user_id column
$completionCheckQuery = "SELECT COUNT(*) FROM quizzes WHERE id = ? AND FIND_IN_SET(?, end_user_id)";
$stmt = $conn->prepare($completionCheckQuery);
if (!$stmt) {
    die('Prepare failed: ' . $conn->error);
}
$stmt->bind_param("ii", $quiz_id, $user_id);
$stmt->execute();
$stmt->bind_result($completionCount);
$stmt->fetch();
$stmt->close();

if ($completionCount > 0) {
    // If the user has already completed the quiz, redirect to a "completed" page or show a message
    header("Location: https://kipeducationlessons.am/quizzes.php?id=" . $quiz_id);
    exit();
}

// Fetch quiz details from the database
$quizTitle = '';
$quizSubtitle = '';
$timeInSeconds = 0;

$quizQuery = "SELECT title, subtitle, time_in_seconds FROM quizzes WHERE id = ?";
$stmt = $conn->prepare($quizQuery);
if (!$stmt) {
    die('Prepare failed: ' . $conn->error);
}
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$stmt->bind_result($quizTitle, $quizSubtitle, $timeInSeconds);
$stmt->fetch();
$stmt->close();

// Fetch questions and correct answers for the current quiz from questions_new table
$questions = [];
$correctAnswers = [];
$questionQuery = "SELECT id, question_title, type, media, answers, true_answer FROM questions_new WHERE quiz_id = ?";
$stmt = $conn->prepare($questionQuery);
if (!$stmt) {
    die('Prepare failed: ' . $conn->error);
}
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
    $decodedAnswer = json_decode($row['true_answer'], true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $correctAnswers[$row['id']] = $decodedAnswer;
    } else {
        error_log('JSON decoding error for question ID: ' . $row['id'] . ' - ' . json_last_error_msg());
        $correctAnswers[$row['id']] = [];
    }
}

$stmt->close();

// Fetch the current user's points
$currentPoints = 0;
$pointsQuery = "SELECT points FROM children WHERE id = ?";
$stmt = $conn->prepare($pointsQuery);
if (!$stmt) {
    die('Prepare failed: ' . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($currentPoints);
$stmt->fetch();
$stmt->close();

// Remove point updating logic from here, as it is handled via AJAX in update_point.php

// Include the separated header
include 'headerchild.php';
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Game</title>
    <link rel="icon" href="resource/img/favicon.png" type="image/png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
/* Page styles */
body {
    background-color: #f5f5f5;
    color: #333333;
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    padding-bottom: 80px;
}
.main-content {
    text-align: center;
    padding-top: 60px;
    margin: 0 auto;
    max-width: 800px;
}
.quiz-title {
    font-size: 28px;
    font-weight: bold;
    color: #333333;
    margin-bottom: 10px;
}
.quiz-subtitle {
    font-size: 20px;
    color: #555555;
    margin-bottom: 30px;
}
.question {
    margin-bottom: 30px;
    padding: 20px;
    background-color: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    color: #333333;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
    text-align: left;
}
.question-title {
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 20px;
}
.answers {
    list-style: none;
    padding: 0;
    margin-top: 10px;
}
.answers li {
    display: flex;
    align-items: center;
    margin-bottom: 12px;
    cursor: pointer;
}

/* Custom Checkbox Style for Multiple Checks (Square) */
.checkbox-custom {
    width: 20px;
    height: 20px;
    border: 2px solid #ccc; /* Default gray border color */
    cursor: pointer;
    margin-right: 10px;
    position: relative;
    transition: all 0.3s;
    background-color: white; /* White background by default */
}

/* Checkbox checked style */
.checkbox-custom.checked {
    background-color: #28a745; /* Fully filled green background when checked */
    border-color: #28a745; /* Remove border color to match background */
}

/* Checkmark for the checkbox */
/* Checkmark for the checkbox */
.checkbox-custom.checked::after {
    content: '✔'; /* Checkmark symbol */
    color: white; /* White color for the checkmark */
    font-size: 16px; /* Size of the checkmark */
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%); /* Center the checkmark */
}


/* Custom Radio Button Style (Circle) */
.radio-custom {
    width: 20px;
    height: 20px;
    border: 2px solid #ccc; /* Default gray border color for the radio button */
    border-radius: 50%; /* Make it round */
    cursor: pointer;
    margin-right: 10px;
    position: relative;
    transition: all 0.3s;
}

.radio-custom.checked {
    background-color: #007bff; /* Blue background when selected */
    border: 2px solid white; /* White border around the blue circle */
}

/* Inner dot for the radio button */
.radio-custom.checked::after {
    content: ''; /* This will create the inner circle */
    width: 14px; /* Inner circle size, slightly larger for better visibility */
    height: 14px; /* Inner circle size */
    border-radius: 50%; /* Make it round */
    background-color: #007bff; /* Inner circle color */
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%); /* Center the inner circle */
    border: 2px solid white; /* Add a blue border around the inner circle */
}

.error-message {
    color: red;
    font-size: 14px;
    display: none;
}
.general-error {
    color: red;
    font-size: 16px;
    display: none;
    margin-top: 20px;
}
.media-content {
    margin-top: 20px;
    text-align: center;
}
.play-btn {
    background-color: #4b6cb7;
    border: none;
    color: #ffffff;
    padding: 15px;
    font-size: 24px;
    border-radius: 50%;
    cursor: pointer;
    transition: background-color 0.3s, transform 0.3s;
    outline: none;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}
.play-btn:hover {
    background-color: #182848;
}
.play-btn:active {
    transform: scale(0.9);
}
audio, video {
    width: 100%;
    max-width: 280px;
    margin-top: 20px;
    border-radius: 8px;
}
.confirm-btn {
    margin-top: 40px;
    padding: 12px 25px;
    background-color: #4b6cb7;
    border: none;
    border-radius: 25px;
    color: #ffffff;
    font-size: 18px;
    cursor: pointer;
    transition: background-color 0.3s, transform 0.3s;
    outline: none;
}
.confirm-btn:hover {
    background-color: #182848;
}
.confirm-btn:active {
    transform: scale(0.95);
}
/* Countdown timer styles */
.countdown-timer {
    position: fixed;
    top: 20px;
    right: 20px;
    background-color: #ff9900; /* Softer orange for a friendly look */
    color: #fff; /* White text for good contrast */
    padding: 15px 30px;
    border-radius: 50%; /* Circular shape */
    font-size: 24px;
    font-weight: bold;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Soft shadow for subtle depth */
    border: 3px solid #cc7700; /* Slightly darker orange border */
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Animation to give the timer a pulsing effect */
@keyframes pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
    100% {
        transform: scale(1);
    }
}
/* Popup Styles */
.popup {
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
}
.popup-content {
    background-color: white;
    padding: 40px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    max-width: 500px;
    width: 100%;
}
.close-btn {
    position: absolute;
    top: 10px;
    right: 20px;
    font-size: 28px;
    cursor: pointer;
}
.result-icon {
    font-size: 50px;
    margin: 10px;
}
.correct-icon {
    color: green;
}
.wrong-icon {
    color: red;
}

.result-sections {
    display: flex;
    justify-content: space-between;
    gap: 20px;
}

.correct-section, .incorrect-section {
    width: 45%;
    padding: 15px;
    background-color: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.score-container {
    background-color: #ffffff;
    padding: 10px;
    margin-top: 10px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

h2 {
    margin-top: 20px;
    font-size: 18px; /* Smaller font for "Your Final Score" */
    font-weight: bold;
    color: #333;
    text-align: center;
}

h2 + h2 {
    font-size: 40px; /* Larger font for the score numbers */
    margin-top: 0;
}

ul {
    list-style: none;
    padding-left: 0;
}

ul li {
    margin-bottom: 10px;
    line-height: 1.5;
}
.answer-feedback {
    margin-top: 10px;
    font-size: 14px;
}

.correct-feedback {
    color: green;
    font-weight: bold;
}

.wrong-feedback {
    color: red;
    font-weight: bold;
}

.answers li {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}

.answers li span {
    font-size: 18px; /* Icon size */
}
.answer-select {
    padding: 10px;
    border-radius: 5px;
    border: 2px solid #ccc; /* Default border color */
    width: 100%; /* Full width */
    max-width: 600px; /* Optional: limit the width */
    margin: 10px 0; /* Margin for spacing */
    cursor: pointer; /* Pointer cursor */
    transition: border-color 0.3s; /* Smooth transition for border color */
}

/* Focus style for the select dropdown */
.answer-select:focus {
    border-color: #007bff; /* Change border color on focus */
    outline: none; /* Remove default outline */
}

.question-card {
            padding: 20px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            text-align: left; /* Align content to left */
        }
        .question-title {
            text-align: left; /* Align text to the left */
            margin: 0; /* Remove margin to keep it compact */
            padding: 10px 0; /* Optional: add some padding for spacing */
            font-size: 20px;
            font-weight: bold;
        }



/* Error message style */
.error-message {
    color: red; /* Error color */
    font-size: 14px; /* Size of the error message */
    display: none; /* Initially hidden */
}
.image-select-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 10px; /* Spacing between images */
}

.image-option {
    cursor: pointer;
    width: 100px; /* Adjust the size of the images */
    height: 100px;
}

.image-thumbnail {
    width: 100%;
    height: 100%;
    object-fit: cover; /* Ensures the image maintains aspect ratio */
    border: 2px solid transparent;
}

.image-option.selected .image-thumbnail {
    border-color: #007bff; /* Highlight selected image */
}

    </style>
</head>
<body>
    
     <!-- Countdown Timer -->
    <div class="countdown-timer" id="countdown">00:00</div>

<!-- Main Content -->
<div class="main-content">
    <?php if (!empty($quizTitle)): ?>
        <div class="quiz-title"><?= htmlspecialchars($quizTitle, ENT_QUOTES, 'UTF-8') ?></div>
        <div class="quiz-subtitle"><?= htmlspecialchars($quizSubtitle, ENT_QUOTES, 'UTF-8') ?></div>
    <?php else: ?>
        <p>Quiz details not found.</p>
    <?php endif; ?>

    <!-- Display questions -->
    <?php if (!empty($questions)): ?>
        <?php 
        $questionNumber = 1; // Initialize the question counter
        foreach ($questions as $question): ?>
            <div class="question-card" data-id="<?= $question['id'] ?>" data-type="<?= $question['type'] ?>"> <!-- Assign data attributes -->
                <div class="question-title"><?= $questionNumber++ . '. ' . htmlspecialchars($question['question_title'], ENT_QUOTES, 'UTF-8') ?></div> <!-- Numbering added -->
                
                <?php if (!empty($question['text_answer'])): ?>
                    <p><?= htmlspecialchars($question['text_answer'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>

                <!-- Audio content handling -->
                <?php if (!empty($question['audio'])): ?>
                    <div class="media-content">
                        <button class="play-btn" onclick="toggleAudio(this, 'audio-<?= $question['id'] ?>')">
                            <i class="fas fa-play"></i>
                        </button>
                        <audio id="audio-<?= $question['id'] ?>" style="display:none;">
                            <source src="<?= htmlspecialchars($question['audio'], ENT_QUOTES, 'UTF-8') ?>" type="audio/mpeg">
                            Your browser does not support the audio element.
                        </audio>
                    </div>
                <?php endif; ?>

                <!-- Video content handling -->
                <?php if (!empty($question['video']) && preg_match('/\.(mp4|avi|mov)$/i', $question['video'])): ?>
                    <div class="media-content">
                        <video controls>
                            <source src="<?= htmlspecialchars($question['video'], ENT_QUOTES, 'UTF-8') ?>" type="video/mp4">
                            Your browser does not support the video element.
                        </video>
                    </div>
                <?php endif; ?>

                <!-- Answer options -->
                <ul class="answers">
                    <?php 
                    // Get answers and split them into an array
                    $answers = json_decode($question['answers'], true); // Assuming answers are stored as JSON

                    // If the question type is 'select', use a select dropdown
                    if ($question['type'] == 'select'): ?>
                        <li>
                            <select class="answer-select" onchange="selectAnswer(this, <?= $question['id'] ?>)">
                                <option value="" disabled selected>Select an answer</option> <!-- Default option -->
                                <?php foreach ($answers as $answer): ?>
                                    <option value="<?= htmlspecialchars($answer, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($answer, ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </li>
                    <?php elseif ($question['type'] == 'image_select'): // New block for image selection ?>
                        <div class="image-select-grid">
                            <?php foreach ($answers as $imageSrc): ?>
                                <div class="image-option" onclick="selectImageAnswer(this, <?= $question['id'] ?>)">
                                    <img src="<?= htmlspecialchars($imageSrc, ENT_QUOTES, 'UTF-8') ?>" alt="Image option" class="image-thumbnail">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: // For checkboxes and radio buttons ?>
                        <?php foreach ($answers as $answer): ?>
                            <li onclick="toggleCheckbox(this)">
                                <?php if ($question['type'] == 'check'): ?>
                                    <input type="checkbox" class="answer-checkbox" value="<?= htmlspecialchars($answer, ENT_QUOTES, 'UTF-8') ?>" style="display:none;">
                                    <span class="checkbox-custom"></span> <!-- Custom styled checkbox -->
                                    <?= htmlspecialchars($answer, ENT_QUOTES, 'UTF-8') ?>
                                <?php elseif ($question['type'] == 'radio'): ?>
                                    <input type="radio" class="answer-radio" name="question-<?= $question['id'] ?>" value="<?= htmlspecialchars($answer, ENT_QUOTES, 'UTF-8') ?>" style="display:none;">
                                    <span class="radio-custom"></span> <!-- Custom styled radio -->
                                    <?= htmlspecialchars($answer, ENT_QUOTES, 'UTF-8') ?>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>

                <div class="error-message" style="display:none;">Please select an answer for this question.</div>
            </div> <!-- End of question-card -->
        <?php endforeach; ?>
    <?php else: ?>
        <p>No questions available for this quiz.</p>
    <?php endif; ?>

    <!-- General error message for unanswered questions -->
    <div class="general-error" style="display:none;">Please answer all questions before confirming.</div>

    <!-- Confirm Button -->
    <button class="confirm-btn" onclick="checkAnswers()">Confirm</button>
</div>


<!-- Popup Window -->
<div id="resultPopup" class="popup" style="display:none;">
    <div class="popup-content">
        <span class="close-btn" onclick="closePopup()">&times;</span>
        <h2>Quiz Results</h2>
        <div id="resultContent">
            <!-- Result details will be dynamically injected here -->
        </div>
    </div>
</div>

    <!-- Footer -->
    <?php include 'footer.php'; ?>
<script>
    // Correct answers from PHP passed to JavaScript
const correctAnswers = <?php echo json_encode($correctAnswers, JSON_UNESCAPED_SLASHES); ?>;
</script>

<script>
    let timerInterval; // Global variable to store the interval
    let timeSpent = 0; // Variable to track time spent

    function toggleAudio(button, audioId) {
        const audio = document.getElementById(audioId);
        if (audio.paused) {
            audio.play();
            button.innerHTML = '<i class="fas fa-pause"></i>';
            audio.onended = function() {
                button.innerHTML = '<i class="fas fa-play"></i>';
            };
        } else {
            audio.pause();
            button.innerHTML = '<i class="fas fa-play"></i>';
        }
    }

    function selectAnswer(answerElement, questionId) {
        const questionContainer = document.getElementById(questionId);
        const allAnswers = questionContainer.querySelectorAll('.answers li');
        allAnswers.forEach(answer => {
            answer.querySelector('.checkbox-custom').classList.remove('checked');
        });
        answerElement.querySelector('.checkbox-custom').classList.add('checked');
        questionContainer.querySelector('.error-message').style.display = 'none';
    }
function selectImageAnswer(element, questionId) {
    // Deselect previously selected images
    const allOptions = document.querySelectorAll(`.image-select-grid div`);
    allOptions.forEach(option => option.classList.remove('selected'));

    // Mark the clicked image as selected
    element.classList.add('selected');

    // Get the correct answers for this question
    const correctAnswer = correctAnswers[questionId]; // Example: ["uploads/images/image1.jpg"]

    // Get the selected image source and add the missing 'uploads/' part
    let selectedImage = element.querySelector('img').src.split('/').slice(-2).join('/');
    selectedImage = 'uploads/' + selectedImage;

    console.log('Selected image for question ' + questionId + ': ' + selectedImage);
    console.log('Correct answer for question ' + questionId + ': ' + correctAnswer);

    // Check if the selected image matches the correct answer
    if (correctAnswer.includes(selectedImage)) {
        console.log('Correct image selected.');
    } else {
        console.log('Incorrect image selected.');
    }
}



function toggleCheckbox(answerElement) {
    const checkbox = answerElement.querySelector('.answer-checkbox');
    const radio = answerElement.querySelector('.answer-radio');

    if (checkbox) {
        // For checkboxes
        checkbox.checked = !checkbox.checked; // Toggle the checkbox state
        answerElement.querySelector('.checkbox-custom').classList.toggle('checked', checkbox.checked); // Update the UI for the checkbox
    } else if (radio) {
        // For radio buttons, select only this radio
        const radioGroup = document.querySelectorAll(`.answer-radio[name="${radio.name}"]`);
        radioGroup.forEach(r => {
            r.checked = false; // Uncheck all other radios in the group
            r.nextElementSibling.classList.remove('checked'); // Remove checked style from UI
        });
        radio.checked = true; // Check the clicked radio
        answerElement.querySelector('.radio-custom').classList.add('checked'); // Update the UI for the selected radio
    }
}

function selectAnswer(selectElement, questionId) {
    const selectedValue = selectElement.value;
    // Handle the selected answer logic here if needed, e.g., mark the answer as selected
    console.log(`Selected answer for question ${questionId}: ${selectedValue}`);
}

function checkAnswers() {
    let allQuestionsAnswered = true;
    let correctCount = 0;
    let wrongCount = 0;
    let totalQuestions = document.querySelectorAll('.question-card').length;
    const questions = document.querySelectorAll('.question-card');

    questions.forEach(question => {
        const questionId = question.dataset.id;
        const questionType = question.dataset.type;
        let selectedAnswers = [];
        let correctAnswer = correctAnswers[questionId]; // Correct answer from server

        // Handle different question types
        if (questionType === 'check') {
            selectedAnswers = Array.from(question.querySelectorAll('.answer-checkbox:checked')).map(input => input.value);
        } else if (questionType === 'radio') {
            const selectedRadio = question.querySelector('.answer-radio:checked');
            if (selectedRadio) {
                selectedAnswers = [selectedRadio.value];
            }
        } else if (questionType === 'select') {
            const selectedOption = question.querySelector('.answer-select').value;
            if (selectedOption) {
                selectedAnswers = [selectedOption];
            }
        } else if (questionType === 'image_select') {
            const selectedImage = question.querySelector('.image-option.selected img');
            if (selectedImage) {
                selectedAnswers = [selectedImage.src];
            }
        }

        const errorMessage = question.querySelector('.error-message');
        if (selectedAnswers.length === 0) {
            allQuestionsAnswered = false;
            errorMessage.style.display = 'block'; // Show error if no answer selected
        } else {
            errorMessage.style.display = 'none'; // Hide error if answer selected

            // Check if selected answers match the correct answers
            const isCorrect = selectedAnswers.every(answer => correctAnswer.includes(answer)) &&
                selectedAnswers.length === correctAnswer.length;
            
            if (isCorrect) {
                correctCount++;
            } else {
                wrongCount++;
            }
        }
    });

    const generalError = document.querySelector('.general-error');
    if (allQuestionsAnswered) {
        generalError.style.display = 'none';
        stopCountdown(); // Stop the countdown timer
        hideCountdown(); // Hide the countdown after quiz ends

        // Initialize finalScore based on correct answers
        let finalScore = (correctCount / totalQuestions) * 10;
        finalScore = isNaN(finalScore) ? 0 : finalScore; // Handle NaN case

        // Calculate the time score
        const totalTime = <?= json_encode($timeInSeconds); ?>; // Time limit from PHP
        const timeScore = calculateTimeScore(timeSpent, totalTime);

        // Add the time score to the final score
        const totalFinalScore = parseFloat(finalScore) + parseFloat(timeScore);

        // Show result popup
        showResultPopup(correctCount, wrongCount, finalScore, timeScore, totalFinalScore);

        // Update user points
        updateUserPoints(totalFinalScore, <?= json_encode($quiz_id); ?>);

        // Call function to display feedback for correct and incorrect answers
        displayAnswerFeedback();
    } else {
        generalError.style.display = 'block'; // Show error if not all questions answered
    }
}


function displayBottomButtons() {
    // Create a container for the buttons
    const buttonContainer = document.createElement('div');
    buttonContainer.style.textAlign = 'center';
    buttonContainer.style.marginTop = '30px';

    // Create "View Result" button
    const viewResultButton = document.createElement('button');
    viewResultButton.classList.add('confirm-btn'); // Apply the same style as other buttons
    viewResultButton.innerHTML = "View Result";
    viewResultButton.onclick = function() {
        document.getElementById('resultPopup').style.display = 'flex'; // Re-open the result popup
    };

    // Create "Go to Main Page" button
    const mainPageButton = document.createElement('button');
    mainPageButton.classList.add('confirm-btn'); // Apply the same style as other buttons
    mainPageButton.innerHTML = "Go to Main Page";
    mainPageButton.style.marginLeft = '10px'; // Add some space between buttons
    mainPageButton.onclick = function() {
        window.location.href = "quizzes.php"; // Redirect to quizzes.php
    };

    // Append both buttons to the container
    buttonContainer.appendChild(viewResultButton);
    buttonContainer.appendChild(mainPageButton);

    // Append the button container at the end of the quiz content
    const quizContainer = document.querySelector('.main-content'); // Assuming this is the quiz container
    quizContainer.appendChild(buttonContainer);
}

    function hideCountdown() {
        document.getElementById('countdown').style.display = 'none'; // Hide the countdown after quiz completion
    }

function calculateTimeScore(timeSpent, totalTime) {
    const maxScore = 10; // Maximum score for time
    const timeRemaining = totalTime - timeSpent; // Time left
    if (timeRemaining <= 0) {
        return 0; // No points if time exceeds the limit
    }

    const timePercentage = timeRemaining / totalTime; // Percentage of time remaining
    const timeScore = timePercentage * maxScore; // Scale the time score to maxScore
    return timeScore.toFixed(1); // Return time score rounded to one decimal
}

function showResultPopup(correct, wrong, questionScore, timeScore, finalScore) {
    const resultContent = document.getElementById('resultContent');
    const timeElapsed = formatTimeSpent(timeSpent); // Get the formatted time

    // Ensure that timeScore is a number before using toFixed()
    timeScore = isNaN(timeScore) ? 0 : parseFloat(timeScore); 
    questionScore = isNaN(questionScore) ? 0 : parseFloat(questionScore); 

    // Calculate the average score (golden mean) of questionScore and timeScore
    const totalFinalScore = ((questionScore + timeScore) / 2).toFixed(1);

    // Create icon elements for correct and incorrect answers
    const correctIcon = '<span style="color: green; font-size: 24px;">✔</span>';
    const wrongIcon = '<span style="color: red; font-size: 24px;">❌</span>';
    const crownIcon = '<span style="color: gold; font-size: 30px;">👑</span>';

    // Show scores and final result in popup
    resultContent.innerHTML = `
        <p>You completed the quiz in <strong>${timeElapsed}</strong>.</p>

        <!-- Correct and wrong answers with icons -->
        <div style="display: flex; justify-content: center; align-items: center; margin: 20px 0;">
            <div style="margin-right: 20px;">${correctIcon} ${correct}</div>
            <div>${wrongIcon} ${wrong}</div>
        </div>

        <!-- Score for questions and time -->
        <div style="text-align: center; margin-bottom: 20px;">
            <p>Your score for questions: <strong>${questionScore.toFixed(1)}</strong></p>
            <p>Your score for time: <strong>${timeScore.toFixed(1)}</strong></p>
        </div>

        <!-- Final score with crown icon and different font sizes -->
        <h2 style="text-align: center; font-size: 18px; font-weight: bold; margin-top: 20px;">
            ${crownIcon} Your Final Score:
        </h2>
        <h2 style="text-align: center; font-size: 40px; font-weight: bold; margin-top: 0;">
            <strong>${totalFinalScore} / 10</strong>
        </h2>
    `;

    // Display the popup
    document.getElementById('resultPopup').style.display = 'flex';

    // Hide the Confirm button when the popup is displayed
    const confirmButton = document.querySelector('.confirm-btn');
    if (confirmButton) {
        confirmButton.style.display = 'none';
    }

    // Add "Close" button to the popup
    const closeButton = document.createElement('button');
    closeButton.classList.add('confirm-btn'); // Apply the same style as other buttons
    closeButton.innerHTML = "Close";
    closeButton.onclick = function() {
        updateEndUserId(); // Refresh the data by updating end_user_id
        closePopup(); // Close the popup
    };

    // Append the "Close" button to the popup content
    resultContent.parentElement.appendChild(closeButton);
}



// Function to update end_user_id in the database
function updateEndUserId() {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "update_end_user_id.php", true); // Assuming you have an update_end_user_id.php script
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.status !== "success") {
                alert(response.message); // Show error message if needed
            }
        }
    };

    const userId = <?= json_encode($_SESSION['quiz_user_id']); ?>; // Get the current user ID
    const quizId = <?= json_encode($quiz_id); ?>; // Get the current quiz ID

    xhr.send("quiz_id=" + quizId + "&user_id=" + userId); // Send quiz ID and user ID to the server
}

function closePopup() {
    document.getElementById('resultPopup').style.display = 'none';
}


    function formatTimeSpent(seconds) {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;
        const formattedMinutes = minutes < 10 ? '0' + minutes : minutes;
        const formattedSeconds = remainingSeconds < 10 ? '0' + remainingSeconds : remainingSeconds;
        return `${formattedMinutes}:${formattedSeconds}`;
    }

function startCountdown(duration) {
    let timer = duration;
    console.log('Starting countdown with duration:', duration); // Check if duration is correct
    timerInterval = setInterval(function () {
        const minutes = parseInt(timer / 60, 10);
        const seconds = parseInt(timer % 60, 10);

        const formattedMinutes = minutes < 10 ? "0" + minutes : minutes;
        const formattedSeconds = seconds < 10 ? "0" + seconds : seconds;

        document.getElementById('countdown').textContent = formattedMinutes + ":" + formattedSeconds;
        timeSpent++; // Track the time spent on the quiz

        if (--timer < 0) {
            clearInterval(timerInterval);
            document.getElementById('countdown').textContent = "Time's up!";
            // Automatically show result when time's up
            checkAnswers();
        }
    }, 1000);
}

window.onload = function () {
    const timeInSeconds = <?= json_encode($timeInSeconds) ?>; // Ensure this value is correctly passed from PHP
    console.log('Time in seconds from PHP:', timeInSeconds); // Debugging: Check if the value is passed correctly
    startCountdown(timeInSeconds); // Start countdown with the correct duration
};


    function stopCountdown() {
        clearInterval(timerInterval); // Stop the countdown
    }

function displayAnswerFeedback() {
    const questions = document.querySelectorAll('.question-card');

    questions.forEach(question => {
        const questionId = question.dataset.id; // Get question ID
        const questionType = question.dataset.type; // Get question type
        const correctAnswer = correctAnswers[questionId]; // Correct answers from server

        // Handle check and radio types
        if (questionType === 'check' || questionType === 'radio') {
            const answerElements = question.querySelectorAll('.answers li');

            // Loop through all answer elements
            answerElements.forEach(answerElement => {
                const answerText = answerElement.textContent.trim(); // Get text of each answer
                const isSelected = answerElement.querySelector('.checkbox-custom.checked') || answerElement.querySelector('.radio-custom.checked');

                // Create an icon element
                const iconElement = document.createElement('span');
                iconElement.style.marginRight = '10px';

                // Check if answer is correct or incorrect
                if (correctAnswer.includes(answerText)) {
                    iconElement.innerHTML = '✔'; // Correct answer
                    iconElement.style.color = 'green';
                } else if (isSelected) {
                    iconElement.innerHTML = '❌'; // Incorrect answer
                    iconElement.style.color = 'red';
                } else {
                    iconElement.innerHTML = ''; // No icon for unselected wrong answers
                }

                // Remove existing checkbox/radio and replace with icon
                const checkboxDiv = answerElement.querySelector('.checkbox-custom');
                const radioDiv = answerElement.querySelector('.radio-custom');
                if (checkboxDiv) {
                    checkboxDiv.replaceWith(iconElement);
                } else if (radioDiv) {
                    radioDiv.replaceWith(iconElement);
                }
            });
        }

        // Handle select type answers without showing the select field
        if (questionType === 'select') {
            const selectedOption = question.querySelector('.answer-select').value; // Get selected option
            
            if (!selectedOption) {
                console.error('No option selected for question: ', questionId);
                return; // Skip if no answer was selected
            }

            const answersData = question.querySelector('.answer-select').options; // Get all options from select

            const answerContainer = document.createElement('ul');
            answerContainer.classList.add('answers');

            // Loop through all options (answers)
            Array.from(answersData).forEach(option => {
                const answerElement = document.createElement('li');
                answerElement.style.listStyle = 'none'; // Remove bullet points

                const answerText = option.value;
                const iconElement = document.createElement('span');
                iconElement.style.marginRight = '10px';

                if (correctAnswer.includes(answerText)) {
                    iconElement.innerHTML = '✔'; // Correct answer
                    iconElement.style.color = 'green';
                } else if (answerText === selectedOption) {
                    iconElement.innerHTML = '❌'; // Incorrect answer
                    iconElement.style.color = 'red';
                } else {
                    iconElement.innerHTML = ''; // No icon for unselected wrong answers
                }

                answerElement.appendChild(iconElement);
                answerElement.append(answerText);
                answerContainer.appendChild(answerElement); // Append each answer with its icon
            });

            // Remove the select field and append the answers list
            const selectParent = question.querySelector('.answer-select').parentNode;
            selectParent.innerHTML = ''; // Clear the parent container
            selectParent.appendChild(answerContainer); // Add the custom list with answers and icons
        }
// Handle image selection questions
        if (questionType === 'image_select') {
            const imageOptions = question.querySelectorAll('.image-option');
            let selectedOptionFound = false;

            // Loop through all image options
            imageOptions.forEach(imageOption => {
                const imageElement = imageOption.querySelector('img');

                if (!imageElement) return; // Skip if no image element found

                // Check if the image source exists and create the imageSrc variable
                let imageSrc = imageElement.src ? imageElement.src.split('/').slice(-2).join('/') : null;
                if (imageSrc) {
                    imageSrc = 'uploads/' + imageSrc; // Add the 'uploads/' part to match the correct answer format
                } else {
                    console.error("Image source not found for question " + questionId);
                    return; // Skip further processing if imageSrc is not valid
                }

                const isSelected = imageOption.classList.contains('selected');

                // Create icon element for visual feedback
                const iconElement = document.createElement('div');
                iconElement.style.textAlign = 'center'; // Center the icon under the image
                iconElement.style.marginTop = '5px';

                // Check if the image path is the correct answer
                if (correctAnswer.includes(imageSrc)) {
                    // Add green border to correct image
                    imageElement.style.border = '4px solid green';
                    iconElement.innerHTML = '✔'; // Check icon for correct answer
                    iconElement.style.color = 'green';

                    // Correct answer selected
                    if (isSelected) {
                        selectedOptionFound = true;
                    }
                } else if (isSelected) {
                    // Add red border to incorrect selected image
                    imageElement.style.border = '4px solid red';
                    iconElement.innerHTML = '❌'; // False icon for incorrect answer
                    iconElement.style.color = 'red';
                    selectedOptionFound = true; // Mark that an image was selected
                } else {
                    // Reset border for unselected wrong images
                    imageElement.style.border = 'none';
                    iconElement.innerHTML = ''; // No icon for unselected wrong answers
                }

                // Append icon element under the image
                if (!imageOption.querySelector('.icon-feedback')) {
                    imageOption.appendChild(iconElement);
                    iconElement.classList.add('icon-feedback');
                }
            });

            // Create feedback for the selected image
            const feedbackElement = document.createElement('div');
            feedbackElement.classList.add('answer-feedback');

            if (selectedOptionFound) {
                if (correctAnswer.includes(imageSrc)) {
                    feedbackElement.innerHTML = `<span class="correct-feedback" style="color:green;">✔ Correct</span>`;
                } else {
                    feedbackElement.innerHTML = `<span class="wrong-feedback" style="color:red;">❌ Incorrect. Correct Image is highlighted in green.</span>`;
                }
            } else {
                feedbackElement.innerHTML = `<span class="correct-feedback" style="color:green;">✔ Correct</span>`;
            }

            // Add feedback element to the question
            question.appendChild(feedbackElement);
        }
    });
}
function updateUserPoints(finalScore, quizId) {
    const halfFinalScore = finalScore / 2; // Քսմի հաշվարկ
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "update_point.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.status !== "success") {
                alert(response.message); // Show error message if needed
            }
        }
    };

    // Փոխանցենք կեսը finalScore-ի
    xhr.send("finalScore=" + halfFinalScore + "&quiz_id=" + quizId);
}



</script>


</body>
</html>

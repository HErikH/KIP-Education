<?php

session_start(); // Добавляем session_start() в самом начале
require_once 'constants.php';

// Проверка авторизации и роли
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Если пользователь не авторизован, перенаправляем на страницу логина
    header("Location: login");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    // Если пользователь авторизован, но не админ, перенаправляем на главную страницу
    header("Location: index");
    exit();
}

// Include database connection

include 'db_connect.php';



// Include the admin header

include 'headeradmin.php';



// Handle form submission

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = $_POST['quizTitle'];

    $subtitle = $_POST['quizSubtitle'];

    $timeInSeconds = $_POST['time_in_seconds'];

    $imagePath = null;



    // Handle file upload

    if (isset($_FILES['quizImage']) && $_FILES['quizImage']['error'] === UPLOAD_ERR_OK) {

        $uploadDir = UPLOAD_DIR . 'resource/quiz/img/';
        $fileName = basename($_FILES['quizImage']['name']);
        $savePath = $uploadDir . $fileName;
    
        if (move_uploaded_file($_FILES['quizImage']['tmp_name'], $savePath)) {
            // Save public URL in DB
            $imagePath = IMAGE_URL_BASE_FOR_DB . '/resource/quiz/img/' . $fileName;
        } else {
            echo "<div class='alert alert-danger'>Error uploading image.</div>";
            $imagePath = null;
        }

    }



    // Validate that the title is not empty

    if (!empty($title)) {

        $stmt = $conn->prepare("INSERT INTO quizzes (title, subtitle, time_in_seconds, image) VALUES (?, ?, ?, ?)");

        $stmt->bind_param("ssis", $title, $subtitle, $timeInSeconds, $imagePath);



        if ($stmt->execute()) {

            header("Location: " . $_SERVER['PHP_SELF']);

            exit();

        } else {

            echo "<div class='alert alert-danger'>Error adding quiz: " . $conn->error . "</div>";

        }



        $stmt->close();

    } else {

        echo "<div class='alert alert-warning'>Title is required.</div>";

    }

}



// Fetch quizzes and question counts from the database

$quizQuery = "

    SELECT quizzes.*, 

           IFNULL(question_counts.question_count, 0) AS question_count

    FROM quizzes

    LEFT JOIN (

        SELECT quiz_id, COUNT(*) AS question_count 

        FROM questions_new 

        GROUP BY quiz_id

    ) AS question_counts ON quizzes.id = question_counts.quiz_id

";

$quizResult = $conn->query($quizQuery);



// Close the database connection

$conn->close();

?>



<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Panel - Quizzes</title>

    <!-- Bootstrap CSS -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Font Awesome for Icons -->

    <style>
        body {

            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;

            background-color: #f8f9fa;

            margin: 0;

            padding: 0;

        }

        .main-container {

            display: flex;

            gap: 20px;

            padding: 20px;

        }

        .left-container {

            width: 30%;

            background-color: #ffffff;

            padding: 20px;

            border-radius: 10px;

            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);

            text-align: center;

        }

        .right-container {

            width: 70%;

        }

        .quiz-card {

            background-color: #ffffff;

            border-radius: 10px;

            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);

            margin-bottom: 20px;

            overflow: hidden;

            transition: transform 0.3s ease;

            width: 32%;
            /* Adjust to fit three cards in a row */

            display: inline-block;

            vertical-align: top;

        }



        .quiz-card:hover {

            transform: scale(1.05);
            /* Slightly increase the scale for a more noticeable effect */

        }



        .quiz-card img {

            width: 100%;

            height: 180px;
            /* Adjusted height for a larger image */

            object-fit: cover;

            border-bottom: 1px solid #ddd;
            /* Add a bottom border to separate the image from the content */

        }



        .quiz-card-body {

            padding: 15px;

            text-align: center;

        }



        .quiz-card-body {

            padding: 15px;

        }

        .quiz-title {

            font-size: 18px;

            font-weight: bold;

            color: #007bff;

            margin-bottom: 8px;

        }

        .quiz-subtitle {

            font-size: 14px;

            color: #6c757d;

        }

        .add-button {

            width: 90%;

            background-color: transparent;

            color: #007bff;

            border: 2px solid #007bff;

            padding: 15px;

            border-radius: 50px;

            font-size: 18px;

            font-weight: bold;

            cursor: pointer;

            transition: all 0.3s ease;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            text-decoration: none;

        }

        .add-button i {

            margin-right: 10px;

        }

        .add-button:hover {

            background-color: #007bff;

            color: #ffffff;

            border-color: #007bff;

            box-shadow: 0px 4px 10px rgba(0, 123, 255, 0.3);

        }

        .form-container {

            display: none;

            margin-top: 20px;

            text-align: left;

            background-color: #f9f9f9;

            padding: 20px;

            border-radius: 10px;

            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);

        }

        .form-container.active {

            display: block;

        }

        .form-control {

            margin-bottom: 15px;

        }

        .required-asterisk {

            color: #007bff;
            /* Blue color for asterisk */

        }

        .preview-image {

            margin-top: 15px;

            position: relative;

            display: inline-block;

        }

        .preview-image img {

            width: 100%;

            height: auto;

            max-height: 150px;

            border-radius: 10px;

            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);

        }

        .close-preview {

            position: absolute;

            top: -10px;

            right: -10px;

            background-color: #ff0000;

            color: #ffffff;

            border-radius: 50%;

            width: 25px;

            height: 25px;

            display: flex;

            align-items: center;

            justify-content: center;

            cursor: pointer;

            font-size: 14px;

            font-weight: bold;

        }

        .quiz-card-actions {

            display: flex;

            justify-content: space-around;
            /* Space buttons evenly */

            padding: 15px;

            border-top: 1px solid #ddd;
            /* Optional: to separate the buttons from the content */

            text-align: center;

            gap: 10px;
            /* Optional: add some space between the buttons */

        }



        .quiz-card-actions .btn {

            flex: 1;
            /* Make buttons take equal space */

            margin: 0;
            /* Remove any default margins */

            min-width: 0;
            /* Ensure buttons fit in the card */

        }

        .quiz-details {

            font-size: 14px;

            color: #6c757d;

            margin-top: 5px;

        }
    </style>

</head>

<body>



    <div class="main-container">

        <div class="left-container">

            <h3>Management Section</h3>

            <a href="#" class="add-button" id="addQuizButton">

                <i class="fas fa-plus"></i> Add New Quiz

            </a>

            <div class="form-container" id="quizFormContainer">

                <form method="POST" action="" enctype="multipart/form-data">

                    <div class="mb-3">

                        <label for="quizTitle" class="form-label">Title <span class="required-asterisk">*</span></label>

                        <input type="text" class="form-control" id="quizTitle" name="quizTitle"
                            placeholder="Enter quiz title" required>

                    </div>

                    <div class="mb-3">

                        <label for="quizSubtitle" class="form-label">Subtitle</label>

                        <input type="text" class="form-control" id="quizSubtitle" name="quizSubtitle"
                            placeholder="Enter quiz subtitle">

                    </div>

                    <div class="mb-3">

                        <label for="time_in_seconds" class="form-label">Time</label>

                        <select class="form-control" id="time_in_seconds" name="time_in_seconds">

                            <option value="30">30 sec</option>

                            <option value="60">1 min</option>

                            <option value="120">2 min</option>

                            <option value="300">5 min</option>

                            <option value="900" selected>15 min</option>

                            <option value="600">10 min</option>

                            <option value="1200">20 min</option>

                            <option value="1800">30 min</option>

                            <option value="3600">1 hour</option>

                        </select>

                    </div>

                    <div class="mb-3">

                        <label for="quizImage" class="form-label">Image</label>

                        <input type="file" class="form-control" id="quizImage" name="quizImage" accept="image/*">

                        <div class="preview-image" id="imagePreview" style="display: none;">

                            <img src="#" alt="Preview">

                            <div class="close-preview" onclick="removeImage()">x</div>

                        </div>

                    </div>

                    <button type="submit" class="btn btn-primary">Submit</button>

                </form>

            </div>

        </div>

        <div class="right-container">

            <h3>Quizzes</h3>

            <?php if ($quizResult && $quizResult->num_rows > 0): ?>

            <?php while ($quiz = $quizResult->fetch_assoc()): ?>

            <div class="quiz-card">

                <img src="<?= htmlspecialchars($quiz['image']) ?>" alt="Quiz Image">

                <div class="quiz-card-body">

                    <div class="quiz-title">
                        <?= htmlspecialchars($quiz['title']) ?>
                    </div>

                    <div class="quiz-subtitle">
                        <?= htmlspecialchars($quiz['subtitle']) ?>
                    </div>

                    <div class="quiz-details">

                        <?= htmlspecialchars($quiz['question_count']) ?> questions <br>

                        Duration:
                        <?= ceil($quiz['time_in_seconds'] / 60) ?> min

                    </div>

                </div>

                <div class="quiz-card-actions">

                    <a href="#" class="btn btn-info btn-sm" data-bs-toggle="modal"
                        data-bs-target="#manageModal-<?= $quiz['id'] ?>">

                        <i class="fas fa-edit"></i> Manage

                    </a>

                    <a href="questionssmart.php?id=<?= $quiz['id'] ?>" class="btn btn-warning btn-sm">

                        <i class="fas fa-question-circle"></i> Questions

                    </a>

                    <a href="delete_quiz.php?id=<?= $quiz['id'] ?>" class="btn btn-danger btn-sm">

                        <i class="fas fa-trash-alt"></i> Delete

                    </a>

                </div>

            </div>



            <!-- Modal for Managing Quiz -->

            <div class="modal fade" id="manageModal-<?= $quiz['id'] ?>" tabindex="-1"
                aria-labelledby="manageModalLabel-<?= $quiz['id'] ?>" aria-hidden="true">

                <div class="modal-dialog">

                    <div class="modal-content">

                        <div class="modal-header">

                            <h5 class="modal-title" id="manageModalLabel-<?= $quiz['id'] ?>">Manage Quiz</h5>

                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                        </div>

                        <div class="modal-body">

                            <form method="POST" action="update_quiz.php" enctype="multipart/form-data">

                                <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>">

                                <div class="mb-3">

                                    <label for="quizTitle-<?= $quiz['id'] ?>" class="form-label">Title</label>

                                    <input type="text" class="form-control" id="quizTitle-<?= $quiz['id'] ?>"
                                        name="quizTitle" value="<?= htmlspecialchars($quiz['title']) ?>">

                                </div>

                                <div class="mb-3">

                                    <label for="quizSubtitle-<?= $quiz['id'] ?>" class="form-label">Subtitle</label>

                                    <input type="text" class="form-control" id="quizSubtitle-<?= $quiz['id'] ?>"
                                        name="quizSubtitle" value="<?= htmlspecialchars($quiz['subtitle']) ?>">

                                </div>

                                <div class="mb-3">

                                    <label for="time_in_seconds-<?= $quiz['id'] ?>" class="form-label">Time (in
                                        seconds)</label>

                                    <input type="number" class="form-control" id="time_in_seconds-<?= $quiz['id'] ?>"
                                        name="time_in_seconds"
                                        value="<?= htmlspecialchars($quiz['time_in_seconds']) ?>">

                                </div>

                                <div class="mb-3">

                                    <label for="quizImage-<?= $quiz['id'] ?>" class="form-label">Image</label>

                                    <input type="file" class="form-control" id="quizImage-<?= $quiz['id'] ?>"
                                        name="quizImage" accept="image/*">

                                    <div class="preview-image mt-2">

                                        <img src="<?= htmlspecialchars($quiz['image']) ?>" alt="Current Image"
                                            style="max-width: 100%;">

                                    </div>

                                </div>

                                <button type="submit" class="btn btn-primary">Save Changes</button>

                            </form>

                        </div>

                    </div>

                </div>

            </div>

            <?php endwhile; ?>

            <?php else: ?>

            <p>No quizzes available.</p>

            <?php endif; ?>

        </div>

    </div>



    <?php include 'footer.php'; ?>



    <!-- Bootstrap JS (Optional) -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>

        // Toggle form visibility and change button text

        document.getElementById("addQuizButton").addEventListener("click", function (event) {

            event.preventDefault();

            var formContainer = document.getElementById("quizFormContainer");

            var addButton = document.getElementById("addQuizButton");

            formContainer.classList.toggle("active");

            addButton.innerHTML = formContainer.classList.contains("active")

                ? '<i class="fas fa-times"></i> Close Form'

                : '<i class="fas fa-plus"></i> Add New Quiz';

        });



        // Image preview function

        document.getElementById('quizImage').addEventListener('change', function (event) {

            const [file] = event.target.files;

            if (file) {

                const imagePreview = document.getElementById('imagePreview');

                const imgElement = imagePreview.querySelector('img');

                imgElement.src = URL.createObjectURL(file);

                imagePreview.style.display = 'block';

            }

        });



        // Remove image preview

        function removeImage() {

            const imagePreview = document.getElementById('imagePreview');

            const imgElement = imagePreview.querySelector('img');

            imgElement.src = '#';

            document.getElementById('quizImage').value = '';

            imagePreview.style.display = 'none';

        }

    </script>

</body>

</html>
<?php
session_start();
include 'db_connect.php';

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quizzes</title>
    <link rel="icon" href="resource/img/favicon.png" type="image/png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Body styling */
        body {
            background: linear-gradient(90deg, #4b6cb7 0%, #182848 100%);
            color: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
        }
        .main-content {
            padding: 180px 20px; /* Add space for header and safety for inline padding */
            text-align: center;
        }
        .page-title {
            font-size: 36px;
            font-weight: bold;
            color: #fff;
            margin-bottom: 30px;
            text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.5);
        }
        .container-quizzes {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        .quiz-card {
            background-color: #fff;
            color: #333;
            border-radius: 15px;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            width: 320px;
            text-align: center;
            position: relative;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .quiz-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.4);
        }
        .quiz-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .quiz-card-body {
            padding: 20px;
        }
        .quiz-title {
            font-size: 20px;
            font-weight: bold;
            color: #4b6cb7;
            margin-bottom: 10px;
        }
        .quiz-subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }
        .badge-info {
            background-color: #4b6cb7;
            color: #fff;
            border-radius: 15px;
            padding: 5px 10px;
            font-size: 12px;
            position: absolute;
            top: 15px;
            left: 15px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .badge-duration {
            background-color: #182848;
            color: #fff;
            border-radius: 15px;
            padding: 5px 10px;
            font-size: 12px;
            position: absolute;
            top: 15px;
            right: 15px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-view {
            display: inline-block;
            padding: 12px 25px;
            background-color: #4b6cb7;
            color: #fff;
            border-radius: 25px;
            text-decoration: none;
            transition: background-color 0.3s ease;
            font-size: 14px;
        }
        .btn-view:hover {
            color: white;
            /* background-color: #182848; */
            background-color: #2c4572;
            text-decoration: none;
        }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <div class="main-content">
        <div class="page-title">Quizzes - Test Your Knowledge</div>
        <div class="container-quizzes">
            <?php if ($quizResult && $quizResult->num_rows > 0): ?>
                <?php while ($quiz = $quizResult->fetch_assoc()): ?>
                    <div class="quiz-card">
                        <?php if (!empty($quiz['image'])): ?>
                            <img src="<?= htmlspecialchars($quiz['image']) ?>" alt="Quiz Image">
                        <?php else: ?>
                            <img src="resource/quiz/img/default.jpg" alt="Default Image">
                        <?php endif; ?>
                        <div class="badge-info">
                            <i class="fas fa-question-circle"></i> <?= htmlspecialchars($quiz['question_count']) ?> Questions
                        </div>
                        <div class="badge-duration">
                            <i class="fas fa-clock"></i> <?= ceil($quiz['time_in_seconds'] / 60) ?> min
                        </div>
                        <div class="quiz-card-body">
                            <div class="quiz-title"><?= htmlspecialchars($quiz['title']) ?></div>
                            <div class="quiz-subtitle"><?= htmlspecialchars($quiz['subtitle']) ?></div>
                            <a href="quiz_details?id=<?= $quiz['id'] ?>" class="btn-view">View Quiz</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No quizzes available.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>

</body>
</html>

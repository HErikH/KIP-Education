<?php
session_start();

include 'db_connect.php'; // Подключение к базе данных

// Проверка, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: login");
    exit();
}

$current_user_id = $_SESSION['user_id']; // ID текущего пользователя
$role = $_SESSION['role']; // Получение роли пользователя

// Проверка типа пользователя (только для "student")
$showPopup = false; // По умолчанию popup не показывается

if ($role === 'student') {
    // Проверка на наличие date_start_role и date_end_role
    $query = "SELECT date_start_role, date_end_role FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $current_user_id);
    $stmt->execute();
    $stmt->bind_result($date_start_role, $date_end_role);
    $stmt->fetch();
    $stmt->close();

    // Если date_start_role или date_end_role пустое, показываем popup
    if (empty($date_start_role) || empty($date_end_role)) {
        $showPopup = true;

        // Если кнопка Start была нажата, обновляем дату начала и окончания роли
        if (isset($_POST['start'])) {
            // Получаем текущую дату
            $date_start = new DateTime(); // Текущая дата
            $date_end = new DateTime();    // Клонируем, чтобы прибавить интервал

            // Добавляем 76 дней (точно 2.5 месяца)
            $date_end->add(new DateInterval('P76D')); // 76 дней

            $date_start_role = $date_start->format('Y-m-d');
            $date_end_role = $date_end->format('Y-m-d');

            // Обновление date_start_role и date_end_role в базе данных
            $updateQuery = "UPDATE users SET date_start_role = ?, date_end_role = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("ssi", $date_start_role, $date_end_role, $current_user_id);
            $updateStmt->execute();
            $updateStmt->close();

            // Закрываем popup после обновления
            $showPopup = false;
        }
    }
}


// Уроки на странице
$limit = 8;

// Текущая страница
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Проверка на наличие post_id
if (isset($_GET['post_id']) && is_numeric($_GET['post_id'])) {
    $post_id = (int)$_GET['post_id'];
    
    // SQL-запрос для получения урока по ID
    $lessonsQuery = "SELECT lessons.id, lessons.title, lessons.tag, lessons.image, lessons.video, lessons.small_videos, lessons.files 
    FROM lessons 
    WHERE lessons.id = $post_id";

    // Запрос для общего количества уроков для пагинации
    $totalLessonsQuery = "SELECT COUNT(*) as total FROM lessons";
    $totalLessonsResult = $conn->query($totalLessonsQuery);
    $totalLessons = $totalLessonsResult->fetch_assoc()['total'];
    $totalPages = ceil($totalLessons / $limit);
} else {
    // Стандартный запрос для получения уроков
    $lessonsQuery = "SELECT lessons.id, lessons.title, lessons.tag, lessons.image, lessons.video, lessons.small_videos, lessons.files 
    FROM lessons 
    ORDER BY lessons.id ASC 
    LIMIT $limit OFFSET $offset";

    // Запрос для общего количества уроков
    $totalLessonsQuery = "SELECT COUNT(*) as total FROM lessons";
    $totalLessonsResult = $conn->query($totalLessonsQuery);
    $totalLessons = $totalLessonsResult->fetch_assoc()['total'];
    $totalPages = ceil($totalLessons / $limit);
}

$lessonsResult = $conn->query($lessonsQuery);

if (!$lessonsResult) {
    die("SQL query failed: " . $conn->error);
}

// Закрытие соединения
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lesson Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(90deg, #4b6cb7, #182848);
            color: white;
        }

        .content {
            margin-top: 150px;
            margin-bottom: 100px;
        }

        .right-section {
            width: 80%;
            max-width: 900px;
            margin: 50px auto;
            background-color: rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
        }

        @media (max-width: 768px) {
            .right-section {
                width: 100%;
                padding: 20px;
                margin: 20px 0;
            }
        }

        /* Стили модального окна */
        .modal-header {
            background: linear-gradient(90deg, #1e3c72, #2a5298);
            color: white;
            font-size: 2rem;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 2px;
            padding: 20px;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5);
        }

        .modal-dialog {
            max-width: 60%;
        }

        .modal-content {
            background: linear-gradient(90deg, #4b6cb7, #182848);
            color: white;
            border-radius: 10px;
        }

        .modal-body {
            background-color: #2c3e50;
            color: #ecf0f1;
            padding: 20px;
            font-size: 1rem;
        }

        @media (max-width: 768px) {
            .modal-dialog {
                margin: 20px auto;
                max-width: 90%;
            }

            .modal-body {
                padding: 10px;
            }

            .modal-header {
                padding: 15px;
                font-size: 1.5rem;
            }

            .video-files-container {
                flex-direction: column;
            }

            .video-container {
                height: 100px;
            }

            .files-container {
                padding: 15px;
                width: 100%;
            }

            .files-container h5 {
                font-size: 1.1rem;
            }

            .files-container a {
                font-size: 0.9rem;
                padding: 10px;
            }

            .video-return-btn {
                padding: 12px 20px;
            }
        }

        /* File embed container style */
        .file-embed-container {
            width: 50%;
            /* Default width on larger screens */
            height: 400px;
            margin: 0 auto;
            /* Center the container */
        }

        .file-iframe {
            width: 100%;
            height: 100%;
        }

        /* For mobile screens, we want the iframe and file-embed-container to be full-width */
        @media (max-width: 768px) {
            .file-embed-container {
                width: 100%;
                /* Full width on smaller screens */
            }

            .file-iframe {
                width: 100%;
                /* Full width iframe on smaller screens */
            }
        }

        /* Дизайн секции видео и файлов */
        .video-files-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 20px;
            gap: 20px;
        }

        /* Дизайн секции видео */
        .video-container {
            flex: 0.9;
            position: relative;
            background-color: #000;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .video-container video,
        .file-embed-container iframe {
            width: 100%;
            height: 400px;
            border-radius: 10px;
        }

        /* Заголовок видео с иконкой */
        .video-header {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            padding: 10px;
            background-color: rgba(0, 0, 0, 0.7);
            color: #ffffff;
            font-size: 1.4rem;
            font-weight: bold;
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .video-header i {
            font-size: 1.5rem;
        }

        /* Прячем контейнер для файлов по умолчанию */
        .file-embed-container {
            display: none;
        }

        /* Секция файлов */
        .files-container {
            flex: 0.5;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .files-container h5 {
            color: #3498db;
            margin-bottom: 15px;
            font-size: 1.3rem;
        }

        .files-container a {
            display: block;
            width: 100%;
            padding: 12px;
            margin-bottom: 10px;
            background-color: #6c5ce7;
            color: white;
            text-align: center;
            font-size: 1rem;
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .files-container a:hover {
            background-color: #8e44ad;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        .files-container a:active {
            background-color: #9b59b6;
        }

        /* Стили кнопки "Video Lesson" */
        .video-return-btn {
            background-color: #3498db;
            border: none;
            color: white;
            padding: 15px 30px;
            font-size: 1.2rem;
            font-weight: bold;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .video-return-btn i {
            margin-right: 10px;
        }

        .video-return-btn:hover {
            background-color: #2980b9;
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.3);
        }

        .video-return-btn:focus {
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.5);
        }

        .header-lessons {
            margin-top: 2rem;
            font-size: 2rem;
            text-align: center;
        }

        .lesson-post {
            background-color: rgba(255, 255, 255, 0.2);
            margin-top: 20px;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            overflow: hidden;
        }

        .lesson-post img {
            height: 80px;
            border-radius: 8px;
        }

        .lesson-post .info {
            flex-grow: 1;
            margin-left: 15px;
            overflow: hidden;
        }

        .lesson-post .info h4 {
            margin: 0;
            font-size: 1.2rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #ffffff;
        }

        .lesson-post .info p {
            margin: 5px 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #ffffff;
        }

        .lesson-post button {
            font-size: 1rem;
            padding: 10px 20px;
            border-radius: 50px;
            background-color: #3498db;
            border: none;
            transition: background-color 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .lesson-post button:hover {
            background-color: #2980b9;
        }

        /* Pagination Styles */
        .pagination-wrapper-top {
            display: flex;
            justify-content: center;
            /* Centers the block */
            align-items: center;
            /* Aligns buttons and fields vertically */
            gap: 20px;
            /* Spacing between input field and pagination */
            margin-top: 20px;
        }

        .search-container {
            display: flex;
            align-items: center;
            padding: 0;
        }

        .search-by-id {
            display: flex;
            width: auto;
        }

        .search-by-id #post_id_remove {
            position: absolute;
            top: 50%;
            right: 65%;
            transform: translateY(-50%);
            z-index: 10;
            transition: 0.3s linear;
            color: #3498db;
            cursor: pointer;
        }

        .search-by-id #post_id_remove:hover {
            color: #023e86;
        }

        .search-by-id input {
            width: 150px;
            padding: 8px;
            border-radius: 8px 0 0 8px;
            border: 1px solid #ccc;
            font-size: 1rem;
            box-sizing: border-box;
        }

        .search-by-id button {
            width: auto;
            padding: 8px 15px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 0 8px 8px 0;
            cursor: pointer;
            transition: background-color 0.3s ease;
            box-sizing: border-box;
        }

        .search-by-id button:hover {
            background-color: #2980b9;
        }

        .pagination {
            display: flex;
            gap: 5px;
        }

        .pagination a {
            padding: 8px 16px;
            text-decoration: none;
            color: white;
            background-color: #3498db;
            border-radius: 50px;
            transition: background-color 0.3s ease;
            font-weight: bold;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .pagination a.active {
            background-color: #023e86;
        }

        .pagination a:hover {
            background-color: #2980b9;
        }

        .pagination-wrapper-bottom {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
        }

        .pagination a.btn-view-all {
            background-color: #3498db;
            width: 250px;
            padding: 12px 25px;
            font-size: 1.1rem;
            border-radius: 50px;
        }

        @media (max-width: 768px) {
            .pagination a:not(.btn-primary) {
                display: none;
            }

            .pagination-wrapper-bottom {
                flex-direction: row;
                justify-content: center;
                width: 100%;
            }

            .pagination a {
                padding: 6px 10px;
            }

            .pagination a.btn-view-all {
                width: 250px;
                padding: 12px 25px;
                font-size: 1.1rem;
                border-radius: 50px;
            }

            .pagination a.btn-view-all:hover {
                background-color: #2980b9;
            }
        }

        .other-videos-container {
            flex: 0.5;
            background-color: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            margin-left: 20px;
        }

        .other-videos-container h5 {
            color: #3498db;
            margin-bottom: 15px;
        }

        .other-videos-container a {
            display: block;
            margin-bottom: 10px;
            color: white;
            text-decoration: none;
            padding: 10px;
            background-color: #6c5ce7;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        .other-videos-container a:hover {
            background-color: #8e44ad;
        }

        @media (min-width: 992px) {
            .modal-lg {
                max-width: 1500px;
            }
        }

        .video-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            /* 2 սյունակ */
            gap: 20px;
            /* Տարածություն վիդեոների միջև */
            padding: 0;
            /* Հեռացնում ենք padding-ը */
            margin: 0;
            /* Հեռացնում ենք margin-ը */
            list-style-type: none;
            /* Հեռացնում ենք կետերը */
        }

        .video-grid li {
            list-style-type: none;
            /* Հեռացնում ենք ցուցակի կետերը */
        }

        .small-video {
            width: 100%;
            /* Վիդեոները կտեղավորվեն 100% լայնությամբ իրենց բլոկի մեջ */
            height: auto;
            /* Բարձրությունը կհամապատասխանի լայնությանը */
        }

        /* Play a Game button styles */
        .game-btn {
            background-color: #e74c3c;
            border: none;
            color: white;
            padding: 15px 30px;
            font-size: 1.2rem;
            font-weight: bold;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            cursor: pointer;
        }

        .game-btn i {
            margin-right: 10px;
        }

        .game-btn:hover {
            background-color: #c0392b;
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.3);
        }

        .game-btn:focus {
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(231, 76, 60, 0.5);
        }

        .popup-overlay.elegant {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            /* Dark translucent background */
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .popup-content.elegant {
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            text-align: center;
            animation: fadeIn 0.5s ease;
        }

        .popup-content.elegant h2 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #34495e;
        }

        .popup-content.elegant p {
            font-size: 18px;
            margin-bottom: 30px;
            color: #7f8c8d;
        }

        .popup-content.elegant button {
            background-color: #3498db;
            color: white;
            padding: 10px 30px;
            font-size: 16px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .popup-content.elegant button:hover {
            background-color: #2980b9;
        }

        .popup-content.elegant a {
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
        }

        .popup-content.elegant a:hover {
            color: #2980b9;
        }

        /* Fade-in animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-body-overlay {
            position: absolute;
            bottom: 24px;
            /* Համոզվեք, որ overlay-ը տեղադրվում է մոդալի ներքևում */
            left: calc(50% - 100px);
            /* Տեղադրում ենք ձախ կողմ՝ կախված ընդհանուր լայնությունից */
            width: 90px;
            /* Կարգավորում ենք լայնությունը */
            height: 20px;
            /* Նվազեցնում ենք բարձրությունը */
            background-color: #444444;
            /* Թույլ մոխրագույն գույն՝ առանց թափանցիկության */
            z-index: 9999;
            /* Ամենաբարձր z-index, որպեսզի ծածկի մյուս տարրերը */
            border-top-right-radius: 0px;
            /* Տարրին ավելի գեղեցիկ տեսք տալու համար */
        }

        /* Մեդիա հարցումներ 991px-ից 769px էկրանների համար */
        @media (max-width: 991px) and (min-width: 769px) {
            .modal-body-overlay {
                left: calc(50% - 120px);
                /* Տեղափոխում ենք ավելի արագ ձախ կողմ */
                width: 30px;
                /* Կարգավորում ենք լայնությունը 10px */
                bottom: 25px;
                /* Համոզվեք, որ overlay-ը տեղադրվում է մոդալի ներքևում */

            }
        }

        /* Մեդիա հարցումներ 768px և ավելի փոքր էկրանների համար */
        @media (max-width: 768px) {
            .modal-body-overlay {
                left: calc(50% - 180px);
                /* Կարգավորում ենք ձախ դիրքը փոքր էկրանների համար */
                width: 0px;
                /* Չփոփոխվող լայնություն */
                bottom: 953px;
                /* Բարձրությունը մնում է նույնը */
            }
        }
    </style>
</head>

<body>

    <!-- Header -->
    <?php include 'header.php'; ?>

    <div class="content">
        <div class="right-section">

            <!-- Pagination and Search at the top -->
            <div class="pagination-wrapper-top">
                <!-- Search input and button container -->
                <div class="search-container">
                    <form method="GET" action="" class="search-by-id">
                        <div class="position-relative">
                            <?php if (isset($_GET['post_id'])): ?>
                            <i onclick="removePostId()" id="post_id_remove" class="fas fa-times"></i>
                            <?php endif; ?>
                            <input type="number" name="post_id" id="post_id" placeholder="ID" min="1" max="64"
                                value="<?= isset($_GET['post_id']) ? htmlspecialchars($_GET['post_id']) : '' ?>"
                                class="form-control-lg" required>
                        </div>
                        <button type="submit" class="btn btn-secondary">Search</button>
                    </form>
                </div>


                <!-- Տեսանելի է միայն այն ժամանակ, երբ ID չի որոնվում -->
                <?php if (!isset($_GET['post_id'])): ?>
                <div class="pagination pagination-top">
                    <!-- Previous Button -->
                    <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>" class="btn btn-primary">Previous</a>
                    <?php endif; ?>

                    <!-- Page Numbers for desktop only -->
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?>" class="btn <?= ($i == $page) ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                    <?php endfor; ?>

                    <!-- Next Button -->
                    <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>" class="btn btn-primary">Next</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Content Section -->
            <div class="header-lessons">
                <span>Lessons</span>
            </div>

            <?php while ($lesson = $lessonsResult->fetch_assoc()): ?>
            <div class="lesson-post">
                <img src="<?= $lesson['image'] ?>" alt="Lesson Image">
                <div class="info">
                    <h4>
                        <?= $lesson['title'] ?>
                    </h4>
                    <p>
                        <?= $lesson['tag'] ?>
                    </p>
                </div>
                <a href="lessonview?id=<?= $lesson['id'] ?>" class="btn btn-primary">Open</a>

            </div>

            <?php endwhile; ?>
            <?php if ($showPopup): ?>
            <div class="popup-overlay elegant">
                <div class="popup-content elegant">
                    <h2>Notification</h2>
                    <p>By clicking <strong>Start</strong>, the page becomes available to you for 76 days.<br> Upon
                        expiry, please contact us: <a href="contact">Contact Us</a>.</p>
                    <form method="POST">
                        <button type="submit" name="start">Start</button>
                    </form>
                </div>

            </div>
            <?php endif; ?>


            <!-- <?php include 'footer.php'; ?> -->

</body>

</html>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstra
p.bundle.min.js"></script>

<script>
    $(document).ready(function () {
        // Hide the preloader when the page is fully loaded
        $(window).on('load', function () {
            $('#preloader').fadeOut('slow', function () {
                $(this).remove();
            });
        });

        // Stop video playback when the modal is closed
        $('.modal').on('hidden.bs.modal', function () {
            var video = $(this).find('video')[0];
            if (video) {
                video.pause();
                video.currentTime = 0;
            }
        });

        // Handle clicks on file links (PDF, PPTX, and Office files)
        $('.file-link').on('click', function (e) {
            e.preventDefault();

            var fileSrc = $(this).data('file'); // Get the file path
            var lessonId = $(this).data('id');  // Get the lesson ID for this modal

            // Hide the video container and stop video playback
            var video = $('#video-' + lessonId)[0];
            if (video) {
                video.pause();  // Pause the video
                video.currentTime = 0;  // Reset video to start
            }
            $('#video-container-' + lessonId).hide();

            // Determine the file extension to choose the correct viewer
            var fileExtension = fileSrc.split('.').pop().toLowerCase();
            var fileIframe;

            // Create iframe for PDF or PPTX or other Office files
            if (fileExtension === 'pptx') {
                // Show the overlay only for PPTX files
                $('.modal-body-overlay').show();

                var officeViewerUrl = 'https://view.officeapps.live.com/op/embed.aspx?src=' + encodeURIComponent(window.location.origin + '/' + fileSrc);
                fileIframe = `<iframe src="${officeViewerUrl}" class="file-iframe" frameborder="0"></iframe>`;
            } else if (fileExtension === 'pdf') {
                fileIframe = `<iframe src="${fileSrc}" class="file-iframe" frameborder="0"></iframe>`;
                // Hide the overlay for other file types
                $('.modal-body-overlay').hide();
            } else if (fileExtension === 'jpg' || fileExtension === 'jpeg') {
                // Create an image element for JPG files with customizable dimensions
                fileIframe = `<img src="${fileSrc}" class="file-iframe" style="width: 100%; max-width: 300px; height: auto; margin: 0 auto; display: block;" alt="Image">`;
                // Hide the overlay for image files
                $('.modal-body-overlay').hide();
            } else {
                var officeViewerUrl = 'https://view.officeapps.live.com/op/embed.aspx?src=' + encodeURIComponent(window.location.origin + '/' + fileSrc);
                fileIframe = `<iframe src="${officeViewerUrl}" class="file-iframe" frameborder="0"></iframe>`;
                // Hide the overlay for other file types
                $('.modal-body-overlay').hide();
            }

            // Insert the iframe or image into the file embed container and show it
            $('#file-embed-container-' + lessonId).html(fileIframe).show();
        });

        // Handle click on "Video Lesson" button
        $('.video-return-btn').on('click', function (e) {
            e.preventDefault();

            var lessonId = $(this).data('id');
            var mainVideo = $('#video-' + lessonId)[0]; // Get the main video element for this lesson

            // Hide the file container and clear its content
            $('#file-embed-container-' + lessonId).hide();
            $('#file-embed-container-' + lessonId).html('');

            // Reset the video source to the initial video (if it was changed)
            var initialVideoSrc = $(this).data('video'); // Use the video source dynamically
            mainVideo.querySelector('source').setAttribute('src', initialVideoSrc);
            mainVideo.load(); // Load the video with the initial source
            mainVideo.play(); // Start playing the video

            // Show the video container if it was hidden
            $('#video-container-' + lessonId).show();

            // Hide the overlay when video is shown
            $('.modal-body-overlay').hide();
        });

        // Function to play a small video in the main player
        function playInMainVideo(videoSrc, lessonId) {
            var mainVideo = document.getElementById('video-' + lessonId); // Use dynamic video ID based on lesson ID
            if (mainVideo) {
                var source = mainVideo.querySelector('source');
                if (source) {
                    source.setAttribute('src', videoSrc);
                    mainVideo.load(); // Load the new video source
                    mainVideo.play(); // Play the video

                    // Hide file container and show video container
                    $('#file-embed-container-' + lessonId).hide(); // Hide file embed container
                    $('#file-embed-container-' + lessonId).html(''); // Clear the file content
                    $('#video-container-' + lessonId).show(); // Show the video container
                }
            }

            // Hide the overlay when a video is played
            $('.modal-body-overlay').hide();
        }

        // Assign click event to small video elements
        $('.small-video').on('click', function () {
            var videoSrc = $(this).find('source').attr('src');
            var lessonId = $(this).closest('.modal').find('.video-return-btn').data('id');
            playInMainVideo(videoSrc, lessonId); // Call the function when a small video is clicked
        });

        // Add active class to clicked pagination links
        document.querySelectorAll('.pagination a').forEach(function (link) {
            link.addEventListener('click', function () {
                // Remove active class from all pagination links
                document.querySelectorAll('.pagination a').forEach(function (el) {
                    el.classList.remove('active');
                });
                // Add active class to the clicked link
                this.classList.add('active');
            });
        });

        // Input validation for post_id field
        document.getElementById('post_id').addEventListener('input', function () {
            var inputValue = parseInt(this.value);

            // Limit input value to a maximum of 64
            if (inputValue > 64) {
                this.value = 64;
            }
        });

        // Function to set the poster for videos at 30 seconds, but only for 'Other Videos'
        function setPosterForOtherVideos() {
            var smallVideos = document.querySelectorAll('.small-video');

            smallVideos.forEach(function (video) {
                video.addEventListener('loadedmetadata', function () {
                    video.currentTime = 30; // Seek to 30 seconds

                    video.addEventListener('seeked', function () {
                        var canvas = document.createElement('canvas');
                        canvas.width = video.videoWidth;
                        canvas.height = video.videoHeight;
                        var context = canvas.getContext('2d');
                        context.drawImage(video, 0, 0, canvas.width, canvas.height);

                        var dataURL = canvas.toDataURL();
                        video.setAttribute('poster', dataURL);

                        video.currentTime = 0; // Reset to the start
                    }, { once: true });
                });
            });
        }

        // Call the function to set posters for "Other Videos" only
        setPosterForOtherVideos();
    });

    function removePostId() {
        const url = new URL(window.location.href);

        console.log(url.searchParams.has('post_id'))

        url.searchParams.delete('post_id'); // Remove post_id
        url.searchParams.set('page', '1');  // Set page to 1

        // Redirect to the new URL (actually navigates)
        window.location.href = url.toString();

        return false;
    }
</script>


<?php include 'footer.php'; ?>

</body>

</html>
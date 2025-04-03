<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include 'db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student' || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$post_id = (int)$_GET['id'];
$query = "SELECT title, tag, image, video, small_videos, files FROM lessons WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$stmt->bind_result($title, $tag, $image, $video, $small_videos, $files);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> - Lesson View</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(90deg, #4b6cb7, #182848);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #fff;
            padding-top: 120px;
        }
        .container {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            gap: 20px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }
        .left-container {
            width: 40%;
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
        }
        .right-container {
            width: 60%;
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
        }
        .lesson-header {
            text-align: center;
            padding-bottom: 15px;
            margin-bottom: 20px;
            border-bottom: 1px solid #ccc;
        }
        .lesson-header h1 {
            font-size: 1.8rem;
            color: #e0e4f0;
        }
        .lesson-header p {
            font-size: 1.2rem;
            color: #b0c4de;
        }
        .lesson-image img {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            transition: transform 0.3s ease, border-color 0.3s ease;
        }
        .lesson-image img:hover {
            transform: scale(1.05);
            border: 3px solid #3498db;
        }
        .file-link {
            display: block;
            width: 100%;
            padding: 12px;
            margin-bottom: 10px;
            background-color: #6c5ce7;
            color: white;
            font-weight: bold;
            border-radius: 8px;
            text-align: center;
            transition: background-color 0.3s ease;
            text-decoration: none;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        .file-link:hover {
            background-color: #5b4acd;
            color: white;
        }
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .popup-content {
            width: 100%;
            height: 100%;
            background: #444444;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5);
            overflow: auto;
            color: #fff;
            position: relative;
        }
        .popup-content iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        .popup-close {
            position: absolute;
            top: 5px;
            right: 10px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #fff;
            color: #4b6cb7;
            font-size: 1.5rem;
            text-align: center;
            line-height: 40px;
            border: 2px solid #4b6cb7;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .popup-close:hover {
            transform: scale(1.1);
        }
        .popup-footer-bar {
            position: absolute;
            bottom: 24px;
            right: 24px;
            width: 90px; /* Increased width */
            height: 20px;
            background-color: #444444;
            border-radius: 5px;
                        display: none; /* Hidden by default */

        }
.popup-bottom-bar {
    position: absolute;
    bottom: 24px;
    right: 24px;
    width: 90px; /* Adjusted width */
    height: 15px;
    background-color: #ffffff; /* White color for the bottom bar */
    border-radius: 5px;
    display: none; /* Hidden by default */
}


        .other-videos h2 {
            font-size: 1.2rem;
            color: #e0e4f0;
            margin-top: 20px;
        }
        .video-thumbnail {
            width: 22%;
            height: auto;
            object-fit: cover;
            border-radius: 10px;
            cursor: pointer;
            transition: transform 0.3s;
            margin: 5px;
        }
        .video-thumbnail:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3);
        }
        @media (max-width: 768px) {
    .container {
        flex-direction: column;
    }
    .right-container {
        order: 1;
        width: 100%;
    }
    .left-container {
        order: 2;
        width: 100%;
        margin-bottom:100px;
    }
}
.popup-content img {
    display: block;
    margin: 0 auto; /* Centers the image horizontally */
    max-width: 100%;
    max-height: 100%;
    width: auto;
    height: auto;
    object-fit: contain; /* Ensures the image covers the area without stretching */
    border-radius: 10px;
}

    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <div class="left-container">
        <div class="lesson-header">
            <h1><?= htmlspecialchars($title) ?></h1>
            <p><?= htmlspecialchars($tag) ?></p>
        </div>
        <div class="lesson-image text-center my-3">
            <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($title) ?>" loading="lazy" onclick="playInPreview('<?= htmlspecialchars($video) ?>')">
        </div>

        <?php if (!empty($files)): ?>
            <div class="files-section mt-4">
                <?php foreach (json_decode($files, true) as $file): ?>
                    <a href="#" class="file-link" data-file="<?= htmlspecialchars($file) ?>">
                        <i class="fas fa-file"></i> <?= basename($file) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
         <div class="text-center mt-4">
        <a href="https://wordwall.net/" target="_blank" class="btn btn-lg btn-primary game-btn">
            <i class="fas fa-gamepad"></i> Play a Game
        </a>
    </div>
    </div>

    <div class="right-container">
        <div class="video-preview">
            <h2><i class="fas fa-eye"></i> Video Preview</h2>
            <video id="main-video" controls controlsList="nodownload" style="width: 100%;" poster="<?= htmlspecialchars($image) ?>">
                <source src="<?= htmlspecialchars($video) ?>" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>

        <?php if (!empty($small_videos)): ?>
            <div class="other-videos">
                <h2>Other Videos</h2>
                <div class="d-flex flex-wrap">
                    <?php foreach (json_decode($small_videos, true) as $small_video): ?>
                        <video muted class="video-thumbnail" onclick="playInPreview('<?= htmlspecialchars($small_video) ?>')" src="<?= htmlspecialchars($small_video) ?>#t=30"></video>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="popup-overlay" id="popup-overlay">
    <div class="popup-content">
        <span class="popup-close" onclick="closePopup()">&times;</span>
        <iframe id="file-iframe"></iframe>
        <div class="popup-footer-bar" id="popup-footer-bar"></div> <!-- Bottom right bar -->
        <div class="popup-bottom-bar" id="popup-bottom-bar"></div> <!-- New styled bar for docx -->
    </div>
</div>

<?php include 'footer.php'; ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    $('.file-link').on('click', function(e) {
        e.preventDefault();
        const fileSrc = $(this).data('file');
        const fileExtension = fileSrc.split('.').pop().toLowerCase();
        let fileIframeSrc;

        if (fileExtension === 'pdf') {
            // For PDFs, trigger a download instead of showing a preview
            window.location.href = fileSrc;
            return;  // Exit the function to prevent further actions
        } else if (fileExtension === 'pptx') {
            fileIframeSrc = 'https://view.officeapps.live.com/op/embed.aspx?src=' + encodeURIComponent(window.location.origin + '/' + fileSrc) + "&DisableDownload=1";
            $('#popup-footer-bar').show();  // Show the footer bar for PPTX files
            $('#popup-bottom-bar').hide();  // Hide the bottom bar for PPTX files
        } else if (fileExtension === 'docx') {
            fileIframeSrc = 'https://view.officeapps.live.com/op/embed.aspx?src=' + encodeURIComponent(window.location.origin + '/' + fileSrc) + "&DisableDownload=1";
            $('#popup-footer-bar').hide();  // Hide the footer bar for DOCX files
            $('#popup-bottom-bar').show();  // Show the bottom bar for DOCX files
        } else if (fileExtension === 'jpg' || fileExtension === 'jpeg' || fileExtension === 'png') {
            fileIframeSrc = fileSrc;
            $('#file-iframe').replaceWith('<img id="file-iframe" src="' + fileSrc + '" style="max-width: 100%; max-height: 100%; border-radius: 10px;">');
            $('#popup-footer-bar').hide();  // Hide the footer bar for images
            $('#popup-bottom-bar').hide();  // Hide the bottom bar for images
        } else {
            alert("Preview not supported for this file type.");
            return;
        }

        if (fileExtension !== 'jpg' && fileExtension !== 'jpeg' && fileExtension !== 'png') {
            $('#file-iframe').replaceWith('<iframe id="file-iframe" src="' + fileIframeSrc + '"></iframe>');
        }

        $('#popup-overlay').fadeIn();

        const mainVideo = document.getElementById('main-video');
        mainVideo.pause();
    });
});

function closePopup() {
    $('#popup-overlay').fadeOut();
}

function playInPreview(videoSrc) {
    const mainVideo = document.getElementById('main-video');
    mainVideo.querySelector('source').src = videoSrc;
    mainVideo.load();
    mainVideo.play();
}

</script>
</body>
</html>
<?php
// Include the database connection
include 'db_connect.php';

// Ստուգել՝ արդյոք օգտագործողը մուտք է գործել
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    // Եթե օգտագործողը ուսուցիչ չէ, տեղափոխել login.php էջը
    header('Location: login.php');
    exit();
}


include 'header.php';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$userId = $_SESSION['user_id']; // Օգտագործողի ID

// Ստուգել, արդյոք `date_start_role` կամ `date_end_role`-ը NULL է
$sql = "SELECT date_start_role, date_end_role FROM users WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

// Եթե `date_start_role` կամ `date_end_role`-ը NULL է, ցույց տալ popup-ը
$showPopup = is_null($userData['date_start_role']) || is_null($userData['date_end_role']);

// Եթե սեղմվել է Start կոճակը, թարմացնել տվյալները
if (isset($_POST['start'])) {
    $currentDate = date('Y-m-d H:i:s'); // Ստեղծել ստարտային ժամկետը
    $date_end = date('Y-m-d H:i:s', strtotime('+1 year')); // Վերջնաժամկետ՝ 1 տարի ավելացմամբ

    // Թարմացնել `date_start_role` և `date_end_role`
    $sql = "UPDATE users SET date_start_role = ?, date_end_role = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$currentDate, $date_end, $userId]);

    $showPopup = false; // Թարմացումից հետո popup-ը փակել
}

// Querying lessons
$query = "SELECT * FROM lessons";
$stmt = $pdo->prepare($query);
$stmt->execute();
$lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group lessons by tag (Letters, Music, etc.)
$grouped_lessons = [
    "Letters" => [],
    "Music" => [],
    "Culture" => [],
    "Logic_Math" => [],
    "Art" => [],
    "Book" => [] // Գրքերի բաժինը նույնպես տեղադրված է, սակայն այն կթաքցնենք
];

// Fill the groups
foreach ($lessons as $lesson) {
    if (array_key_exists($lesson['tag'], $grouped_lessons)) {
        $grouped_lessons[$lesson['tag']][] = $lesson;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>For Teachers</title>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Bootstrap for layout -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Styles -->
    <style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 70px 0 30px 0; /* Վերևից 70px, ներքևից 30px */
    background: linear-gradient(90deg, #4b6cb7, #182848); /* Background gradient */
    color: #fff;
    min-height: 100vh;
}

.main-container {
    display: flex;
    justify-content: flex-start;
    align-items: flex-start;
    padding: 20px;
    margin: 50px;
}

.files-list {
    width: 30%;
    height: 690px; /* Սահմանափակենք բարձրությունը */
    margin-right: 20px;
    background-color: rgba(255, 255, 255, 0.1); /* Թափանցիկ ֆոն */
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
    text-align: center;
    overflow-y: auto; /* Ավելացնենք ուղղահայաց scroll */
    scrollbar-width: thin; /* Հասանելի է Firefox-ի համար */
    scrollbar-color: #4b6cb7 rgba(255, 255, 255, 0.1); /* Scroll գույների կարգավորում Firefox-ի համար */
}

/* Chrome, Edge, Safari համար scrollbar ձևավորումը */
.files-list::-webkit-scrollbar {
    width: 10px;
}

.files-list::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1); /* Scrollbar-ի ֆոնը */
    border-radius: 10px;
}

.files-list::-webkit-scrollbar-thumb {
    background-color: #4b6cb7; /* Scrollbar-ի հատվածի գույնը */
    border-radius: 10px;
    border: 2px solid rgba(255, 255, 255, 0.1); /* Scrollbar-ի հատվածի եզրեր */
}

.files-list::-webkit-scrollbar-thumb:hover {
    background-color: #182848; /* Hover-ի վրա գույնի փոփոխություն */
}

.files-list h3 {
    margin-bottom: 20px;
    text-transform: uppercase;
    font-size: 24px;
    text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
}

.file-item {
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    display: flex;
    align-items: center;
}

.file-item:hover {
    background-color: rgba(255, 255, 255, 0.4);
}

.file-item i {
    margin-right: 10px;
}

/* Assign colors based on file type */
.file-item.video {
    background-color: rgba(54, 162, 235, 0.5); /* Blue for videos */
}

.file-item.excel {
    background-color: rgba(75, 192, 192, 0.5); /* Teal for Excel */
}

.file-item.word {
    background-color: rgba(153, 102, 255, 0.5); /* Purple for Word */
}

.file-item.powerpoint {
    background-color: rgba(255, 159, 64, 0.5); /* Orange for PowerPoint */
}

.file-item.pdf {
    background-color: rgba(255, 99, 132, 0.5); /* Red for PDF */
}

/* Video section */
.video-section {
    width: 70%;
    background-color: rgba(255, 255, 255, 0.1);
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
    text-align: center;
    position: relative;
}

.video-section h3 {
    margin-bottom: 20px;
    text-transform: uppercase;
    font-size: 24px;
    text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
}

/* Fullscreen icon */
.fullscreen-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 24px;
    color: #ffffff;
    cursor: pointer;
    background-color: rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    padding: 12px;
    text-align: center;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease;
}

.fullscreen-btn:hover {
    background-color: rgba(255, 255, 255, 0.8);
    color: #4b6cb7; /* Matches the gradient background color */
    transform: scale(1.1); /* Slight scaling for effect */
}

.popup {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: #393a3d; /* Semi-transparent black background */
    display: none; /* Hidden by default */
    justify-content: center;
    align-items: center;
    z-index: 9999;
}


.popup-content {
    position: relative;
    background-color: #393a3d;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
}

/* Close button styles */
.popup-close {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 28px;
    color: #fff;
    cursor: pointer;
    background-color: rgba(0, 0, 255, 0.5); /* Light blue background */
    border-radius: 50%;
    padding: 10px;
    text-align: center;
    line-height: 20px;
    width: 40px;
    height: 40px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease;
}

.popup-close:hover {
    background-color: rgba(255, 0, 0, 0.8); /* Bright red background on hover */
    color: #fff; /* White text on hover */
    transform: scale(1.1); /* Slightly scale up on hover */
}


.popup video {
    width: 100%;
    height: 100%;
    border-radius: 15px;
    object-fit: cover;
}

/* Popup close button */
.popup-close {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 28px;
    color: #ffffff;
    cursor: pointer;
    background-color: rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    padding: 12px;
    width: 45px;
    height: 45px;
    text-align: center;
    line-height: 20px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease;
    z-index: 10000; /* Ensures it stays above other elements */
}

.popup-close:hover {
    background-color: rgba(255, 255, 255, 0.8);
    color: #ff4d4d; /* Bright red for the close button */
    transform: scale(1.1); /* Slight scaling for effect */
}

/* Enlarge file preview section */
.files-viewer iframe {
    width: 100%;
    height: 600px; /* Reduced height for better layout */
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

/* Loader styles */
#loader {
    display: none;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 1.5rem;
    color: white;
    text-align: center;
}

/* Overlay for File Preview inside the iframe */
.overlay-container {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100px; /* Covers top 50px of iframe */
    background-color: #393a3d; /* Semi-transparent dark overlay */
    z-index: 10;
    display: none; /* Hidden by default */
}

/* Overlay for the popup */
.overlay-container-popup {
    position: absolute;
    top: 55px;
    left: 0;
    width: 100%;
    height: 40px;
    background-color: #393a3d;
    z-index: 1000; /* Above popup iframe */
}
.popup-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8); /* Semi-transparent black background */
    display: none; /* Hidden by default */
    justify-content: center;
    align-items: center;
    z-index: 9999; /* Always on top */
}


/* Popup container */
.popup-container {
    background-color: #393a3d;
    width: 90%;
    height: 90%; /* Adjust height for the popup */
    border-radius: 10px;
    position: relative;
    padding: 10px;
    box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.5);
}

/* Close button styles */
.close-btn {
    position: absolute;
    top: 10px;
    right: 20px;
    font-size: 30px;
    color: #ff0000;
    cursor: pointer;
}
/* Popup styles for small devices */
@media (max-width: 768px) {
    /* Adjust popup container size for smaller screens */
    .popup-container {
        width: 95%; /* A bit more space on the sides */
        height: 70%; /* Smaller height for mobile */
        padding: 5px;
        border-radius: 10px;
        margin: 10px; /* Add margin for spacing from edges */
    }

    /* Adjust the size of the iframe for smaller screens */
    .popup-container iframe {
        width: 100%;
        height: 60vh; /* Smaller height for iframe inside popup */
    }

    /* Adjust the overlay-container for smaller screens */
    .overlay-container-popup {
        top: 40px; /* Adjust the overlay to fit the smaller iframe */
        height: 30px; /* Adjust the height of the overlay for mobile */
    }

    /* Adjust the close button size for smaller screens */
    .close-btn {
        font-size: 24px;
        width: 35px;
        height: 35px;
        padding: 8px;
        top: 5px;
        right: 10px;
    }

    /* Hide the fullscreen button on mobile if necessary */
    .fullscreen-btn {
        display: none;
    }
     .main-container {
        flex-direction: column; /* Վերածել սյունային դասավորության */
        padding: 0; /* Փոքր սարքերում padding-ը հեռացվում է */
        margin: 0; /* Margin-ը փոքր սարքերում */
    }

    .files-list {
        width: 100%; /* Ֆայլերի ցուցակը լցնում է ամբողջ լայնությունը */
        margin: 0;
        height: 50vh; /* Գումարենք, որ ֆայլերի ցուցակը լինի էկրանի կեսը */
        overflow-y: auto; /* Ավելացնենք scroll եթե բովանդակությունը շատ է */
    }

    .video-section {
        width: 100%; /* File preview-ն նույնպես ամբողջ լայնությամբ */
        height: 50vh; /* File preview-ը նույնպես թող լինի էկրանի մյուս կեսը */
        margin: 0;
        padding: 10px; /* Փոքր padding */
    }

    iframe {
        height: 100%; /* Iframe-ը լցնում է իր ամբողջ section-ի բարձրությունը */
    }
     /* Վիդեո popup-ի բարձրությունը */
    .popup video {
        width: 100%;
        height: 50vh; /* Կարգավորում ենք վիդեոյի բարձրությունը հեռախոսների վրա */
        border-radius: 15px;
        object-fit: cover;
    }

    /* Ֆայլի preview-ի iframe-ը */
    .files-viewer iframe {
        width: 100%;
        height: 50vh; /* Կարգավորում ենք ֆայլի preview-ի բարձրությունը հեռախոսների վրա */
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    }

    /* File preview-ի section-ը */
    .video-section {
        width: 100%; /* Լայնությունը 100% */
        height: 50vh; /* Բարձրությունը 50vh՝ հեռախոսների վրա */
        margin: 0;
        padding: 10px;
    }

}

/* Popup styles for extra small devices */
@media (max-width: 480px) {
    /* Adjust popup container size for extra small screens */
    .popup-container {
        width: 90%; /* Even more space on the sides */
        height: 65%; /* Smaller height for extra small screens */
        padding: 5px;
        margin: 15px; /* More margin for spacing */
    }

    /* Adjust the size of the iframe for extra small screens */
    .popup-container iframe {
        width: 100%;
        height: 50vh; /* Popup-ի iframe-ի բարձրությունը հեռախոսների վրա */
    }

    /* Popup-ի overlay container */
    .overlay-container-popup {
        top: 40px; /* Adjust the overlay to fit the smaller iframe */
        height: 30px; /* Adjust the height of the overlay for mobile */
    }

    /* Adjust the close button size for extra small screens */
    .close-btn {
        font-size: 22px;
        width: 30px;
        height: 30px;
        padding: 6px;
        top: 5px;
        right: 10px;
    }

    /* Hide the fullscreen button on extra small screens if necessary */
    .fullscreen-btn {
        display: none;
    }
}
/* Elegant Popup overlay */
.elegant-popup-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.75); /* Darker transparent background */
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    backdrop-filter: blur(5px); /* Adds background blur */
}

/* Elegant Popup content */
.elegant-popup-content {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); /* Gradient background */
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0px 15px 30px rgba(0, 0, 0, 0.3); /* Deep shadow for depth */
    text-align: center;
    max-width: 450px;
    width: 100%;
    animation: slideDown 0.6s ease-out;
    position: relative;
    border: 1px solid rgba(0, 0, 0, 0.1); /* Subtle border for definition */
}

/* Elegant title styling */
.elegant-popup-title {
    font-size: 28px;
    font-weight: bold;
    color: #34495e;
    margin-bottom: 20px;
    text-transform: uppercase;
    letter-spacing: 2px;
}

/* Elegant message text */
.elegant-popup-message {
    font-size: 18px;
    color: #555;
    margin-bottom: 30px;
    line-height: 1.6;
    letter-spacing: 0.5px;
}

/* Link inside elegant popup */
.elegant-popup-link {
    color: #3498db;
    text-decoration: none;
    font-weight: bold;
    transition: color 0.3s ease;
}

.elegant-popup-link:hover {
    color: #2980b9;
}

/* Elegant button styling */
.elegant-popup-button {
    background-color: #f3ed17; 
    color: #ffffff;
    padding: 15px 35px;
    font-size: 18px;
    font-weight: bold;
    border: none;
    border-radius: 50px;
    cursor: pointer;
    box-shadow: 0px 10px 20px rgba(46, 204, 113, 0.4); /* Soft green shadow */
    transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
}

.elegant-popup-button:hover {
    background-color: #27ae60;
    transform: translateY(-3px); /* Slight lift on hover */
    box-shadow: 0px 15px 25px rgba(46, 204, 113, 0.6); /* Deeper shadow on hover */
}

/* Close button (optional) */
.elegant-popup-close {
    position: absolute;
    top: 15px;
    right: 15px;
    font-size: 22px;
    color: #333;
    cursor: pointer;
    background-color: rgba(255, 255, 255, 0.8);
    border-radius: 50%;
    padding: 8px;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.elegant-popup-close:hover {
    background-color: rgba(255, 0, 0, 0.8); /* Bright red */
    transform: scale(1.1); /* Slightly enlarges */
    color: #fff;
}

/* Animation for popup entrance */
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive Design for Popup */
@media (max-width: 768px) {
    .elegant-popup-content {
        padding: 30px;
    }

    .elegant-popup-title {
        font-size: 24px;
    }

    .elegant-popup-message {
        font-size: 16px;
    }

    .elegant-popup-button {
        padding: 12px 30px;
        font-size: 16px;
    }
}

    </style>
</head>
<body>

<!-- Video Popup -->
<div class="popup" id="videoPopup" style="display: none;">
    <div class="popup-close" onclick="closeVideoPopup()">×</div>
    <div class="popup-content">
        <video id="videoPlayer" controls controlsList="nodownload">
            <source id="videoSource" src="" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>
</div>

<div class="main-container" style="display: flex;">
    <!-- Files list -->
    <div class="files-list" style="flex: 0 0 30%; margin-right: 20px;">
        <h3>Available Lessons</h3>

        <!-- Loop through each lesson group (e.g., Letters, Music, etc.) -->
        <?php foreach ($grouped_lessons as $category => $lessons): ?>
            <!-- Թաքցնենք Book բաժինը -->
            <?php if ($category === 'Book') continue; ?> 

            <!-- Category Folder -->
            <div class="file-item" onclick="toggleSection('<?php echo $category; ?>Section')">
                <i class="fas fa-folder"></i> <?php echo $category; ?>
            </div>

            <div id="<?php echo $category; ?>Section" style="display: none; padding-left: 20px;">
                <?php if (!empty($lessons)): ?>
                    <!-- Loop through lessons within the category -->
                    <?php foreach ($lessons as $lesson): ?>
                        <!-- Each lesson becomes a folder -->
                        <div class="file-item" onclick="toggleSection('<?php echo $lesson['title']; ?>Section')" style="margin-left: 20px;">
                            <i class="fas fa-folder"></i> <?php echo $lesson['title']; ?>
                        </div>

                        <!-- Inside each lesson folder, display the corresponding files, videos, etc. -->
                        <div id="<?php echo $lesson['title']; ?>Section" style="display: none; padding-left: 20px;">

                            <!-- Display Video if exists -->
                            <?php if (!empty($lesson['video'])): ?>
                                <div class="file-item video" onclick="openVideo('<?php echo $lesson['video']; ?>')" style="margin-left: 20px;">
                                    <i class="fas fa-video"></i> Play Video
                                </div>
                            <?php endif; ?>

                            <!-- Display "Small Videos" folder if exists -->
                            <?php 
                            // Decode the small_videos JSON
                            $small_videos = json_decode($lesson['small_videos'], true); 
                            ?>
                            <?php if (!empty($small_videos)): ?>
                                <div class="file-item" onclick="toggleSection('<?php echo $lesson['title']; ?>SmallVideosSection')" style="margin-left: 20px;">
                                    <i class="fas fa-folder"></i> Small Videos
                                </div>

                                <!-- Inside "Small Videos" folder, list videos -->
                                <div id="<?php echo $lesson['title']; ?>SmallVideosSection" style="display: none; padding-left: 20px;">
                                    <?php foreach ($small_videos as $small_video): ?>
                                        <div class="file-item video" onclick="openVideo('<?php echo $small_video; ?>')" style="margin-left: 20px;">
                                            <i class="fas fa-video"></i> <?php echo basename($small_video); ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Display "View Files" folder -->
                            <?php 
                            // Decode the files JSON
                            $files = json_decode($lesson['files'], true); 
                            ?>
                            <?php if (!empty($files)): ?>
                                <div class="file-item" onclick="toggleSection('<?php echo $lesson['title']; ?>FilesSection')" style="margin-left: 20px;">
                                    <i class="fas fa-folder"></i> View Files
                                </div>

                                <!-- Inside "View Files" folder, list files -->
                                <div id="<?php echo $lesson['title']; ?>FilesSection" style="display: none; padding-left: 20px;">
                                    <?php foreach ($files as $file): ?>
                                        <div class="file-item" onclick="loadFile('<?php echo $file; ?>')" style="margin-left: 20px;">
                                            <i class="fas fa-file"></i> <?php echo basename($file); ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="file-item" style="margin-left: 20px;">No lessons available in this category.</div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

<!-- Popup shown only if `date_start_role` or `date_end_role` is NULL -->
<?php if ($showPopup): ?>
    <div class="elegant-popup-overlay">
        <div class="elegant-popup-content">
            <h2 class="elegant-popup-title">Notification</h2>
            <p class="elegant-popup-message">By clicking <strong>Start</strong>, the page becomes available to you for 366 days. 
               Upon expiry, please contact us: <a href="contact.php" class="elegant-popup-link">Contact Us</a>.</p>
            <form method="POST">
                <button type="submit" name="start" class="elegant-popup-button">Start</button>
            </form>
        </div>
    </div>
<?php endif; ?>

    

        <!-- Existing Files inside the container -->
        <h3>Additional Resources</h3>
        <div class="file-item video" onclick="openVideo('/resource/For%20teacher/Morning%20Relaxing%20Music%20-%20Positive%20Background%20Music%20for%20Kids%20(Sway).mp4')">
            <i class="fas fa-video"></i> Background Music - Morning Relaxing Music
        </div>
        <div class="file-item excel" onclick="loadFile('/resource/For%20teacher/K1_Syllabus_Program.xlsx')">
            <i class="fas fa-file-excel"></i> K1_Syllabus_Program.xlsx
        </div>
        <div class="file-item word" onclick="loadFile('/resource/For%20teacher/K1_Suggested%20Lesson%20Plan%20Order.docx')">
            <i class="fas fa-file-word"></i> K1_Suggested Lesson Plan Order.docx
        </div>
        <div class="file-item powerpoint" onclick="loadFile('/resource/For%20teacher/K1_Convorsation.pptx')">
            <i class="fas fa-file-powerpoint"></i> K1_Convorsation.pptx
        </div>
        <div class="file-item word" onclick="loadFile('/resource/For%20teacher/50%20Fun%20Games%20for%20Children.docx')">
            <i class="fas fa-file-word"></i> 50 Fun Games for Children.docx
        </div>

        <!-- Special Lessons Section inside the container -->
        <div class="file-item" onclick="toggleSection('specialLessonsSection')">
            <i class="fas fa-folder"></i> Special Lessons
        </div>
        <div id="specialLessonsSection" style="display: none; padding-left: 20px;">
            <!-- Happy Easter Folder -->
            <div class="file-item" onclick="toggleSection('happyEasterSection')">
                <i class="fas fa-folder"></i> Happy Easter
            </div>
            <div id="happyEasterSection" style="display: none; padding-left: 20px;">
                <div class="file-item pdf" onclick="loadFile('/resource/For%20teacher/Easter%20worksheet_Card.pdf')">
                    <i class="fas fa-file-pdf"></i> Easter worksheet_Card.pdf
                </div>
                <div class="file-item video" onclick="openVideo('/resource/For%20teacher/The%20Bunny%20Hokey%20Pokey%20-%20The%20Kiboomers%20Preschool%20Songs%20for%20Circle%20Time%20-%20Easter%20Song.mp4')">
                    <i class="fas fa-video"></i> The Bunny Hokey Pokey - The Kiboomers Easter Song.mp4
                </div>
                <div class="file-item pdf" onclick="loadFile('/resource/For%20teacher/The%20Bunny%20Pokey.pdf')">
                    <i class="fas fa-file-pdf"></i> The Bunny Pokey.pdf
                </div>
                <div class="file-item powerpoint" onclick="loadFile('/resource/For%20teacher/Happy%20Easter.pptx')">
                    <i class="fas fa-file-powerpoint"></i> Happy Easter.pptx
                </div>
            </div>

            <!-- Merry Christmas Folder -->
            <div class="file-item" onclick="toggleSection('merryChristmasSection')">
                <i class="fas fa-folder"></i> Merry Christmas
            </div>
            <div id="merryChristmasSection" style="display: none; padding-left: 20px;">
                <div class="file-item powerpoint" onclick="loadFile('/resource/For%20teacher/Christmas-lesson.pptx')">
                    <i class="fas fa-file-powerpoint"></i> Christmas-lesson.pptx
                </div>
                <div class="file-item video" onclick="openVideo('/resource/For%20teacher/Jingle%20Bells%20_%20Christmas%20Song%20_%20Super%20Simple%20Songs.mp4')">
                    <i class="fas fa-video"></i> Jingle Bells - Christmas Song
                </div>
                <div class="file-item pdf" onclick="loadFile('/resource/For%20teacher/Jingle+Bells+Lyrics.pdf')">
                    <i class="fas fa-file-pdf"></i> Jingle Bells Lyrics.pdf
                </div>
                <div class="file-item pdf" onclick="loadFile('/resource/For%20teacher/Christmas%20worksheet_Card.pdf')">
                    <i class="fas fa-file-pdf"></i> Christmas worksheet_Card.pdf
                </div>
            </div>
        </div>

        <!-- Resources for Teachers Section inside the container -->
        <div class="file-item" onclick="toggleSection('resourcesForTeachersSection')">
            <i class="fas fa-folder"></i> Resources for Teachers
        </div>
        <div id="resourcesForTeachersSection" style="display: none; padding-left: 20px;">
            <div class="file-item pdf" onclick="loadFile('/resource/For%20teacher/b_dialogues_everyday_conversations_english_lo_0.pdf')">
                <i class="fas fa-file-pdf"></i> Everyday Conversations in English.pdf
            </div>
            <div class="file-item pdf" onclick="loadFile('/resource/For%20teacher/Early%20Learning%20Teaching%20Strategies.pdf')">
                <i class="fas fa-file-pdf"></i> Early Learning Teaching Strategies.pdf
            </div>
            <div class="file-item pdf" onclick="loadFile('/resource/For%20teacher/high-impact-teaching-strategies.pdf')">
                <i class="fas fa-file-pdf"></i> High-Impact Teaching Strategies.pdf
            </div>
            <div class="file-item pdf" onclick="loadFile('/resource/For%20teacher/UNICEF_Learning%20through%20play.pdf')">
                <i class="fas fa-file-pdf"></i> UNICEF Learning through Play.pdf
            </div>
        </div>
    </div>

<!-- Video/File Section -->
<div class="video-section" style="flex: 0 0 70%; background-color: rgba(255, 255, 255, 0.1); padding: 20px; border-radius: 15px;">
    <h3>File Preview</h3>
    <div class="fullscreen-btn" onclick="openPopup()">
        <i class="fas fa-expand"></i>
    </div>
    <!-- Loader during file loading -->
    <div id="loader" style="display: none;">Loading: <span id="loaderText">0%</span></div>
    
    <!-- File viewer -->
    <div class="files-viewer" id="fileViewer" style="position: relative;">
        <iframe id="fileIframe" src="" onload="hideLoader()" style="width: 100%; height: 600px; border: none; position: relative;"></iframe>

        <!-- Overlay inside iframe -->
        <div class="overlay-container" id="overlay-container"></div>
    </div>
</div>

<!-- File Popup -->
<div id="popupOverlay" class="popup-overlay" style="display: none;">
    <div class="popup-container">
        <span class="close-btn" onclick="closeFilePopup()">×</span>
        <!-- Message to suggest viewing on a desktop -->
        <p id="mobileMessage" style="color: white; text-align: center; font-size: 18px; margin-bottom: 15px; display: none;">
            Please view on a computer for a better experience.
        </p>
        <!-- File viewer in popup -->
        <iframe id="popupIframe" src="" style="width: 100%; height: 80vh; border: none; display: block;"></iframe>
        <div class="overlay-container-popup"></div>
    </div>
</div>


<?php include 'footer.php'; ?>


<!-- JavaScript to handle video and file loading with progress -->
<script>
   let progressInterval;
let progress = 0;

// Loader functions for file viewer
function showLoader() {
    document.getElementById('loader').style.display = 'block';
    progress = 0;
    updateProgress();
    progressInterval = setInterval(updateProgress, 100);
}

function hideLoader() {
    clearInterval(progressInterval);
    document.getElementById('loader').style.display = 'none';
}

// Update the progress bar to simulate file loading
function updateProgress() {
    if (progress < 100) {
        progress += 1;
        document.getElementById('loaderText').innerText = progress + '%';
    } else {
        clearInterval(progressInterval);
    }
}

// Function to load files (PPTX, PDF, DOCX, XLSX) and manage overlay
function loadFile(fileSrc) {
    const fileIframe = document.getElementById('fileIframe');
    const overlayContainer = document.getElementById('overlay-container');

    // Show the loader when a file starts loading
    showLoader();

    // Detect file type and use the appropriate viewer
    if (fileSrc.endsWith('.pptx') || fileSrc.endsWith('.docx') || fileSrc.endsWith('.xlsx')) {
        // Use Office365 Viewer for Word, Excel, and PowerPoint
        const office365ViewerUrl = 'https://view.officeapps.live.com/op/view.aspx?src=';
        const fullUrl = office365ViewerUrl + encodeURIComponent(window.location.origin + '/' + fileSrc); // Ensure fileSrc has the correct path
        fileIframe.src = fullUrl;

        // Show overlay only for PowerPoint files if needed
        overlayContainer.style.display = fileSrc.endsWith('.pptx') ? 'block' : 'none';
    } else if (fileSrc.endsWith('.pdf')) {
        // Handle PDF files directly in the iframe
        fileIframe.src = fileSrc;
        overlayContainer.style.display = 'none';
    } else {
        // For other file types (e.g., videos)
        fileIframe.src = fileSrc;
        overlayContainer.style.display = 'none';
    }

    // Once the file is loaded, hide the loader
    fileIframe.onload = function() {
        hideLoader();
        
        // Handle overlay for PowerPoint files
        if (fileSrc.endsWith('.pptx')) {
            document.querySelector('.overlay-container-popup').style.display = 'block';
        } else {
            document.querySelector('.overlay-container-popup').style.display = 'none';
        }
    };

    // If there's an error loading the file, hide the loader and display an error message
    fileIframe.onerror = function() {
        hideLoader();
        alert('Failed to load the file. Please make sure the file path is correct and try again.');
    };
}



// Function to load PPTX files and manage overlay
function loadPptx(fileSrc) {
    const fileIframe = document.getElementById('fileIframe');
    const overlayContainer = document.getElementById('overlay-container');

    // Բացել Office 365 Viewer-ով
    const office365ViewerUrl = 'https://view.officeapps.live.com/op/view.aspx?src=';
    const fullUrl = office365ViewerUrl + encodeURIComponent(fileSrc);
    
    // Set the iframe src to the Office 365 Viewer URL
    fileIframe.src = fullUrl;

    // Արգելափակել աջ կոճակի մենյուն (context menu)
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });

    // Wait for iframe to load the PPTX file
    fileIframe.onload = function() {
        // Only show overlay if the file is a .pptx file
        if (fileSrc.endsWith('.pptx')) {
            // Show the overlay-container for PPTX files
            overlayContainer.style.display = 'block';
        } else {
            // Hide overlay for other file types
            overlayContainer.style.display = 'none';
        }
    };
}

// Function to hide the overlay for other file types
function hideOverlay() {
    const overlayContainer = document.getElementById('overlay-container');
    overlayContainer.style.display = 'none';
}


// Video Popup Functions
function openVideo(videoSrc) {
    const videoSource = document.getElementById('videoSource');
    const videoPopup = document.getElementById('videoPopup');
    const videoPlayer = document.getElementById('videoPlayer');

    videoSource.src = videoSrc;
    videoPlayer.load(); // Reload the video source
    videoPopup.style.display = 'flex'; // Show video popup
}

function closeVideoPopup() {
    const videoPopup = document.getElementById('videoPopup');
    const videoPlayer = document.getElementById('videoPlayer');
    
    videoPopup.style.display = 'none'; // Hide video popup
    videoPlayer.pause(); // Stop the video playback when closing
    videoPlayer.currentTime = 0; // Reset video to start
}



// Toggle sections visibility
function toggleSection(sectionId) {
    const section = document.getElementById(sectionId);
    section.style.display = section.style.display === 'none' ? 'block' : 'none';
}

// Function to open the file popup
function openFilePopup() {
    const popupOverlay = document.getElementById('popupOverlay');
    const iframeSrc = document.getElementById('fileIframe').src;

    // Set iframe src in the popup to match the original iframe
    document.getElementById('popupIframe').src = iframeSrc;

    // Check if the opened file is a pptx
    if (iframeSrc.includes('.pptx')) {
        // Show the overlay if it's a pptx
        document.querySelector('.overlay-container-popup').style.display = 'block';
    } else {
        // Hide the overlay for non-pptx files
        document.querySelector('.overlay-container-popup').style.display = 'none';
    }

    // Display the file popup
    popupOverlay.style.display = 'flex';
}

// Function to close the file popup
function closeFilePopup() {
    const popupOverlay = document.getElementById('popupOverlay');
    
    // Hide the file popup
    popupOverlay.style.display = 'none';
}

// Fullscreen function for file preview in iframe
function toggleFullscreen(iframeId) {
    const iframe = document.getElementById(iframeId);
    
    if (iframe.requestFullscreen) {
        iframe.requestFullscreen();
    } else if (iframe.mozRequestFullScreen) { // Firefox
        iframe.mozRequestFullScreen();
    } else if (iframe.webkitRequestFullscreen) { // Chrome, Safari, and Opera
        iframe.webkitRequestFullscreen();
    } else if (iframe.msRequestFullscreen) { // IE/Edge
        iframe.msRequestFullscreen();
    }
}
// Function to detect if the user is on a mobile or tablet device
function isMobileDevice() {
    return /Mobi|Android|iPad|iPhone/.test(navigator.userAgent);
}

// Function to handle popup display based on the device
function openPopup() {
    const popupOverlay = document.getElementById('popupOverlay');
    const iframeSrc = document.getElementById('fileIframe').src;
    const mobileMessage = document.getElementById('mobileMessage');
    const popupIframe = document.getElementById('popupIframe');

    if (isMobileDevice()) {
        // If the user is on mobile, show the message and hide the iframe
        mobileMessage.style.display = 'block';
        popupIframe.style.display = 'none';
    } else {
        // If the user is on a desktop, hide the message and show the iframe
        mobileMessage.style.display = 'none';
        popupIframe.src = iframeSrc;
        popupIframe.style.display = 'block';
    }
    popupOverlay.style.display = 'flex'; // Show the popup
}

// Function to close the file popup
function closeFilePopup() {
    const popupOverlay = document.getElementById('popupOverlay');
    popupOverlay.style.display = 'none'; // Hide the popup
}
</script>

</body>
</html>

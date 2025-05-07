<?php
session_start();
include 'db_connect.php';
require_once 'constants.php';

// Display errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}

// Fetch the list of lessons
$lessonsResult = $conn->query("SELECT id, title, image, tag, video, small_videos, files, date_created FROM lessons ORDER BY id ASC");

// Fetch the list of users
$usersResult = $conn->query("SELECT id, email FROM users");

// Set upload directory for video files
$uploadDir = UPLOAD_DIR . 'uploads/videos/';

// Check if the small_videos array exists
$smallVideos = isset($_FILES['small_videos']) ? $_FILES['small_videos'] : null;

if (!empty($smallVideos['name']) && isset($_POST['lesson_id'])) {
    $lessonId = $_POST['lesson_id'];
    
    // Check if small_videos field is an array and has values
    if (is_array($smallVideos['name']) && !empty(array_filter($smallVideos['name']))) {
        $videoPaths = [];
        foreach ($smallVideos['name'] as $key => $video) {
            $videoName = str_replace(' ', '_', basename($smallVideos['name'][$key]));
            $savePath = $uploadDir . $videoName;
            $videoPath = MEDIA_BASE_URL_FOR_DB . "uploads/videos/" . $videoName;

            if (move_uploaded_file($smallVideos['tmp_name'][$key], $savePath)) {
                $videoPaths[] = $videoPath;
            }
        }

        // Update small_videos in the database
        if (!empty($videoPaths)) {
            $smallVideosJson = json_encode($videoPaths);
            $stmt = $conn->prepare("UPDATE lessons SET small_videos = ? WHERE id = ?");
            $stmt->bind_param("si", $smallVideosJson, $lessonId);
            if ($stmt->execute()) {
                echo "<script>alert('Small videos uploaded successfully!');</script>";
            } else {
                echo "<script>alert('Failed to update small videos in the database.');</script>";
            }
            $stmt->close();
        }
    } else {
        echo "<script>alert('No small videos uploaded.');</script>";
    }
}

// Close the database connection
$conn->close();
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Lessons</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .card {
            border-radius: 10px;
        }

        .card-header {
            background-color: #007bff;
            color: white;
            border-radius: 10px 10px 0 0;
            font-size: 18px;
        }

        .btn-custom {
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .btn-custom:hover {
            background-color: #0056b3;
        }

        .table-striped>tbody>tr:nth-of-type(odd) {
            --bs-table-accent-bg: #e9ecef;
            color: #495057;
        }

        .navbar {
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .add-lesson-btn {
            background-color: #28a745;
            color: white;
            margin-bottom: 20px;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .add-lesson-btn:hover {
            background-color: #218838;
        }

        .lesson-image {
            max-width: 100px;
            height: auto;
        }

        .btn-public {
            background-color: #ff7f50;
            color: white;
            border-radius: 8px;
            padding: 10px 20px;
            margin-left: 10px;
            font-size: 16px;
            transition: background-color 0.3s ease;
            box-shadow: 0 4px 8px rgba(255, 127, 80, 0.4);
        }

        .btn-public:hover {
            background-color: #ff6347;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
            margin-left: 10px;
            border-radius: 5px;
            font-size: 16px;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        .modal-body {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .modal-video {
            flex: 0 0 60%;
            margin-right: 10px;
        }

        .modal-files {
            flex: 0 0 35%;
        }

        .file-btn {
            display: block;
            width: 100%;
            margin-bottom: 10px;
            padding: 10px;
            text-align: center;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease, transform 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.4);
        }

        .file-btn:hover {
            background-color: #0056b3;
            transform: translateY(-5px);
        }

        .modal-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .modal-tag {
            font-size: 14px;
            color: gray;
        }

        .video-container {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            max-width: 100%;
            background-color: #000;
        }

        .video-container video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .modal-dialog {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 90vh;
        }

        .edit-form input,
        .edit-form textarea {
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

    <!-- Header inclusion -->
    <?php include 'headeradmin.php'; ?>

    <div class="content">
        <div class="content-header">
            <h1>Lessons</h1>
            <p>Manage and view lessons here.</p>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Lessons List</div>
                    <div class="card-body">
                        <!-- Add Lesson Button and Public Button -->
                        <button class="add-lesson-btn" data-bs-toggle="modal" data-bs-target="#addLessonModal">+ Add
                            Lesson</button>
                        <button class="btn-public" data-bs-toggle="modal"
                            data-bs-target="#publicLessonModal">Public</button>
                        <button class="btn btn-info" data-bs-toggle="modal"
                            data-bs-target="#updateUserLessonsModal">Update user_lessons</button>

                        <?php if ($lessonsResult && $lessonsResult->num_rows > 0): ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Image</th>
                                    <th scope="col">Title</th>
                                    <th scope="col">Tag</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($lesson = $lessonsResult->fetch_assoc()): ?>
                                <tr>
                                    <th scope="row">
                                        <?php echo $lesson['id']; ?>
                                    </th>
                                    <td><img src="<?php echo $lesson['image']; ?>" alt="Lesson Image"
                                            class="lesson-image"></td>
                                    <td>
                                        <?php echo $lesson['title']; ?>
                                    </td>
                                    <td>
                                        <?php echo $lesson['tag']; ?>
                                    </td>
                                    <td>
                                        <!-- View button -->
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#viewLessonModal<?php echo $lesson['id']; ?>">View</button>
                                        <!-- Edit button -->
                                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#editLessonModal<?php echo $lesson['id']; ?>">Edit</button>
                                        <!-- Delete button -->
                                        <a href="delete_lesson.php?id=<?php echo $lesson['id']; ?>"
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this lesson?')">Delete</a>
                                        <!-- Manage button -->
                                        <button class="btn btn-success btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#manageLessonModal<?php echo $lesson['id']; ?>">Manage</button>
                                        <!-- Duplicate button -->
                                        <a href="duplicate_lesson.php?id=<?php echo $lesson['id']; ?>"
                                            class="btn btn-sm btn-secondary">Duplicate</a>
                                    </td>

                                </tr>

                                <!-- Update user_lessons Modal -->
                                <div class="modal fade" id="updateUserLessonsModal" tabindex="-1"
                                    aria-labelledby="updateUserLessonsModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="updateUserLessonsModalLabel">Update
                                                    user_lessons</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Do you want to update the user_lessons table with the latest changes?
                                                </p>
                                                <form action="update_user_lessons.php" method="POST">
                                                    <button type="submit" class="btn btn-info">Update</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal for viewing lesson -->
                                <div class="modal fade" id="viewLessonModal<?php echo $lesson['id']; ?>" tabindex="-1"
                                    aria-labelledby="viewLessonModalLabel<?php echo $lesson['id']; ?>"
                                    aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <div>
                                                    <h5 class="modal-title"
                                                        id="viewLessonModalLabel<?php echo $lesson['id']; ?>">
                                                        <?php echo $lesson['title']; ?>
                                                    </h5>
                                                    <p class="modal-tag">
                                                        <?php echo $lesson['tag']; ?>
                                                    </p>
                                                </div>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <!-- Main Video section -->
                                                <div class="modal-video">
                                                    <?php if ($lesson['video']): ?>
                                                    <div class="video-container">
                                                        <video id="lessonVideo<?php echo $lesson['id']; ?>" controls
                                                            controlslist="nodownload">
                                                            <source src="<?php echo $lesson['video']; ?>"
                                                                type="video/mp4">
                                                            Your browser does not support the video tag.
                                                        </video>
                                                    </div>
                                                    <?php endif; ?>
                                                    <button class="btn btn-secondary"
                                                        onclick="restoreMainVideo('<?php echo $lesson['video']; ?>', 'lessonVideo<?php echo $lesson['id']; ?>')">Restore
                                                        Main Video</button>
                                                </div>

                                                <!-- Files section -->
                                                <div class="modal-files">
                                                    <?php if ($lesson['files']): ?>
                                                    <h6>Downloadable Files:</h6>
                                                    <?php
                    $files = json_decode($lesson['files'], true);
                    foreach ($files as $file) {
                        echo '<a href="' . $file . '" class="file-btn" download>' . basename($file) . '</a>';
                    }
                    ?>
                                                    <?php else: ?>
                                                    <p>No files available for download.</p>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Other Videos section -->
                                                <div class="modal-other-videos"
                                                    style="display: flex; flex-wrap: wrap; gap: 10px;">
                                                    <h6>Other Videos:</h6>
                                                    <?php if ($lesson['small_videos']): ?>
                                                    <?php
    $smallVideos = json_decode($lesson['small_videos'], true);
    foreach ($smallVideos as $smallVideo) {
        echo "<div class='small-video-item' onclick=\"changeVideoSource('$smallVideo', 'lessonVideo{$lesson['id']}')\" style='border: 2px solid #ccc; border-radius: 8px; overflow: hidden; width: 150px; height: auto;'>";
        echo "<video src='$smallVideo' width='100%' height='auto' muted preload='metadata' style='background-color: black;' onloadedmetadata=\"this.currentTime = 30\"></video>";
        echo "</div>";
    }
    ?>
                                                    <?php else: ?>
                                                    <p>No small videos available.</p>
                                                    <?php endif; ?>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>




                                <!-- Modal for editing lesson -->
                                <div class="modal fade" id="editLessonModal<?php echo $lesson['id']; ?>" tabindex="-1"
                                    aria-labelledby="editLessonModalLabel<?php echo $lesson['id']; ?>"
                                    aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"
                                                    id="editLessonModalLabel<?php echo $lesson['id']; ?>">Edit Lesson
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form id="editLessonForm<?php echo $lesson['id']; ?>"
                                                    action="edit_lesson_handler.php" method="post" class="edit-form"
                                                    enctype="multipart/form-data">
                                                    <input type="hidden" name="id" value="<?php echo $lesson['id']; ?>">

                                                    <!-- Title Field -->
                                                    <div class="mb-3">
                                                        <label for="title<?php echo $lesson['id']; ?>"
                                                            class="form-label">Title</label>
                                                        <input type="text" name="title" class="form-control"
                                                            id="title<?php echo $lesson['id']; ?>"
                                                            value="<?php echo $lesson['title']; ?>">
                                                    </div>

                                                    <!-- Tag Field -->
                                                    <div class="mb-3">
                                                        <label for="tag<?php echo $lesson['id']; ?>"
                                                            class="form-label">Tag</label>
                                                        <input type="text" name="tag" class="form-control"
                                                            id="tag<?php echo $lesson['id']; ?>"
                                                            value="<?php echo $lesson['tag']; ?>">
                                                    </div>

                                                    <!-- Image Upload -->
                                                    <div class="mb-3">
                                                        <label for="image<?php echo $lesson['id']; ?>"
                                                            class="form-label">Image</label>
                                                        <input type="file" name="image" class="form-control"
                                                            id="image<?php echo $lesson['id']; ?>">
                                                    </div>

                                                    <!-- Main Video Upload -->
                                                    <div class="mb-3">
                                                        <label for="video<?php echo $lesson['id']; ?>"
                                                            class="form-label">Main Video</label>
                                                        <input type="file" name="video" class="form-control"
                                                            id="video<?php echo $lesson['id']; ?>"
                                                            onchange="autoUploadVideo(this, '<?php echo $lesson['id']; ?>')">
                                                    </div>

                                                    <!-- Small Videos Upload -->
                                                    <div class="mb-3">
                                                        <label for="small_videos<?php echo $lesson['id']; ?>"
                                                            class="form-label">Small Videos</label>
                                                        <input type="file" name="small_videos[]" class="form-control"
                                                            id="small_videos<?php echo $lesson['id']; ?>" multiple>
                                                        <small class="text-muted">You can upload multiple small
                                                            videos.</small>
                                                    </div>

                                                    <!-- Files Upload -->
                                                    <div class="mb-3">
                                                        <label for="files<?php echo $lesson['id']; ?>"
                                                            class="form-label">Files</label>
                                                        <input type="file" name="files[]" class="form-control"
                                                            id="files<?php echo $lesson['id']; ?>" multiple>
                                                    </div>

                                                    <!-- Progress Bar -->
                                                    <progress id="progressBar<?php echo $lesson['id']; ?>" value="0"
                                                        max="100" style="width:100%;"></progress>
                                                    <p id="status<?php echo $lesson['id']; ?>"></p>
                                                    <p id="progressPercent<?php echo $lesson['id']; ?>"></p>

                                                    <!-- Save Changes Button -->
                                                    <button type="submit" class="btn btn-success">Save changes</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <!-- Modal for managing lesson -->
                                <div class="modal fade" id="manageLessonModal<?php echo $lesson['id']; ?>" tabindex="-1"
                                    aria-labelledby="manageLessonModalLabel<?php echo $lesson['id']; ?>"
                                    aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"
                                                    id="manageLessonModalLabel<?php echo $lesson['id']; ?>">Manage
                                                    Lesson</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <!-- Right side (Activation) -->
                                                    <div class="col-md-6">
                                                        <h6>Activation</h6>
                                                        <form action="activate_lesson.php" method="post">
                                                            <input type="hidden" name="lesson_id"
                                                                value="<?php echo $lesson['id']; ?>">
                                                            <div class="mb-3">
                                                                <label for="userIdInput<?php echo $lesson['id']; ?>"
                                                                    class="form-label">Enter User ID</label>
                                                                <input type="text" class="form-control"
                                                                    id="userIdInput<?php echo $lesson['id']; ?>"
                                                                    name="user_id" placeholder="Enter user ID">
                                                            </div>
                                                            <button type="submit"
                                                                class="btn btn-primary">Activate</button>
                                                        </form>
                                                    </div>

                                                    <!-- Left side (Public) -->
                                                    <div class="col-md-6">
                                                        <h6>Public</h6>
                                                        <form action="make_public_lesson.php" method="post">
                                                            <input type="hidden" name="lesson_id"
                                                                value="<?php echo $lesson['id']; ?>">
                                                            <div class="mb-3">
                                                                <label
                                                                    for="publicUserIdInput<?php echo $lesson['id']; ?>"
                                                                    class="form-label">Enter Public User ID</label>
                                                                <input type="text" class="form-control"
                                                                    id="publicUserIdInput<?php echo $lesson['id']; ?>"
                                                                    name="public_user_id"
                                                                    placeholder="Enter public user ID">
                                                            </div>
                                                            <button type="submit" class="btn btn-success">Add to
                                                                Public</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <p>No lessons found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Lesson Modal -->
    <div class="modal fade" id="addLessonModal" tabindex="-1" aria-labelledby="addLessonModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addLessonModalLabel">Add Lesson</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="process_add_lesson.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="lessonTitle" class="form-label">Lesson Title</label>
                            <input type="text" class="form-control" id="lessonTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="lessonTag" class="form-label">Tag</label>
                            <input type="text" class="form-control" id="lessonTag" name="tag" required>
                        </div>
                        <div class="mb-3">
                            <label for="lessonImage" class="form-label">Upload Image</label>
                            <input type="file" class="form-control" id="lessonImage" name="image" accept="image/*"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="lessonVideo" class="form-label">Upload Video</label>
                            <input type="file" class="form-control" id="lessonVideo" name="video" accept="video/*"
                                onchange="showProgressBar(this)">
                        </div>
                        <div class="progress mt-3" style="display: none;">
                            <div class="progress-bar" role="progressbar" style="width: 0%;"></div>
                        </div>
                        <div class="mb-3">
                            <label for="lessonFiles" class="form-label">Upload Files (Optional)</label>
                            <input type="file" class="form-control" id="lessonFile1" name="file1"
                                accept=".pdf,.pptx,.docx">
                            <input type="file" class="form-control mt-2" id="lessonFile2" name="file2"
                                accept=".pdf,.pptx,.docx">
                            <input type="file" class="form-control mt-2" id="lessonFile3" name="file3"
                                accept=".pdf,.pptx,.docx">
                            <input type="file" class="form-control mt-2" id="lessonFile4" name="file4"
                                accept=".pdf,.pptx,.docx">
                        </div>
                        <button type="submit" class="btn btn-primary">Add Lesson</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Public Lesson Modal -->
    <div class="modal fade" id="publicLessonModal" tabindex="-1" aria-labelledby="publicLessonModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="publicLessonModalLabel">Make Lesson Public</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="make_public_lesson.php" method="POST">
                        <div class="mb-3">
                            <label for="lessonId" class="form-label">Lesson ID</label>
                            <input type="text" class="form-control" id="lessonId" name="lesson_id"
                                placeholder="Enter Lesson ID">
                        </div>
                        <div class="mb-3">
                            <label for="userId" class="form-label">User ID</label>
                            <input type="text" class="form-control" id="userId" name="user_id"
                                placeholder="Enter User ID">
                        </div>
                        <button type="submit" class="btn btn-success">Make Public</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for auto-upload video -->
    <script>
        function autoUploadVideo(input, lessonId) {
            var formData = new FormData();
            formData.append('video', input.files[0]);
            formData.append('id', lessonId);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'auto_upload_video.php', true);

            xhr.upload.onprogress = function (event) {
                if (event.lengthComputable) {
                    var percentComplete = Math.round((event.loaded / event.total) * 100);
                    document.getElementById('progressBar' + lessonId).value = percentComplete;
                    document.getElementById('progressPercent' + lessonId).innerHTML = percentComplete + '% uploaded';
                }
            };

            xhr.onload = function () {
                if (xhr.status == 200) {
                    document.getElementById('status' + lessonId).innerHTML = 'Video uploaded successfully!';
                } else {
                    document.getElementById('status' + lessonId).innerHTML = 'Video upload failed!';
                }
            };

            xhr.send(formData);
        }

        function showProgressBar(input) {
            const progressBar = document.querySelector('.progress');
            const progressFill = document.querySelector('.progress-bar');

            progressBar.style.display = 'block';

            const file = input.files[0];
            const formData = new FormData();
            formData.append('video', file);

            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'auto_upload_video.php', true);

            xhr.upload.onprogress = function (e) {
                if (e.lengthComputable) {
                    const percentComplete = Math.round((e.loaded / e.total) * 100);
                    progressFill.style.width = percentComplete + '%';
                }
            };

            xhr.onload = function () {
                if (xhr.status === 200) {
                    progressFill.style.width = '100%';
                    progressBar.innerHTML = 'Video uploaded successfully!';
                } else {
                    progressBar.innerHTML = 'Video upload failed!';
                }
            };

            xhr.send(formData);
        }

        // JavaScript to stop video when modal is closed
        document.querySelectorAll('.modal').forEach(function (modal) {
            modal.addEventListener('hidden.bs.modal', function () {
                const video = modal.querySelector('video');
                if (video) {
                    video.pause();
                    video.currentTime = 0;
                }
            });
        });

        function updateUserLessons() {
            if (confirm("Are you sure you want to update the user lessons?")) {
                // Create a form dynamically
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = 'update_user_lessons.php';

                document.body.appendChild(form);
                form.submit();
            }
        }

        <!-- JavaScript for switching the main video -->
        function switchMainVideo(newVideoSrc, lessonId) {
            const mainVideo = document.getElementById('lessonVideo' + lessonId);
            const mainVideoSource = document.getElementById('mainVideoSource' + lessonId);
            mainVideoSource.src = newVideoSrc;
            mainVideo.load();
            mainVideo.play();
        }

        function changeVideoSource(newSource, videoElementId) {
            var videoElement = document.getElementById(videoElementId);
            videoElement.src = newSource;
            videoElement.play();
        }

        function restoreMainVideo(mainVideoSource, videoElementId) {
            var videoElement = document.getElementById(videoElementId);
            videoElement.src = mainVideoSource;
            videoElement.play();
        }
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
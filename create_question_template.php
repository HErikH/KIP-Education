<?php
// Include database connection and admin header
include 'db_connect.php';
include 'headeradmin.php'; // Including the headeradmin.php file

// Retrieve the quiz ID from the URL
$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

// Check if the quiz ID is valid
if ($quiz_id <= 0) {
    die("Invalid Quiz ID");
}

// Fetch quiz details if necessary (optional for context)
$stmt = $conn->prepare("SELECT title FROM quizzes WHERE id = ?");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$stmt->bind_result($quizTitle);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"> <!-- Ensure UTF-8 -->
    <title>Create New Question - <?= htmlspecialchars($quizTitle) ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="stylequestion.css">
    <style>
        /* Custom styles for icon cards */
        .icon-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid #ccc;
            border-radius: 10px;
            text-align: center;
            padding: 20px;
            height: 250px; /* Set fixed height */
        }

        .icon-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            border-color: #007bff; /* Change border color on hover */
        }

        .icon-card .material-icons {
            font-size: 50px;
            color: #007bff; /* Icon color */
        }

        .icon-card h5 {
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <div id="successMessage" class="alert alert-success" role="alert" style="display: none;">
        New question added successfully!
    </div>

    <!-- Heading: Create New Question -->
    <h1>Create New Question</h1>

    <!-- Icon Text Containers -->
    <div class="row text-center mt-4">
        <div class="col-md-6 mb-4">
            <div class="icon-card">
                <span class="material-icons">check_circle</span>
                <h5>Checking</h5>
                <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#checkModal">View Form</button>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="icon-card">
                <span class="material-icons">radio_button_checked</span>
                <h5>Radio</h5>
                <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#radioModal">View Form</button>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="icon-card">
                <span class="material-icons">image</span>
                <h5>Image Select</h5>
                <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#imageSelectModal">View Form</button>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="icon-card">
                <span class="material-icons">list</span>
                <h5>Select</h5>
                <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#selectModal">View Form</button>
            </div>
        </div>
    </div>

<!-- Checking Modal -->
<div class="modal fade" id="checkModal" tabindex="-1" aria-labelledby="checkModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="checkModalLabel">Checking Options</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Checking form content -->
                <div class="form-group check-option-group" id="checkOptionGroup1" style="margin-bottom: 20px;">
                    <!-- Question Title Input -->
                    <label for="questionTitle">Question Title</label>
                   <input type="text" class="form-control" name="questionTitle" id="questionTitle" placeholder="Enter your question title" required>


<div class="d-flex align-items-center mb-3">
    <!-- Image Upload Button and Input -->
    <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('uploadImage').click()" style="margin-right: 5px;">Image</button>
    <input type="file" class="form-control-file" id="uploadImage" name="image" accept="image/*" onchange="previewImage(event)" style="display: none;">
    
    <!-- Music Upload Button and Input -->
    <button type="button" class="btn btn-success btn-sm" onclick="document.getElementById('uploadMusic').click()" style="margin-right: 5px;">Music</button>
    <input type="file" class="form-control-file" id="uploadMusic" name="audio" accept="audio/*" onchange="previewMusic(event)" style="display: none;">
    
    <!-- Video Upload Button and Input -->
    <button type="button" class="btn btn-danger btn-sm" onclick="document.getElementById('uploadVideo').click()" style="margin-right: 5px;">Video</button>
    <input type="file" class="form-control-file" id="uploadVideo" name="video" accept="video/*" onchange="previewVideo(event)" style="display: none;">
</div>

<!-- Image Preview -->
<div class="image-preview" id="imagePreview" style="display: none; margin-top: 10px;">
    <img id="previewImg" src="" alt="Image Preview" style="max-width: 100%; height: auto; border: 1px solid #ccc; border-radius: 5px;"/>
    <span class="remove-image" style="color: red; cursor: pointer; display: inline; margin-top: 5px;" onclick="removeImage()">×</span>
</div>

<!-- Music Preview -->
<div class="music-preview" id="musicPreview" style="display: none; margin-top: 10px;">
    <audio id="previewAudio" controls style="width: 100%;"></audio>
    <span class="remove-music" style="color: red; cursor: pointer; display: inline; margin-top: 5px;" onclick="removeMusic()">×</span>
</div>

<!-- Video Preview -->
<div class="video-preview" id="videoPreview" style="display: none; margin-top: 10px;">
    <video id="previewVideo" controls style="max-width: 100%; height: auto; border: 1px solid #ccc; border-radius: 5px;"></video>
    <span class="remove-video" style="color: red; cursor: pointer; display: inline; margin-top: 5px;" onclick="removeVideo()">×</span>
</div>


                    <!-- Option Input and Actions -->
                    <div id="optionList">
                        <div class="option-container mt-3" style="border: 1px solid #ccc; border-radius: 5px; padding: 10px; display: flex; align-items: center; margin-top: 10px;">
                            <input type="text" class="form-control check-option-text" name="options[]" placeholder="Option 1" style="margin-bottom: 0; width: auto; flex-grow: 1;" required>

                            <!-- Checkbox toggle -->
                            <span class="toggle-check" style="cursor: pointer; display: inline-flex; align-items: center; margin-left: 10px;" onclick="toggleCheck(this)">
                                <i class="material-icons check-icon unchecked" style="color: red;">close</i>
                                <input type="hidden" class="check-status" name="checkStatus[]" value="false">
                            </span>

                            <!-- Action buttons -->
                            <span class="field-actions" style="display: inline-flex; align-items: center; margin-left: 10px;">
                                <div class="icon-container" style="width: 30px; height: 30px; border: 1px solid #ccc; border-radius: 5px; display: flex; justify-content: center; align-items: center; margin-right: 5px;">
                                    <i class="material-icons add-field" style="cursor: pointer; color: blue;" onclick="addField()">add</i>
                                </div>
                                <div class="icon-container remove-field-container" style="width: 30px; height: 30px; border: 1px solid #ccc; border-radius: 5px; display: flex; justify-content: center; align-items: center; margin-right: 5px;">
                                    <i class="material-icons remove-field" style="cursor: pointer; color: red;" onclick="removeField(this)">delete</i>
                                </div>
                                <div class="icon-container" style="width: 30px; height: 30px; border: 1px solid #ccc; border-radius: 5px; display: flex; justify-content: center; align-items: center; margin-right: 5px;">
                                    <i class="material-icons move-up" style="cursor: pointer; color: green;" onclick="moveUp(this)">arrow_upward</i>
                                </div>
                                <div class="icon-container" style="width: 30px; height: 30px; border: 1px solid #ccc; border-radius: 5px; display: flex; justify-content: center; align-items: center;">
                                    <i class="material-icons move-down" style="cursor: pointer; color: green;" onclick="moveDown(this)">arrow_downward</i>
                                </div>
                            </span>
                        </div>
                    </div>

                    <!-- Full Width Create Question Button -->
                    <form action="submit_question_check.php?quiz_id=<?= $quiz_id ?>" method="POST" id="createQuestionForm" enctype="multipart/form-data">
    <!-- Form fields here -->
    <button type="submit" class="btn btn-primary btn-block" style="margin-top: 20px; width: 100%; padding: 15px; font-size: 18px;">Create Question</button>
</form>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>



    <div class="modal fade" id="radioModal" tabindex="-1" aria-labelledby="radioModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="radioModalLabel">Radio Options</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Include the radio_option.php content here -->
                    <?php include 'radio_option.php'; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="imageSelectModal" tabindex="-1" aria-labelledby="imageSelectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageSelectModalLabel">Image Select Options</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Include the imageselect_option.php content here -->
                    <?php include 'imageselect_option.php'; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="selectModal" tabindex="-1" aria-labelledby="selectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="selectModalLabel">Select Options</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Include the select_option.php content here -->
                    <?php include 'select_option.php'; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="scriptquestion.js"></script>
</body>
</html>

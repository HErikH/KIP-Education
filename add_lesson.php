<?php
session_start();
include 'db_connect.php';

// Проверка сессии
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Lesson</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #007bff;
            color: white;
            border-radius: 10px 10px 0 0;
            font-size: 18px;
            font-weight: bold;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .btn-custom {
            background-color: #28a745;
            color: white;
            border-radius: 5px;
            padding: 10px 20px;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.4);
        }
        .btn-custom:hover {
            background-color: #218838;
            transform: translateY(-3px);
        }
        .preview {
            margin-top: 0;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            height: 100%;
        }
        .preview video, .preview img {
            width: 100%;
            height: auto;
            object-fit: cover;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        .content {
            display: flex;
            justify-content: space-between;
            flex-wrap: nowrap;
            align-items: flex-start;
        }
        .form-section {
            width: 60%;
            margin-right: 20px;
        }
        .preview-section {
            width: 35%;
        }
        @media (max-width: 1200px) {
            .form-section, .preview-section {
                width: 100%;
                margin-right: 0;
            }
            .content {
                flex-direction: column;
            }
            .preview {
                margin-top: 30px;
            }
        }
        .loading-spinner {
            display: none;
            margin-top: 10px;
        }
        .progress {
            height: 15px;
            background-color: #e9ecef;
            border-radius: 10px;
            margin-top: 15px;
            display: none;
        }
        .progress-bar {
            width: 0%;
            height: 100%;
            background-color: #28a745;
            border-radius: 10px;
            transition: width 0.4s ease;
        }
    </style>
</head>
<body>

    <!-- Включение header -->
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="row">
            <div class="col-md-7">
                <div class="card mt-5">
                    <div class="card-header">Add New Lesson</div>
                    <div class="card-body">
                        <form id="lessonForm" method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="title">Title</label>
                                <input type="text" name="title" id="title" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="tag">Tag</label>
                                <input type="text" name="tag" id="tag" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="image">Upload Image</label>
                                <input type="file" name="image" id="image" class="form-control" accept="image/*">
                            </div>
                            <div class="form-group">
                                <label for="video">Upload Video</label>
                                <input type="file" name="video" id="video" class="form-control" accept="video/*">
                            </div>
                            <div class="progress mt-3" style="display: none;">
                                <div class="progress-bar" role="progressbar" style="width: 0%;"></div>
                            </div>
                            <div class="form-group mt-3">
                                <label for="file1">Upload Files</label>
                                <input type="file" name="file1" id="file1" class="form-control" accept=".pdf,.pptx,.docx">
                                <input type="file" name="file2" id="file2" class="form-control mt-2" accept=".pdf,.pptx,.docx">
                                <input type="file" name="file3" id="file3" class="form-control mt-2" accept=".pdf,.pptx,.docx">
                                <input type="file" name="file4" id="file4" class="form-control mt-2" accept=".pdf,.pptx,.docx">
                            </div>
                            <button type="button" class="btn btn-custom mt-3" id="submitLesson">Submit</button>
                            <div class="loading-spinner spinner-border text-primary mt-3" role="status" style="display: none;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="preview-section mt-5">
                    <div class="card">
                        <div class="card-header">Preview</div>
                        <div class="card-body preview" id="previewContent">
                            <video controls style="display: none;" width="100%" height="auto"></video>
                            <img src="" alt="Uploaded Image" style="display: none; width: 100%; height: auto;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Preview the uploaded image, video, and files -->
    <script>
        document.getElementById('submitLesson').addEventListener('click', function (e) {
            e.preventDefault(); // Останавливаем стандартное поведение формы

            const form = document.getElementById('lessonForm');
            const formData = new FormData(form);

            const xhr = new XMLHttpRequest();
            const progressBar = document.querySelector('.progress-bar');
            const progressContainer = document.querySelector('.progress');
            const spinner = document.querySelector('.loading-spinner');

            // Показать прогресс-бар и спиннер
            progressContainer.style.display = 'block';
            spinner.style.display = 'block';

            xhr.upload.addEventListener('progress', function (e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    progressBar.style.width = percentComplete + '%';
                }
            });

            xhr.onload = function () {
                spinner.style.display = 'none';
                if (xhr.status === 200) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Lesson Added!',
                        text: 'Your lesson has been successfully added.',
                    }).then(() => {
                        window.location.href = 'lessons.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'There was an error adding the lesson.',
                    });
                }
            };

            xhr.onerror = function () {
                spinner.style.display = 'none';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred during the upload.',
                });
            };

            xhr.open('POST', 'process_add_lesson.php', true);
            xhr.send(formData);
        });
    </script>

</body>
</html>

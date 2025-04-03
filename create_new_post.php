<?php
include 'db_connect.php';
include 'headeradmin.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Blog Post</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Quill CSS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            margin-top: 50px;
        }
        #editor {
            height: 1000px;
        }
        .preview-container {
            position: relative;
            display: inline-block;
        }
        .preview-container img {
            max-width: 100px;
            max-height: 100px;
            margin-top: 10px;
        }
        .remove-preview {
            position: absolute;
            top: 0;
            right: 0;
            background: red;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center mb-4">Create a New Blog Post</h2>
    
    <form action="submit_blog.php" method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="title" class="form-label">Blog Title</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>

        <!-- Blog Image Upload -->
        <div class="mb-3">
            <label for="image" class="form-label">Blog Image</label>
            <input type="file" class="form-control" id="image" name="image" accept="image/*">
            <div id="previewContainer" class="preview-container" style="display:none;">
                <img id="imagePreview" src="" alt="Image Preview">
                <button type="button" class="remove-preview" onclick="removeImage()">x</button>
            </div>
        </div>

        <div class="mb-3">
            <label for="content" class="form-label">Content</label>
            <!-- Quill-ի խմբագրիչի տարր -->
            <div id="editor"></div>
            <!-- Hidden input field to store the editor content -->
            <input type="hidden" id="content" name="content">
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Quill JS -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<!-- Quill Initialization -->
<script>
    var quill = new Quill('#editor', {
        theme: 'snow',
        placeholder: 'Write your blog content here...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, false] }],
                ['bold', 'italic', 'underline'],
                ['link', 'image', 'video'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['clean']
            ]
        }
    });

    // Store the editor content in a hidden input field before form submission
    document.querySelector('form').onsubmit = function() {
        document.querySelector('#content').value = quill.root.innerHTML;
    };
</script>

<!-- Image Preview and Remove Functionality -->
<script>
    document.getElementById('image').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imagePreview').src = e.target.result;
                document.getElementById('previewContainer').style.display = 'inline-block';
            };
            reader.readAsDataURL(file);
        }
    });

    function removeImage() {
        // Clear the file input value
        document.getElementById('image').value = '';
        // Hide the preview container
        document.getElementById('previewContainer').style.display = 'none';
        // Remove the preview image src
        document.getElementById('imagePreview').src = '';
    }
</script>

</body>
</html>

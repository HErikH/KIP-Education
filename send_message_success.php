<?php
    require_once 'helpers.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?= addMediaBaseUrl('resource/img/favicon.png') ?>" type="image/png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <title>Message sent successfully</title>
    <style>
        html {
            position: relative;
            overflow-x: hidden;
        }

        body {
            height: 100vh;
            background: linear-gradient(90deg, #4b6cb7 0%, #182848 100%);
            color: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .header {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            background-color: #182848;
            color: #ffffff;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            min-height: 65px;
        }

        .left-section {
            display: flex;
            align-items: center;
        }

        .exit-link {
            color: #ffffff;
            text-decoration: none;
            font-size: 16px;
            margin-right: 20px;
            cursor: pointer;
        }

        .exit-link:hover {
            color: #ff9800;
            text-decoration: none;
        }

        .exit-link i {
            margin-right: 8px;
        }

        .main-content {
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .message-success {
            color: #f3ed17;
        }
    </style>
</head>

<body>
    <header class="header">
        <div class="left-section">
            <a onclick="location.replace('contact')" class="exit-link">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </header>
    <div class="main-content">
        <h1 class="message-success">Message sent successfully !</h1>
    </div>
    <?php include 'footer.php'; ?>
</body>

</html>
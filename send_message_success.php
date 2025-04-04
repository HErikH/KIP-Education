<?php
include 'headerchild.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="resource/img/favicon.png" type="image/png">
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

        .leaderboard-btn {
            visibility: hidden;
        }
    </style>
</head>

<body>
    <div class="main-content">
        <h1 class="message-success">Message sent successfully !</h1>
    </div>
    <?php include 'footer.php'; ?>
</body>

</html>
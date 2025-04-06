<?php
session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KIP Education - Lessons</title>
    <link rel="icon" href="resource/img/favicon.png" type="image/png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> <!-- Font Awesome Icons -->
    <style>
        html {
            position: relative;
            overflow-x: hidden;
        }
        /* Body styling */
        body {
            background: linear-gradient(90deg, #4b6cb7 0%, #182848 100%);
            color: white;
            display: flex;
            flex-direction: column; 
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative;
            overflow-x: hidden;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 60px;
            border-radius: 15px;
            box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 700px;
            text-align: center;
            z-index: 2;
            position: relative;
        }

        .container h1 {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .container h2 {
            font-size: 28px;
            font-weight: 400;
            margin-bottom: 40px;
            color: rgba(255, 255, 255, 0.8);
        }

        .my-account-btn {
            background-color: #3498db;
            color: white;
            padding: 15px 50px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            font-size: 18px;
            transition: background-color 0.3s ease;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            display: inline-block;
        }

        .my-account-btn:hover {
            background-color: #2980b9;
        }

        /* Left-side image positioned absolutely */
        .left-image {
            position: absolute;
            left: 0;
            bottom: 0;
            height: 90%;
            width: auto;
            z-index: 1;
        }

        /* Right-side boy image positioned absolutely */
.right-image {
    position: absolute;
    right: -300px; /* Նախկինում -160px էր, ավելի ձախ բերենք */
    bottom: 0;
    height: 90%; /* Նույն չափ */
    width: auto;
    z-index: 1; /* Պահում ենք նկարը մյուսների վրայից */
}


        /* Hide images on devices with a width of 1024px or less */
        @media (max-width: 1024px) {
            .left-image, .right-image {
                display: none;
            }
        }

        /* Responsive adjustments for tablet devices */
        @media (max-width: 1024px) {
            .container {
                padding: 40px;
                max-width: 600px;
            }

            .container h1 {
                font-size: 32px;
            }

            .container h2 {
                font-size: 24px;
            }

            .my-account-btn {
                padding: 12px 40px;
                font-size: 16px;
            }
        }

        /* Responsive adjustments for mobile devices */
        @media (max-width: 768px) {
            .container {
                padding: 30px;
                max-width: 90%;
            }

            .container h1 {
                font-size: 28px;
            }

            .container h2 {
                font-size: 22px;
            }

            .my-account-btn {
                padding: 10px 30px;
                font-size: 14px;
            }
        }

        @media (max-width: 576px) {
            .container {
                padding: 20px;
                max-width: 90%;
            }

            .container h1 {
                font-size: 24px;
            }

            .container h2 {
                font-size: 20px;
            }

            .my-account-btn {
                padding: 8px 20px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>

    <?php include "header.php"; ?>

    <div class="container">
        <h1>Welcome to</h1>
        <h2>KIP Education and Training ID Center</h2>
        <p>Education is power, a developed child is a bright future...</p>
        <a href="#" class="my-account-btn" id="myAccountBtnMain">
            MY ACCOUNT
        </a>
    </div>

    <img src="resource/img/girl.webp" alt="Girl Image" class="left-image">
    <img src="resource/img/boy.webp" alt="Boy Image" class="right-image"> <!-- Boy image added here -->

    <?php include "footer.php"; ?>

    <script>
        // Redirects to profile or login page based on session state
        document.getElementById("myAccountBtnMain").onclick = function() {
            <?php if (
              isset($_SESSION["loggedin"]) &&
              $_SESSION["loggedin"] === true &&
              isset($_SESSION["user_id"])
            ): ?>
                window.location.href = "profile?id=<?php echo $_SESSION[
                  "user_id"
                ]; ?>";
            <?php else: ?>
                window.location.href = "login";
            <?php endif; ?>
        };
    </script>

</body>
</html>

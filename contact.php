<?php
session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - KIP Education</title>
    <link rel="icon" href="resource/img/favicon.png" type="image/png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> <!-- Font Awesome Icons -->
    <style>
        body {
            background: linear-gradient(90deg, #4b6cb7 0%, #182848 100%);
            color: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        /* Contact Page Layout */
        .contact-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin-top: 130px;
            padding: 20px;
            flex-grow: 1;
            gap: 20px;
            width: 80%;
            min-height: 500px;
        }

        .contact-form, .contact-info {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 600px;
        }

        .contact-form h3, .contact-info h3 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            border-radius: 5px;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.3);
        }

        .btn-submit {
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-submit:hover {
            background-color: #2980b9;
        }

        .contact-info p {
            font-size: 16px;
            margin-bottom: 10px;
        }

        .contact-info i {
            color: #3498db;
            margin-right: 10px;
        }

        .contact-info img {
            width: 100px;
            margin-bottom: 20px;
        }

        .social-icons {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            justify-items: center;
            margin-top: 20px;
        }

        .social-icons a {
            color: white;
            font-size: 24px;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 60px;
            height: 60px;
            border: 2px solid white;
            border-radius: 50%;
            background-color: transparent;
            text-decoration: none;
            transition: transform 0.3s ease, background-color 0.3s ease, border-color 0.3s ease;
        }

        .social-icons a:hover {
            transform: scale(1.1);
            background-color: rgba(255, 255, 255, 0.2);
            border-color: white;
        }

        .social-icons a i {
            color: white;
            margin-left: 10px;
        }

        /* Telegram */
        .social-icons a:hover .telegram {
          color: #0088cc;
        }

        /* Facebook */
        .social-icons a:hover .facebook {
          color: #1877f2;
        }

        /* Instagram */
        .social-icons a:hover .instagram {
          color: #e4405f; 
        }

        /* YouTube */
        .social-icons a:hover .youtube {
          color: #ff0000;
        }

        /* TikTok */
        .social-icons a:hover .tiktok {
          color: #010101;
        }

        /* WeChat */
        .social-icons a:hover .wechat {
          color: #07c160; 
        }

        /* Gmail */
        .social-icons a:hover .gmail {
          color: #ea4335; 
        }

        footer {
            text-align: center;
            padding: 20px;
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            margin-top: 40px;
            width: 100%;
        }

        @media (min-width: 769px) {
            .contact-container {
                flex-direction: row;
                justify-content: center;
                margin-top: 180px;
                gap: 20px;
            }

            .contact-form, .contact-info {
                width: 45%;
            }
        }

        @media (max-width: 768px) {
            .contact-container {
                flex-direction: column;
            }

            .social-icons {
                grid-template-columns: repeat(3, 1fr);
                gap: 15px;
            }

            .contact-form, .contact-info {
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <!-- Include Header -->
    <?php include "header.php"; ?>

    <!-- Contact Page Layout -->
    <div class="contact-container">
        <!-- Contact Form -->
        <div class="contact-form">
            <h3>Contact Us</h3>
            <form action="send_message.php" method="POST">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Your Name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Your Email" required>
                </div>
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" class="form-control" rows="6" placeholder="Your Message" required></textarea>
                </div>
                <button type="submit" class="btn-submit">Send Message</button>
            </form>
        </div>

        <!-- Contact Info -->
        <div class="contact-info">
            <img src="resource/img/logo.png" alt="KIP Education Logo">
            <h3>Contact Information</h3>
            <p><i class="fas fa-phone"></i> +374 33 348889</p>
            <p><i class="fas fa-envelope"></i> <a href="mailto:kip.edu.center@gmail.com" style="color:white;">kip.edu.center@gmail.com</a></p>
            <p><i class="fas fa-map-marker-alt"></i> Yerevan, Armenia</p>
            <p><i class="fas fa-clock"></i> Mon - Fri: 9:00 AM - 6:00 PM</p>

            <!-- Social Media Icons -->
            <div class="social-icons">
                <a href="https://www.facebook.com/profile.php?id=61556910637179&mibextid=ZbWKwL" target="_blank"><i class="fab fa-facebook-f facebook"></i></a>
                <a href="https://www.instagram.com/kip.edu_center/profilecard/?igsh=MTd4aDcxZXYyb2I0eA==" target="_blank"><i class="fab fa-instagram instagram"></i></a>
                <a href="https://t.me/kipeducenter" target="_blank"><i class="fab fa-telegram telegram"></i></a>
                <a href="https://youtube.com/@kipeducationandtrainingidcente?si=LtVca2znsVvF5fo-" target="_blank"><i class="fab fa-youtube youtube"></i></a>
                <a href="https://vm.tiktok.com/ZS2TWe4qr/" target="_blank"><i class="fab fa-tiktok tiktok"></i></a>
                <a href="https://u.wechat.com/kLkhkJMI_tCGNDNh_dOXWUo?s=0" target="_blank"><i class="fab fa-weixin wechat"></i></a>
                <a href="mailto:kip.edu.center@gmail.com"><i class="fas fa-envelope gmail"></i></a>
            </div>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include "footer.php"; ?>

    <script>
        document.getElementById("myAccountBtn").onclick = function() {
            <?php if (
              isset($_SESSION["loggedin"]) &&
              $_SESSION["loggedin"] === true &&
              isset($_SESSION["user_id"])
            ): ?>
                window.location.href = "profile.php?id=<?php echo $_SESSION[
                  "user_id"
                ]; ?>";
            <?php else: ?>
                window.location.href = "login.php";
            <?php endif; ?>
        };
    </script>

</body>
</html>

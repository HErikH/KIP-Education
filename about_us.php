<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="resource/img/favicon.png" type="image/png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <title>About Us</title>

    <style>
        body {
            background: linear-gradient(90deg, #4b6cb7 0%, #182848 100%);
            /* #FDB827 */
            color: #F3A953;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        body::after {
            content: "";
            display: block;
            height: 100px;
        }

        .hero-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('/resource/img/kipMeet.jpg') center center / cover no-repeat;
            filter: blur(5px);
            z-index: -1;
            opacity: 0.6;
        }

        .about-us {
            margin-top: 10rem;
            /* display: flex;
            justify-content: center;
            align-items: center;
            text-align: center; */
        }

        .section {
            margin-bottom: 3rem;
        }

        .section-title {
            border-left: 5px solid #007BFF;
            padding-left: 1rem;
            margin-bottom: 1.5rem;
        }

        .intelligences-section .section-title {
            background-color: white;
            border-radius: 0 10px 10px 0;
            padding: 15px;
            cursor: pointer;
        }

        .products-section {
            position: relative;
            border-radius: 10px;
            /* background: url('resource/img/kipMeet2.jpg') center center / cover no-repeat; */
        }

        .card {
            border-radius: 10px;
            transition: all 0.3s ease;
            color: #050315;
        }

        .card:hover {
            transform: translateY(-5px);
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .toggle-icon {
            cursor: pointer;
            color: #007BFF;
            transition: transform 0.3s ease;
        }

        .intelligences-section .section-title[aria-expanded="true"] .toggle-icon {
            transform: rotate(90deg);
        }

        .table th,
        .table td {
            vertical-align: middle;
        }
    </style>
</head>

<body>
    <!-- Include Header -->
    <?php include "header.php"; ?>

    <div class="about-us container py-5">
        <div class="hero-bg"></div>
        <!-- Header -->
        <div class="text-center mb-5">
            <h1 class="display-4 text-primary">KIP Education and Training ID Center</h1>
            <p class="lead">Learning through Multiple Intelligences & the Five Senses</p>
        </div>

        <!-- Who We Are -->
        <div class="section">
            <h3 class="section-title">Who We Are</h3>
            <p>KIP develops English education programs based on Dr. Howard Gardner's Theory of Multiple Intelligences,
                integrating the five senses, inspired by Harvard University research.</p>
            <p>We also offer training for English teachers to apply these principles in the classroom.</p>
        </div>

        <!-- Our Approach -->
        <div class="section">
            <h3 class="section-title">Our Approach</h3>
            <p>Our programs go beyond language instruction by fostering critical thinking, reasoning, expression, and
                teamwork. Every student is unique and has different learning strengths. We aim to engage students
                through their dominant intelligence.</p>
        </div>

        <!-- Multiple Intelligences -->
        <?php include "multiple_intelligences.php"?>

        <!-- Programs -->
        <?php include "programs_we_offer.php"?>

        <!-- Products Section with Background -->
        <?php include "our_products.php"?>

        <!-- English Language Programs Structure -->
        <?php include "english_programs_structure.php"?>

        <!-- Group Online Lessons -->
        <?php include "group_lessons.php"?>

        <!-- Private Online Lessons -->
        <?php include "private_lessons.php"?>

        <!-- Learn Alone Lessons -->
        <?php include "learn_alone.php"?>

    </div>
    <!-- Include Footer -->
    <?php include "footer.php"; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx"
        crossorigin="anonymous"></script>
</body>

</html>
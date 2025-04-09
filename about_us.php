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
        <div class="section intelligences-section">
            <h3 class="section-title d-flex justify-content-between align-items-center" data-toggle="collapse"
                data-target="#intelligences" aria-expanded="false" aria-controls="intelligences">
                Multiple Intelligences
                <span class="toggle-icon">&#9654;</span>
            </h3>

            <div class="collapse" id="intelligences">
                <div class="row">
                    <?php
                $intelligences = [
                  "Linguistic" => "Language use (reading, writing, speaking).",
                  "Logical-Mathematical" => "Reasoning, problem-solving.",
                  "Spatial" => "Visual and spatial awareness.",
                  "Musical" => "Sensitivity to sound and rhythm.",
                  "Bodily-Kinesthetic" => "Physical coordination.",
                  "Interpersonal" => "Understanding others.",
                  "Intrapersonal" => "Self-awareness.",
                  "Naturalistic" => "Understanding nature and patterns."
                ];
    
                foreach ($intelligences as $title => $desc) {
                  echo "
                    <div class='col-md-6 mb-3'>
                      <div class='card h-100 shadow-sm'>
                        <div class='card-body'>
                          <h5 class='card-title text-primary'>$title Intelligence</h5>
                          <p class='card-text'>$desc</p>
                        </div>
                      </div>
                    </div>
                  ";
                }
              ?>
                </div>
            </div>
        </div>

        <!-- Programs -->
        <div class="section">
            <h3 class="section-title">Programs We Offer</h3>
            <div class="row">
                <?php
              $programs = [
                "Children Ages 3-6 (K1)" => "Playful English curriculum to spark early curiosity.",
                "Children Ages 7-11 (K2)" => "Fun & dynamic English to build fluency and creativity.",
                "Teenagers Ages 12-17 (TA1)" => "Comprehensive English for academic success and confidence.",
                "Adults (TA2)" => "Practical English for personal and career growth.",
                "Teacher Training" => "Training specialists in line with labor market and MI theory."
              ];
    
              foreach ($programs as $title => $desc) {
                echo "
                  <div class='col-md-6 mb-3'>
                    <div class='card h-100 border-0 shadow-sm'>
                      <div class='card-body'>
                        <h5 class='card-title text-primary'>$title</h5>
                        <p class='card-text'>$desc</p>
                      </div>
                    </div>
                  </div>
                ";
              }
            ?>
            </div>
        </div>

        <!-- Products Section with Background -->
        <section class="products-section py-5">
            <div class="container position-relative" style="z-index: 2;">
                <h3 class="text-center mb-5 text-primary font-weight-bold">Our Products</h3>
                <div class="row">

                    <!-- Product Card 1 -->
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="fas fa-video fa-3x text-primary"></i>
                                </div>
                                <h5 class="card-title">Video Lessons</h5>
                                <p class="card-text">Engaging, interactive video lessons for independent learning at
                                    your own pace.</p>
                                <a href="programms" class="btn btn-outline-primary mt-3">Learn More</a>
                            </div>
                        </div>
                    </div>

                    <!-- Product Card 2 -->
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="fas fa-globe fa-3x text-primary"></i>
                                </div>
                                <h5 class="card-title">Online Lessons</h5>
                                <p class="card-text">Live international classes (K1, K2, TA1, TA2) twice a week — 1 hour
                                    each.</p>
                                <a href="#online-course-details" class="btn btn-outline-primary mt-3">Learn More</a>
                            </div>
                        </div>
                    </div>

                    <!-- Product Card 3 -->
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="fas fa-school fa-3x text-primary"></i>
                                </div>
                                <h5 class="card-title">Institutional Packages</h5>
                                <p class="card-text">Comprehensive English development programs for educational
                                    institutions.</p>
                                <a href="programms" class="btn btn-outline-primary mt-3">Learn More</a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- English Language Programs Structure -->
        <section class="py-5 bg-light rounded">
            <div class="container">
                <h3 class="text-center mb-5 text-primary font-weight-bold">English Language Programs Structure</h3>

                <!-- Core Sections Table -->
                <div class="mb-5">
                    <h5 class="text-dark mb-3">Each class integrates 6 and 7 core sections for a comprehensive learning
                        experience:</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped bg-white">
                            <thead class="thead-light">
                                <tr>
                                    <th>Class</th>
                                    <th>Sections</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>K1, K2</strong></td>
                                    <td>Phonetics, Grammar, Art, Culture, Music, Linguistics, Logic-Mathematics,
                                        Book/Reading</td>
                                </tr>
                                <tr>
                                    <td><strong>TA1, TA2</strong></td>
                                    <td>Grammar, Art, Culture, Music, Logic-Math, Nature, Emotional Intelligence</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Online Course Details -->
                <div id="online-course-details" class="mb-4">
                    <h5 class="text-dark mb-3">Online Course Details:</h5>
                    <ul class="list-unstyled ml-3">
                        <li>• <strong>Children (K1, K2):</strong> 64 lessons over 9 months, twice a week, 1-hour
                            sessions.</li>
                        <li>• <strong>Teenagers & Adults (TA1, TA2):</strong> Levels A0–C2, 64 lessons per level, twice
                            a week, 1-hour sessions.</li>
                    </ul>
                </div>

                <!-- Program Breakdown Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover bg-white">
                        <thead class="thead-light">
                            <tr>
                                <th>Age Group</th>
                                <th>Course Level</th>
                                <th>Number of Lessons</th>
                                <th>Duration</th>
                                <th>Frequency</th>
                                <th>Session Length</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Children (Ages 3-6)</td>
                                <td>K1</td>
                                <td>64 lessons + 2 bonus lessons</td>
                                <td>9 months</td>
                                <td>Twice a week</td>
                                <td>1 hour</td>
                            </tr>
                            <tr>
                                <td>Children (Ages 7-11)</td>
                                <td>K2</td>
                                <td>64 lessons + 2 bonus lessons</td>
                                <td>9 months</td>
                                <td>Twice a week</td>
                                <td>1 hour</td>
                            </tr>
                            <tr>
                                <td>Teenagers (Ages 12-17)</td>
                                <td>TA1</td>
                                <td>64 lessons + 2 bonus lessons</td>
                                <td>9 months</td>
                                <td>Twice a week</td>
                                <td>1 hour</td>
                            </tr>
                            <tr>
                                <td>Adults</td>
                                <td>TA2</td>
                                <td>15 Units per level</td>
                                <td>2 months</td>
                                <td>Twice a week</td>
                                <td>1 hour</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </section>
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
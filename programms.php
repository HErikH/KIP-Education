<?php
// Include the database connection
include 'db_connect.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // Start session to track user login state

// Check if the user is logged in and user_id is set
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Prepare query to fetch the user's data
$query = "SELECT email, first_last_name, phone_number FROM users WHERE id = ?";
$stmt = $conn->prepare($query);

if ($stmt === false) {
    die("Error preparing the query: " . $conn->error);
}

if ($userId !== null) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
    } else {
        $user_data = []; // User not found, initialize as empty array
    }
} else {
    $user_data = []; // No user is logged in
}

// Fallback values for user_data
$user_data['first_last_name'] = $user_data['first_last_name'] ?? '';
$user_data['phone_number'] = $user_data['phone_number'] ?? '';

// Now fetch the products from the products table, including type column
$sql = "SELECT id, title, price_month, price_year, information, `group`, price, type FROM products";
$result = $conn->query($sql);

if ($result === false) {
    die("Error fetching products: " . $conn->error);
}

$products = []; // Initialize the products array
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row; // Populate the products array
    }
} else {
    // Handle case where no products are found
    echo "<p>No products available at this time.</p>";
}

// Close the prepared statement and database connection
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KIP Education - Programs</title>
    <link rel="icon" href="resource/img/favicon.png" type="image/png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
body {
    background: linear-gradient(90deg, #4b6cb7 0%, #182848 100%);
    color: white;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 0;
    overflow-x: hidden;
    /* Adjust this value to increase or decrease the space */
    /* margin-bottom: 100px;  */


}


@media (min-width: 1024px) {
    body {
        margin-top: 150px; /* Add 50px of space at the top for desktop screens */
    }
}


/* Program Boxes Styling */
.program-container {
    margin-top: 120px;
    display: flex;
    justify-content: center; /* Center the boxes horizontally within the container */
    padding: 0 10px;
    gap: 10px; /* Set gap between program boxes to 10px for desktop */
    flex-wrap: wrap;
    width: 80%; /* Set the width to 80% of the viewport */
    margin-left: auto;
    margin-right: auto; /* Center the container */
}

/* Adjust the size of program boxes to fit 5 in a row */
.program-box {
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 15px;
    padding: 20px;
    text-align: center;
    display: flex;
    justify-content: center; /* Horizontally center content */
    align-items: center; /* Vertically center content */
    transition: transform 0.3s ease, background-color 0.3s ease;
    box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.3);
    width: 20%; /* Adjusted width to fit 5 boxes in a row */
    cursor: pointer;
    margin: 5px; /* Reduced margin to ensure 10px distance between boxes on desktop */
}


/* Media Query for small screens (mobile) */
@media (max-width: 768px) {
    .program-container {
        flex-direction: column;
        align-items: center; /* Center align the boxes */
        gap: 5px; /* Reduced gap for mobile devices */
        width: 100%; /* Full width for mobile */
    }

    .program-box {
        width: 100%; /* Full width for mobile */
        max-width: 90%; /* Add some padding from the edges */
        margin-bottom: 10px; /* Add space between the boxes on mobile */
    }
}

.program-box.active {
    background-color: rgba(255, 255, 255, 0.2);
    transform: scale(1.05);
    box-shadow: 0px 8px 20px rgba(255, 255, 255, 0.4);
    border: 2px solid #ffffff;
}

.program-box h3 {
    font-size: 18px;
    color: #ffffff;
}

.card-container {
    margin-top: 30px;
    display: flex;
        justify-content: center; /* Center the cards horizontally */
    flex-wrap: wrap;
    gap: 5px; /* Reduced gap between the cards */
    width: 100%; /* Card container width set to 90% */
    margin-left: auto;
    margin-right: auto; /* Center the container */
}

/* Card Styling */
.card {
    position: relative;
    background-color: rgba(255, 255, 255, 0.1);
    padding: 30px;
    border-radius: 15px;
    margin: 5px; /* Reduced margin for less space between cards */
    width: 18%; /* Adjusted width to fit 5 cards, accounting for the reduced margin */
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    min-height: 350px; /* Fixed height for all cards */
}

.card:hover {
    transform: translateY(-10px);
    box-shadow: 0px 8px 20px rgba(255, 255, 255, 0.5); /* Added white shadow on hover */
}

.card h4 {
    color: white;
    font-size: 22px;
    margin-bottom: 10px;
}

.card .price {
    font-size: 20px;
    color: #f3ed17;
    margin-bottom: 40px;
    display: flex; 
    justify-content: center;
    flex-wrap: wrap; 
}

.card p {
    color: rgba(255, 255, 255, 0.8);
    font-size: 14px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
}

.card p i {
    color: #f3ed17;
    margin-right: 8px;
}

/* Buy button fixed to the bottom of the card */
.buy-btn {
    background-color: #3498db;
    color: white;
    padding: 10px 50px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: bold;
    transition: background-color 0.3s ease;
    position: absolute;
    bottom: 10px; /* Fix button to bottom */
    left: 50%;
    transform: translateX(-50%);
}

.buy-btn:hover {
    background-color: #2980b9;
}

/* Mobile adjustments for card stacking */
@media (max-width: 768px) {
    .card-container {
        flex-direction: column;
        align-items: center;
        width: 90%; /* Ensure the card container is 90% on mobile */
    }

    .card {
        width: 90%; /* Make the cards 90% of the width on mobile */
        margin: 10px 0; /* Reduced margin between cards */
    }
}


        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            display: none;
            z-index: 1000;
        }

        .popup {
            background: linear-gradient(90deg, #4b6cb7 0%, #182848 100%);
            padding: 30px;
            border-radius: 10px;
            width: 800px;
            max-width: 100%;
            display: flex;
            justify-content: space-between;
            color: white;
            position: relative;
        }

        .popup-close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
            color: white;
        }

        .popup-left {
            width: 40%;
            background-color: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
        }

        .popup-left h3 {
            font-size: 22px;
            margin-bottom: 20px;
        }

        .popup-left p {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .popup-right {
            width: 55%;
        }
        .payment-methods img {
    width: 60px; /* Փոխեք սա ըստ Ձեր ցանկության */
    height: auto; /* Կպահի պատկերի հարաբերակցությունը */
    margin-right: 10px; /* Տարածություն նկարների միջև */
}

/* Popup Message Styling */
#popup-message {
    font-size: 14px; /* Smaller font size */
    color: red;
    margin-top: 10px; /* Spacing from other elements */
    text-align: center;
}

/* Adjustments for Small Screens */
@media (max-width: 768px) {
    .popup {
        max-width: 100%; /* Full width on small screens */
    }

    .popup-left h3 {
        font-size: 16px; /* Adjusted for mobile */
    }

    .popup-left p {
        font-size: 14px; /* Smaller font size for mobile */
    }

    .btn-confirm {
        padding: 10px 20px; /* Adjusted padding */
        font-size: 16px; /* Smaller button text */
    }
}

/* Աչքի նշանների դիրքավորում և գույն */
.eye-icon {
    position: absolute;
    right: 10px;
    top: 73%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #3498db; /* Փոխեք գույնը */
    font-size: 18px; /* Փոքր փոփոխություն գույնի */
}

/* Input դաշտի համար՝ հարաբերական դիրքավորում */
.form-group.position-relative {
    position: relative;
}
.btn-confirm {
    background-color: #f3ed17; /* Կանաչ ֆոն */
    color: white; /* Տեքստի սպիտակ գույն */
    padding: 12px 30px; /* Լավագույն padding */
    border-radius: 50px; /* Կլորացրած անկյուններ */
    font-size: 18px; /* Տեքստի չափ */
    font-weight: bold; /* Տեքստի հաստություն */
    text-transform: uppercase; /* Տեքստը մեծատառ դարձնել */
    border: none; /* Սահման չկա */
    box-shadow: 0px 6px 12px rgba(0, 0, 0, 0.2); /* Թեթև ստվեր */
    transition: background-color 0.3s ease, transform 0.3s ease; /* Անիմացիա */
    cursor: pointer; /* Ցուցիչը դառնում է ձեռքի նշան */
    margin-top: 10px;
    width: 100%; /* Լայնությունը՝ ամբողջ դաշտի */
}

.btn-confirm:hover {
    background-color: #27ae60; /* Մուգ կանաչ երբ մկնիկով նշված է */
    transform: translateY(-2px); /* Մի փոքր բարձրացնում է կոճակը */
}

.btn-confirm:active {
    background-color: #1e8449; /* Շատ մուգ կանաչ սեղմման ժամանակ */
    transform: translateY(0); /* Վերադարձնում է իր տեղը */
}

@media (max-width: 768px) {
    .popup {
        flex-direction: column; /* Ուղղահայաց դառնալու համար */
        max-width: 100%; /* Լրիվ լայնություն փոքր էկրանների համար */
    }

    .popup-left {
        width: 100%; /* Հորիզոնական լայնությունը ամբողջ էկրանով */
        margin-bottom: 20px; /* Տարածություն */
    }

    .popup-right {
        width: 100%; /* Հորիզոնական լայնությունը ամբողջ էկրանով */
        display: flex;
        flex-direction: column; /* Դարձնում ենք ուղղահայաց */
    }

    .btn-confirm {
        order: -1; /* Կոճակը տեղափոխում է վերև */
        margin-bottom: 20px; /* Տարածություն ներքևում */
    }
}
.teacher-packages-title {
    font-size: 22px;
    font-weight: 700;
    text-align: center;
    color: #fff;
    margin-top: 50px;
    margin-bottom: 20px;
    line-height: 1.4;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    padding: 15px 20px;
    background: rgba(0, 0, 0, 0.2);
    border-radius: 10px;
    box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.4);

    width: 70%; /* Set width to 70% */
    margin-left: auto; /* Center alignment */
    margin-right: auto; /* Center alignment */
}
.highlight {
    color: #f1c40f; /* Yellow color */
}
/* Card2 Styling */
.card2 {
    position: relative;
    background-color: rgba(255, 255, 255, 0.1);
    padding: 30px;
    border-radius: 15px;
    margin: 5px; /* Reduced margin for less space between cards */
    width: 22%; /* Adjusted width to fit cards with a slightly larger width */
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    min-height: 350px; /* Fixed height for all cards */
}

.card2:hover {
    transform: translateY(-10px);
    box-shadow: 0px 8px 20px rgba(255, 255, 255, 0.5); /* Added white shadow on hover */
}

.card2 h4 {
    color: white;
    font-size: 22px;
    margin-bottom: 10px;
}

.card2 .price {
    font-size: 20px;
    color: #f3ed17;
    margin-bottom: 40px;
    display: flex; 
    justify-content: center;
    flex-wrap: wrap; 
}

.card2 p {
    color: rgba(255, 255, 255, 0.8);
    font-size: 14px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
}

.card2 p i {
    color: #f3ed17;
    margin-right: 8px;
}
/* Mobile adjustments for card2 stacking */
@media (max-width: 768px) {

    .card2 {
        width: 90%; /* Make the cards 90% of the width on mobile */
        margin: 10px 0; /* Reduced margin between cards */
    }
}

    </style>
</head>
<body>

<?php
// Include the header
include 'header.php';
?>


<!-- Programs Container -->
<div class="program-container">
    <!-- Program 1 -->
    <div class="program-box" id="k1" onclick="showProgramDetails('k1')">
        <h3>Children ages 3-6 / K1</h3>
    </div>

    <!-- Program 2 -->
    <div class="program-box" id="k2" onclick="showProgramDetails('k2')">
        <h3>Children ages 7-11 / K2</h3>
    </div>

    <!-- Program 3 -->
    <div class="program-box" id="ta1" onclick="showProgramDetails('ta1')">
        <h3>Teenagers ages 12-17 / TA1</h3>
    </div>

    <!-- Program 4 -->
    <div class="program-box" id="ta2" onclick="showProgramDetails('ta2')">
        <h3>Adults / TA2</h3>
    </div>
</div>

<!-- Card Container for Program Details -->
<div class="card-container" id="k1-cards" style="display: none;">
    <?php foreach ($products as $product): ?>
        <?php if ($product['group'] == 'Children ages 3-6 /K1/'): ?>
        <div class="card">
            <!-- Convert title to uppercase and set font weight -->
            <h4 style="text-transform: uppercase; font-weight: 600;"><?= htmlspecialchars($product['title']) ?></h4>
            
            <!-- Display the monthly or yearly price with smaller .00 AMD and Monthly/Yearly text -->
            <p class="price" style="font-size: 28px; font-weight: bold;">
                <?= $product['price_month'] != '0.00' ? '<span>' . strtok(htmlspecialchars($product['price_month']), '.') . '</span><span style="font-size: 16px; vertical-align: super;">.00 AMD</span> <span style="font-size: 14px; vertical-align: sub;">/Monthly</span>' : '' ?>
                <?= $product['price_year'] != '0.00' ? '<span>' . strtok(htmlspecialchars($product['price_year']), '.') . '</span><span style="font-size: 16px; vertical-align: super;">.00 AMD</span> <span style="font-size: 14px; vertical-align: sub;">/Yearly</span>' : '' ?>
                <?= $product['price_month'] == '0.00' && $product['price_year'] == '0.00' ? '<span>' . strtok(htmlspecialchars($product['price']), '.') . '</span><span style="font-size: 16px; vertical-align: super;">.00 AMD</span>' : '' ?>
            </p>

            <!-- Display information with checkmarks, aligned to the left -->
            <p>
                <?php 
                $infoLines = explode("\n", htmlspecialchars($product['information'])); 
                ?>
                <ul style="list-style-type: none; padding-left: 0; text-align: left;"> <!-- Added left alignment -->
                    <?php foreach ($infoLines as $line): ?>
                        <li style="margin-bottom: 5px; text-transform: capitalize;"><span style="color: #f3ed17;">&#10003;</span> <?= $line ?></li>
                    <?php endforeach; ?>
                </ul>
            </p>

            <!-- Commented out Group, Monthly Price, and Yearly Price (not visible on the website) -->
            <!--
            <p><strong>Group:</strong> <?= htmlspecialchars($product['group']) ?></p>
            <p><strong>Monthly Price:</strong> <?= htmlspecialchars($product['price_month']) ?> AMD</p>
            <p><strong>Yearly Price:</strong> <?= htmlspecialchars($product['price_year']) ?> AMD</p>
            -->

            <a href="#" class="buy-btn" 
   data-title="<?= htmlspecialchars($product['title']) ?>"
   data-price="<?= htmlspecialchars($product['price']) ?>"
   data-information="<?= htmlspecialchars($product['information']) ?>"
   data-group="<?= htmlspecialchars($product['group']) ?>"
   data-price-month="<?= htmlspecialchars($product['price_month']) ?>"
   data-price-year="<?= htmlspecialchars($product['price_year']) ?>"
      data-product-id="<?= htmlspecialchars($product['id']) ?>"
   onclick="showPopup(this)">Buy</a>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>



<div class="card-container" id="k2-cards" style="display: none;">
    <?php foreach ($products as $product): ?>
        <?php if ($product['group'] == 'Children ages 7-11 /K2/'): ?>
        <div class="card">
            <h4 style="text-transform: uppercase; font-weight: 600;"><?= htmlspecialchars($product['title']) ?></h4>
            <p class="price" style="font-size: 28px; font-weight: bold;">
                <?= $product['price_month'] != '0.00' ? '<span>' . strtok(htmlspecialchars($product['price_month']), '.') . '</span><span style="font-size: 16px; vertical-align: super;">.00 AMD</span> <span style="font-size: 14px; vertical-align: sub;">/Monthly</span>' : '' ?>
                <?= $product['price_year'] != '0.00' ? '<span>' . strtok(htmlspecialchars($product['price_year']), '.') . '</span><span style="font-size: 16px; vertical-align: super;">.00 AMD</span> <span style="font-size: 14px; vertical-align: sub;">/Yearly</span>' : '' ?>
                <?= $product['price_month'] == '0.00' && $product['price_year'] == '0.00' ? '<span>' . strtok(htmlspecialchars($product['price']), '.') . '</span><span style="font-size: 16px; vertical-align: super;">.00 AMD</span>' : '' ?>
            </p>
            
            <p>
                <?php 
                $infoLines = explode("\n", htmlspecialchars($product['information'])); 
                ?>
                <ul style="list-style-type: none; padding-left: 0; text-align: left;">
                    <?php foreach ($infoLines as $line): ?>
                        <li style="margin-bottom: 5px; text-transform: capitalize;"><span style="color: #f3ed17;">&#10003;</span> <?= $line ?></li>
                    <?php endforeach; ?>
                </ul>
            </p>

            <!-- Commented out Group, Monthly Price, and Yearly Price (not visible on the website) -->
            <!--
            <p><strong>Group:</strong> <?= htmlspecialchars($product['group']) ?></p>
            <p><strong>Monthly Price:</strong> <?= htmlspecialchars($product['price_month']) ?> AMD</p>
            <p><strong>Yearly Price:</strong> <?= htmlspecialchars($product['price_year']) ?> AMD</p>
            -->

            <a href="#" class="buy-btn" 
               data-title="<?= htmlspecialchars($product['title']) ?>"
               data-price="<?= htmlspecialchars($product['price']) ?>"
               data-information="<?= htmlspecialchars($product['information']) ?>"
               data-group="<?= htmlspecialchars($product['group']) ?>"
               data-price-month="<?= htmlspecialchars($product['price_month']) ?>"
               data-price-year="<?= htmlspecialchars($product['price_year']) ?>"
               onclick="showPopup(this)">Buy</a>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<div class="card-container" id="ta1-cards" style="display: none;">
    <?php foreach ($products as $product): ?>
        <?php if ($product['group'] == 'Teenagers ages 12-17 /TA1/'): ?>
        <div class="card">
            <h4 style="text-transform: uppercase; font-weight: 600;"><?= htmlspecialchars($product['title']) ?></h4>
            <p class="price" style="font-size: 28px; font-weight: bold; align-items: center;">
                <?= $product['price_month'] != '0.00' ? '<span>' . strtok(htmlspecialchars($product['price_month']), '.') . '</span><span style="font-size: 16px; vertical-align: super;">.00 AMD</span> <span style="font-size: 14px; vertical-align: sub;">/Monthly</span>' : '' ?>
                <?= $product['price_year'] != '0.00' ? '<span>' . strtok(htmlspecialchars($product['price_year']), '.') . '</span><span style="font-size: 16px; vertical-align: super;">.00 AMD</span> <span style="font-size: 14px; vertical-align: sub;">/Yearly</span>' : '' ?>
                <?= $product['price_month'] == '0.00' && $product['price_year'] == '0.00' ? '<span>' . strtok(htmlspecialchars($product['price']), '.') . '</span><span style="font-size: 16px; vertical-align: super;">.00 AMD</span>' : '' ?>
            </p>
            
            <p>
                <?php 
                $infoLines = explode("\n", htmlspecialchars($product['information'])); 
                ?>
                <ul style="list-style-type: none; padding-left: 0; text-align: left;">
                    <?php foreach ($infoLines as $line): ?>
                        <li style="margin-bottom: 5px; text-transform: capitalize;"><span style="color: #f3ed17;">&#10003;</span> <?= $line ?></li>
                    <?php endforeach; ?>
                </ul>
            </p>

            <!-- Commented out Group, Monthly Price, and Yearly Price (not visible on the website) -->
            <!--
            <p><strong>Group:</strong> <?= htmlspecialchars($product['group']) ?></p>
            <p><strong>Monthly Price:</strong> <?= htmlspecialchars($product['price_month']) ?> AMD</p>
            <p><strong>Yearly Price:</strong> <?= htmlspecialchars($product['price_year']) ?> AMD</p>
            -->

            <a href="#" class="buy-btn" 
               data-title="<?= htmlspecialchars($product['title']) ?>"
               data-price="<?= htmlspecialchars($product['price']) ?>"
               data-information="<?= htmlspecialchars($product['information']) ?>"
               data-group="<?= htmlspecialchars($product['group']) ?>"
               data-price-month="<?= htmlspecialchars($product['price_month']) ?>"
               data-price-year="<?= htmlspecialchars($product['price_year']) ?>"
               onclick="showPopup(this)">Buy</a>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<div class="card-container" id="ta2-cards" style="display: none;">
    <?php foreach ($products as $product): ?>
        <?php if ($product['group'] == 'Adults/TA2/'): ?>
        <div class="card">
            <h4 style="text-transform: uppercase; font-weight: 600;"><?= htmlspecialchars($product['title']) ?></h4>
            <p class="price" style="font-size: 28px; font-weight: bold;">
                <?= $product['price_month'] != '0.00' ? '<span>' . strtok(htmlspecialchars($product['price_month']), '.') . '</span><span style="font-size: 16px; vertical-align: super;">.00 AMD</span> <span style="font-size: 14px; vertical-align: sub;">/Monthly</span>' : '' ?>
                <?= $product['price_year'] != '0.00' ? '<span>' . strtok(htmlspecialchars($product['price_year']), '.') . '</span><span style="font-size: 16px; vertical-align: super;">.00 AMD</span> <span style="font-size: 14px; vertical-align: sub;">/Yearly</span>' : '' ?>
                <?= $product['price_month'] == '0.00' && $product['price_year'] == '0.00' ? '<span>' . strtok(htmlspecialchars($product['price']), '.') . '</span><span style="font-size: 16px; vertical-align: super;">.00 AMD</span>' : '' ?>
            </p>
            
            <p>
                <?php 
                $infoLines = explode("\n", htmlspecialchars($product['information'])); 
                ?>
                <ul style="list-style-type: none; padding-left: 0; text-align: left;">
                    <?php foreach ($infoLines as $line): ?>
                        <li style="margin-bottom: 5px; text-transform: capitalize;"><span style="color: #f3ed17;">&#10003;</span> <?= $line ?></li>
                    <?php endforeach; ?>
                </ul>
            </p>

            <!-- Commented out Group, Monthly Price, and Yearly Price (not visible on the website) -->
            <!--
            <p><strong>Group:</strong> <?= htmlspecialchars($product['group']) ?></p>
            <p><strong>Monthly Price:</strong> <?= htmlspecialchars($product['price_month']) ?> AMD</p>
            <p><strong>Yearly Price:</strong> <?= htmlspecialchars($product['price_year']) ?> AMD</p>
            -->

            <a href="#" class="buy-btn" 
               data-title="<?= htmlspecialchars($product['title']) ?>"
               data-price="<?= htmlspecialchars($product['price']) ?>"
               data-information="<?= htmlspecialchars($product['information']) ?>"
               data-group="<?= htmlspecialchars($product['group']) ?>"
               data-price-month="<?= htmlspecialchars($product['price_month']) ?>"
               data-price-year="<?= htmlspecialchars($product['price_year']) ?>"
               onclick="showPopup(this)">Buy</a>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
<!-- Section for teacher packages -->
<section class="teacher-packages">
    <h2 class="teacher-packages-title">
        We offer <span class="highlight">annual teaching packages</span> to educational institutions for English language programs based on the theory of multiple intelligences
    </h2>

   <div id="teacher-cards" class="card-container" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; padding-top: 20px;">
    <?php foreach ($products as $product): ?>
        <?php if ($product['type'] == 'teacher' && strtolower($product['group']) == 'all'): ?> 
        <div class="card2">
            <h4 style="text-transform: uppercase; font-weight: 600;"><?= htmlspecialchars($product['title']) ?></h4>
            <p class="price" style="font-size: 28px; font-weight: bold;">
                <?= $product['price_month'] != '0.00' ? '<span>' . strtok(htmlspecialchars($product['price_month']), '.') . '</span><span style="font-size: 16px; vertical-align: super;">.00 AMD</span> <span style="font-size: 14px; vertical-align: sub;">/Monthly</span>' : '' ?>
                <?= $product['price_year'] != '0.00' ? '<span>' . strtok(htmlspecialchars($product['price_year']), '.') . '</span><span style="font-size: 16px; vertical-align: super;">.00 AMD</span> <span style="font-size: 14px; vertical-align: sub;">/Yearly</span>' : '' ?>
                <?= $product['price_month'] == '0.00' && $product['price_year'] == '0.00' ? '<span>' . strtok(htmlspecialchars($product['price']), '.') . '</span><span style="font-size: 16px; vertical-align: super;">.00 AMD</span>' : '' ?>
            </p>
            
            <p>
                <?php 
                $infoLines = explode("\n", htmlspecialchars($product['information'])); 
                ?>
                <ul style="list-style-type: none; padding-left: 0; text-align: left;">
                    <?php foreach ($infoLines as $line): ?>
                        <li style="margin-bottom: 5px; text-transform: capitalize;"><span style="color: #f3ed17;">&#10003;</span> <?= $line ?></li>
                    <?php endforeach; ?>
                </ul>
            </p>

            <a href="#" class="buy-btn" 
               data-title="<?= htmlspecialchars($product['title']) ?>"
               data-price="<?= htmlspecialchars($product['price']) ?>"
               data-information="<?= htmlspecialchars($product['information']) ?>"
               data-group="<?= htmlspecialchars($product['group']) ?>"
               data-price-month="<?= htmlspecialchars($product['price_month']) ?>"
               data-price-year="<?= htmlspecialchars($product['price_year']) ?>"
               data-product-id="<?= htmlspecialchars($product['id']) ?>"
               onclick="showPopup(this)">Buy</a>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

</section>

<?php
// Include the footer
include 'footer.php';
?>

<!-- Popup Overlay -->
<div class="popup-overlay" id="popupOverlay">
    <div class="popup">
        <span class="popup-close" id="closePopup">&times;</span>
        <!-- Left side: Package Info -->
        <div class="popup-left">
            <h3 style="text-transform: uppercase;"></h3>
            <p id="popup-price"></p> <!-- Price will go here once -->
            <ul id="popup-info" style="list-style-type: none; padding-left: 0; text-align: left;"></ul> <!-- Information with checkmarks -->
            <p id="popup-group"><strong>Group:</strong></p>
            <p id="popup-monthly-price" style="display: none;"></p> <!-- Monthly Price -->
            <p id="popup-yearly-price" style="display: none;"></p> <!-- Yearly Price -->
            <div class="payment-methods">
                <img src="https://upload.wikimedia.org/wikipedia/commons/4/41/Visa_Logo.png" alt="Visa">
                <img src="https://upload.wikimedia.org/wikipedia/commons/a/a4/Mastercard_2019_logo.svg" alt="MasterCard">
            </div>
        </div>


<div class="popup-right">
    <form id="buyProductForm">
        <!-- Hidden inputs for product details -->
        <input type="hidden" name="product_name" id="product_name" value="">
        <input type="hidden" name="product_id" id="product_id" value="">
        <input type="hidden" name="cash" id="cash" value="">

      <!-- Email -->
<?php if (!empty($user_data['email'])): ?>
    <p><strong>Email:</strong> <?= htmlspecialchars($user_data['email']); ?></p>
    <input type="hidden" name="email" value="<?= htmlspecialchars($user_data['email']); ?>">
<?php else: ?>
    <p><strong>Email:</strong> <span class="text-muted">Not provided</span></p>
<?php endif; ?>

<!-- Full Name -->
<?php if (!empty($user_data['first_last_name'])): ?>
    <p><strong>Full Name:</strong> <?= htmlspecialchars($user_data['first_last_name']); ?></p>
    <input type="hidden" name="first_last_name" value="<?= htmlspecialchars($user_data['first_last_name']); ?>">
<?php else: ?>
    <p><strong>Full Name:</strong> <span class="text-muted">Not provided</span></p>
<?php endif; ?>


<!-- Phone Number -->
<?php if (!empty($user_data['phone_number'])): ?>
    <p><strong>Phone Number:</strong> <?= htmlspecialchars($user_data['phone_number']); ?></p>
    <input type="hidden" name="phone_number" value="<?= htmlspecialchars($user_data['phone_number']); ?>">
<?php else: ?>
    <p><strong>Phone Number:</strong> <span class="text-muted">Not provided</span></p>
<?php endif; ?>


        <!-- Password and Confirm Password fields (only shown if user is not registered) -->
        <?php if (empty($user_data)): ?>
            <div class="form-group position-relative">
                <label for="password">Password</label>
                <input type="password" class="form-control" name="password" id="password" required>
                <i class="fas fa-eye eye-icon" id="togglePassword"></i>
            </div>
            <div class="form-group position-relative">
                <label for="confirmPassword">Confirm Password</label>
                <input type="password" class="form-control" name="confirm_password" id="confirmPassword" required>
                <i class="fas fa-eye eye-icon" id="toggleConfirmPassword"></i>
            </div>
        <?php endif; ?>
        
        <div class="form-check">
    <input class="form-check-input" type="radio" name="payment_method" id="balancePayment" value="balance" checked>
    <label class="form-check-label" for="balancePayment">
        <i class="fas fa-wallet" style="margin-right: 10px; color: #f3ed17;"></i> Pay from Balance
    </label>
</div>
<div class="form-check">
    <input class="form-check-input" type="radio" name="payment_method" id="cardPayment" value="card">
    <label class="form-check-label" for="cardPayment">
        <i class="fas fa-credit-card" style="margin-right: 10px; color: #3498db;"></i> Pay with Card
    </label>
</div>

        <!-- Agree to terms -->
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="termsCheck" required>
            <label class="form-check-label" for="termsCheck">I agree with the terms and conditions</label>
        </div>

        <!-- Submit button -->
        <button type="submit" class="btn-confirm">Confirm Buy</button>
    </form>
</div>
<!-- Thank You Popup Overlay -->
<div class="popup-overlay" id="thankYouPopup" style="display: none;">
    <div class="popup" style="text-align: center;">
        <span class="popup-close" id="closeThankYouPopup">&times;</span>
        <h3 style="color: #f3ed17;">&#10003; Thank You!</h3> <!-- Checkmark and Title -->
        <p id="thankYouMessage">Your purchase was successful!</p> <!-- Dynamic message -->
        <p id="packageGroup"></p> <!-- Package group will go here -->
        <p id="packageTitle"></p> <!-- Package title will go here -->
    </div>
</div>



<script>
document.addEventListener("DOMContentLoaded", function() {
    // Automatically activate K1 on page load
    showProgramDetails('k1');
    
    // Buy button click handler
    document.querySelectorAll('.buy-btn').forEach(button => {
        button.onclick = function() {
            if (!isUserLoggedIn()) {
                // Redirect to login page if the user is not logged in
                window.location.href = 'login.php';
            } else {
                showPopup(this);  // Open the popup if the user is logged in
            }
        };
    });
    
    // Function to check if the user is logged in
    function isUserLoggedIn() {
        return <?= json_encode(isset($user_data) && !empty($user_data)) ?>; // Return true if the user data exists
    }

    // Close popup when "x" is clicked
    const closePopupBtn = document.getElementById('closePopup');
    const popupOverlay = document.getElementById('popupOverlay');

    if (closePopupBtn && popupOverlay) {
        closePopupBtn.onclick = function() {
            popupOverlay.style.display = 'none';
        };
    } else {
        console.error('Popup close button or overlay not found');
    }

    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    if (togglePassword) {
        togglePassword.onclick = function() {
            const passwordField = document.getElementById('password');
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        };
    }

    // Toggle confirm password visibility
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    if (toggleConfirmPassword) {
        toggleConfirmPassword.onclick = function() {
            const confirmPasswordField = document.getElementById('confirmPassword');
            const type = confirmPasswordField.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordField.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        };
    }

    // Show company name field if legal entity is selected
    const userType = document.getElementById('userType');
    if (userType) {
        userType.onchange = function() {
            const legalField = document.getElementById('legalField');
            if (this.value === 'legal') {
                legalField.style.display = 'block';
            } else {
                legalField.style.display = 'none';
            }
        };
    }
});

// Move showProgramDetails function outside the event listener
function showProgramDetails(programId) {
    // Clear active state from all program boxes
    document.querySelectorAll('.program-box').forEach(box => {
        box.classList.remove('active');
    });

    // Set active state for the selected program
    document.getElementById(programId).classList.add('active');

    // Hide all card containers except for teacher packages
    document.querySelectorAll('.card-container').forEach(container => {
        if (container.id !== 'teacher-cards') {
            container.style.display = 'none';
        }
    });

    // Show the selected program's cards
    const selectedCardContainer = document.getElementById(`${programId}-cards`);
    if (selectedCardContainer) {
        selectedCardContainer.style.display = 'flex';
    }

    // Always keep the teacher-cards visible
    const teacherCardsContainer = document.getElementById('teacher-cards');
    if (teacherCardsContainer) {
        teacherCardsContainer.style.display = 'flex';
    }
}

function showPopup(button) {
    // Get data-* attributes from the clicked button
    const title = button.getAttribute('data-title');
    const information = button.getAttribute('data-information');
    const group = button.getAttribute('data-group');
    const priceMonth = parseFloat(button.getAttribute('data-price-month')).toFixed(2);
    const priceYear = parseFloat(button.getAttribute('data-price-year')).toFixed(2);
    const price = parseFloat(button.getAttribute('data-price')).toFixed(2);
    const productId = button.getAttribute('data-product-id'); // Add product ID

    // Fill the popup title
    document.querySelector('.popup-left h3').innerText = title;

    // Set form hidden fields with product details
    document.getElementById('product_name').value = title;
    document.getElementById('product_id').value = productId; // Set product ID
    const finalPrice = priceMonth !== '0.00' ? priceMonth : (priceYear !== '0.00' ? priceYear : price);
    document.getElementById('cash').value = finalPrice; // Set the correct price

    // Display the appropriate price (monthly/yearly)
    const priceContainer = document.querySelector('#popup-price');
    if (priceMonth !== '0.00') {
        priceContainer.innerHTML = `
            <strong>PRICE MONTHLY:</strong>
            <div style="font-size: 28px; font-weight: bold; color: #f3ed17;">
                ${priceMonth}<span style="font-size: 16px; vertical-align: super;">.00 AMD</span>
            </div>`;
    } else if (priceYear !== '0.00') {
        priceContainer.innerHTML = `
            <strong>PRICE YEARLY:</strong>
            <div style="font-size: 28px; font-weight: bold; color: #f3ed17;">
                ${priceYear}<span style="font-size: 16px; vertical-align: super;">.00 AMD</span>
            </div>`;
    } else if (price !== '0.00') {
        priceContainer.innerHTML = `
            <strong>PRICE:</strong>
            <div style="font-size: 28px; font-weight: bold; color: #f3ed17;">
                ${price}<span style="font-size: 16px; vertical-align: super;">.00 AMD</span>
            </div>`;
    }

    // Display information as checkmarks
    const infoLines = information.split("\n");
    const infoContainer = document.getElementById('popup-info');
    infoContainer.innerHTML = '';  // Clear previous items
    infoLines.forEach(line => {
        const listItem = document.createElement('li');
        listItem.innerHTML = `<span style="color: #f3ed17;">&#10003;</span> ${line}`;
        infoContainer.appendChild(listItem);
    });

    // Display Group Name
    document.querySelector('#popup-group').innerHTML = `<strong>Group:</strong> ${group}`;

    // Show the popup
    document.getElementById('popupOverlay').style.display = 'flex';
}

document.getElementById("buyProductForm").addEventListener("submit", function(event) {
    event.preventDefault();

    // Get selected payment method
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;

    // Find the submit form
    const formData = new FormData(this);

    // Check if "Pay with Card" is selected
    if (paymentMethod === 'card') {
        // Send a request to register the payment and redirect to the payment platform
        fetch('register_payment.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.redirectUrl) {
                window.location.href = data.redirectUrl; // Redirect to the payment form URL
            } else {
                showPopupMessage(data.message); // Show error message in popup
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    } else {
        // Proceed with balance payment as before
fetch('buy_product.php', {
    method: 'POST',
    body: formData
})
.then(response => {
    if (!response.ok) {
        throw new Error('Network response was not ok');
    }
    return response.json();
})
.then(data => {
    if (data.status === 'success') {
        showThankYouPopup(data.packageGroup, data.packageTitle);
    } else {
        showPopupMessage(data.message);
    }
})
.catch(error => {
    console.error('Error:', error);
    showPopupMessage('An error occurred. Please try again.');
});
    }
});

// Function to show thank you message
function showThankYouPopup(packageGroup, packageTitle) {
    // Check if packageGroup and packageTitle are defined and not undefined
    if (packageGroup && packageTitle) {
        document.getElementById('packageGroup').innerText = `Group: ${packageGroup}`; // Set the package group
        document.getElementById('packageTitle').innerText = `Title: ${packageTitle}`; // Set the package title
    } else {
        document.getElementById('packageGroup').innerText = ''; // Clear if undefined
        document.getElementById('packageTitle').innerText = ''; // Clear if undefined
    }
    
    document.getElementById('thankYouPopup').style.display = 'flex'; // Show the popup
}


// Close thank you popup when "x" is clicked
const closeThankYouPopupBtn = document.getElementById('closeThankYouPopup');
if (closeThankYouPopupBtn) {
    closeThankYouPopupBtn.onclick = function() {
        document.getElementById('thankYouPopup').style.display = 'none'; // Hide the popup
    };
}


// Function to show message in the popup
function showPopupMessage(message) {
    // Find or create a message container inside the popup
    let messageContainer = document.getElementById('popup-message');
    
    if (!messageContainer) {
        messageContainer = document.createElement('div');
        messageContainer.id = 'popup-message';
        messageContainer.style.fontSize = '14px'; // Set smaller font size
        messageContainer.style.color = 'red'; // Set text color to red for visibility
        messageContainer.style.marginTop = '10px'; // Add margin for spacing
        document.querySelector('.popup-right').appendChild(messageContainer);
    }

    messageContainer.innerText = message; // Set the error message text
}
</script>




</body>
</html>

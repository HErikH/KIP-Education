<?php
include 'db_connect.php';

if (isset($_POST['group'])) {
    $group = $conn->real_escape_string($_POST['group']);

    // Debugging: Log the group being passed
    error_log("Group received: " . $group);

    // Query to fetch products based on group
    $sql = "SELECT * FROM products WHERE `group` = '$group'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Display the products
        while ($row = $result->fetch_assoc()) {
            echo '<div class="card">';
            echo '<h4>' . $row['title'] . '</h4>';
            echo '<p class="price">Price per month: ' . $row['price_month'] . ' AMD</p>';
            echo '<p class="price">Price per year: ' . $row['price_year'] . ' AMD</p>';
            echo '<p>' . $row['information'] . '</p>';
            echo '<a href="#" class="buy-btn">Buy</a>';
            echo '</div>';
        }
    } else {
        echo '<p>No products found for this group.</p>';
        // Debugging: Log if no products are found
        error_log("No products found for group: " . $group);
    }
}

$group = "Children ages 3-6 /K1/";


$conn->close();
?>

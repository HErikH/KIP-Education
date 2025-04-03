<?php
// Include database connection
include 'db_connect.php';

// Check if form is submitted for editing the product
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];

    // Fetch existing product data
    $selectQuery = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($selectQuery);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    // Collect form data and keep the old data if the fields are empty
    $title = !empty($_POST['title']) ? $_POST['title'] : $product['title'];
    $price_month = !empty($_POST['price_month']) ? $_POST['price_month'] : $product['price_month'];
    $price_year = !empty($_POST['price_year']) ? $_POST['price_year'] : $product['price_year'];
    $price = !empty($_POST['price']) ? $_POST['price'] : $product['price'];
    $group = !empty($_POST['group']) ? $_POST['group'] : $product['group'];
    $information = !empty($_POST['information']) ? $_POST['information'] : $product['information'];
    
    // Check the 'type' based on the checkbox status
    $type = isset($_POST['type_teacher']) ? 'teacher' : 'student';

    // Update query
    $updateQuery = "UPDATE products SET title = ?, price_month = ?, price_year = ?, price = ?, `group` = ?, information = ?, type = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("sssssssi", $title, $price_month, $price_year, $price, $group, $information, $type, $id);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Product updated successfully.</div>";
        header("Location: programmsadmin.php");
        exit();
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}
?>

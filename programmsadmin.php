<?php

session_start(); // Добавляем session_start() в самом начале

// Проверка авторизации и роли
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Если пользователь не авторизован, перенаправляем на страницу логина
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    // Если пользователь авторизован, но не админ, перенаправляем на главную страницу
    header("Location: index.php");
    exit();
}

// Include database connection

include 'db_connect.php';



// Check if the form is submitted to add a new product

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Collect form data

    $title = $_POST['title'];

    $price_month = !empty($_POST['price_month']) ? $_POST['price_month'] : 0; // Set to 0 if empty

    $price_year = !empty($_POST['price_year']) ? $_POST['price_year'] : 0; // Set to 0 if empty

    $price = !empty($_POST['price']) ? $_POST['price'] : 0; // Set to 0 if empty

    $information = $_POST['information'];

    $group = $_POST['group'];

    $type = isset($_POST['type_teacher']) ? 'teacher' : 'student'; // Determine type based on checkbox



    // Insert data into the 'products' table

    $insertQuery = "INSERT INTO products (title, price_month, price_year, information, `group`, price, type) VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($insertQuery);

    $stmt->bind_param("sssssss", $title, $price_month, $price_year, $information, $group, $price, $type);



    if ($stmt->execute()) {

        // Redirect to avoid form re-submission on refresh

        header("Location: programmsadmin.php");

        exit();

    } else {

        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";

    }

}



// Fetch products to display in the table

$productsQuery = "SELECT * FROM products";

$productsResult = $conn->query($productsQuery);



// Handle delete request

if (isset($_GET['delete_id'])) {

    $deleteId = $_GET['delete_id'];

    $deleteQuery = "DELETE FROM products WHERE id = ?";

    $stmt = $conn->prepare($deleteQuery);

    $stmt->bind_param("i", $deleteId);

    if ($stmt->execute()) {

        echo "<div class='alert alert-success'>Product deleted successfully.</div>";

    } else {

        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";

    }

}

?>







<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add New Product</title>

    <!-- Bootstrap CSS -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        .form-section {

            background-color: #f7f7f7;

            padding: 20px;

            border-radius: 10px;

            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);

        }



        .form-section h2 {

            font-size: 24px;

            font-weight: bold;

            margin-bottom: 20px;

            color: #333;

        }



        .product-table {

            background-color: white;

            border-radius: 10px;

            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);

            margin-left: 20px;

        }



        .product-table table {

            width: 100%;

        }



        .product-table table th, .product-table table td {

            padding: 10px;

            text-align: center;

        }



        .action-buttons {

            display: flex;

            justify-content: center;

        }



        .action-buttons a {

            margin-right: 10px;

        }



        .modal-footer button {

            margin-right: 10px;

        }

    </style>

</head>

<body>



<?php include 'headeradmin.php'; ?>



<div class="container mt-5 d-flex">

    <!-- Form Section -->

    <div class="col-md-4 form-section">

        <h2>Add New Product</h2>



        <form method="POST" action="programmsadmin.php">

            <div class="mb-3">

                <label for="title" class="form-label">Title</label>

                <input type="text" class="form-control" id="title" name="title" required>

            </div>



            <div class="mb-3">

                <label for="price_month" class="form-label">Price (Month)</label>

                <input type="text" class="form-control" id="price_month" name="price_month">

            </div>



            <div class="mb-3">

                <label for="price_year" class="form-label">Price (Year)</label>

                <input type="text" class="form-control" id="price_year" name="price_year">

            </div>



            <div class="mb-3">

                <label for="group" class="form-label">Group</label>

                <select class="form-control" id="group" name="group" onchange="setInformation()">

                    <option value="all">All</option>

                    <option value="Children ages 3-6 /K1/">Children ages 3-6 /K1/</option>

                    <option value="Children ages 7-11 /K2/">Children ages 7-11 /K2/</option>

                    <option value="Teenagers ages 12-17 /TA1/">Teenagers ages 12-17 /TA1/</option>

                    <option value="Adults/TA2/">Adults/TA2/</option>

                </select>

            </div>



            <div class="mb-3">

                <label for="information" class="form-label">Information</label>

                <textarea class="form-control" id="information" name="information" rows="4"></textarea>

            </div>



            <div class="mb-3">

                <label for="price" class="form-label">Price</label>

                <input type="text" class="form-control" id="price" name="price">

            </div>



            <!-- Checkbox for Type -->

            <div class="mb-3 form-check">

                <input type="checkbox" class="form-check-input" id="type_teacher" name="type_teacher">

                <label class="form-check-label" for="type_teacher">For Teachers</label>

            </div>



            <button type="submit" class="btn btn-primary w-100">Add Product</button>

        </form>

    </div>



    <!-- Products Table Section -->

    <div class="col-md-8 product-table">

        <h2 class="p-3">Product List</h2>

        <table class="table table-striped">

            <thead>

                <tr>

                    <th>#</th>

                    <th>Title</th>

                    <th>Price (Month)</th>

                    <th>Price (Year)</th>

                    <th>Price</th> <!-- Added Price column -->

                    <th>Group</th>

                    <th>Actions</th>

                </tr>

            </thead>

            <tbody>

                <?php while ($product = $productsResult->fetch_assoc()): ?>

                <tr>

                    <td><?php echo $product['id']; ?></td>

                    <td><?php echo $product['title']; ?></td>

                    <td><?php echo $product['price_month']; ?></td>

                    <td><?php echo $product['price_year']; ?></td>

                    <td><?php echo $product['price']; ?></td> <!-- Display Price -->

                    <td><?php echo $product['group']; ?></td>

                    <td class="action-buttons">

                        <a href="#" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $product['id']; ?>">Edit</a>

                        <a href="#" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $product['id']; ?>">Delete</a>

                    </td>

                </tr>



<!-- Modal for Editing Product -->

<div class="modal fade" id="editModal<?php echo $product['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $product['id']; ?>" aria-hidden="true">

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title" id="editModalLabel<?php echo $product['id']; ?>">Edit Product</h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

            </div>

            <div class="modal-body">

                <!-- Edit Form Inside Modal -->

                <form method="POST" action="editproduct.php">

                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">

                    <div class="mb-3">

                        <label for="title" class="form-label">Title</label>

                        <input type="text" class="form-control" id="title" name="title" value="<?php echo $product['title']; ?>" required>

                    </div>

                    <div class="mb-3">

                        <label for="price_month" class="form-label">Price (Month)</label>

                        <input type="text" class="form-control" id="price_month" name="price_month" value="<?php echo $product['price_month']; ?>">

                    </div>

                    <div class="mb-3">

                        <label for="price_year" class="form-label">Price (Year)</label>

                        <input type="text" class="form-control" id="price_year" name="price_year" value="<?php echo $product['price_year']; ?>">

                    </div>

                    <div class="mb-3">

                        <label for="price" class="form-label">Price</label>

                        <input type="text" class="form-control" id="price" name="price" value="<?php echo $product['price']; ?>">

                    </div>

                    <div class="mb-3">

                        <label for="group" class="form-label">Group</label>

                        <select class="form-control" id="group<?php echo $product['id']; ?>" name="group">

                            <option value="all" <?php if ($product['group'] == 'all') echo 'selected'; ?>>All</option>

                            <option value="Children ages 3-6 /K1/" <?php if ($product['group'] == 'Children ages 3-6 /K1/') echo 'selected'; ?>>Children ages 3-6 /K1/</option>

                            <option value="Children ages 7-11 /K2/" <?php if ($product['group'] == 'Children ages 7-11 /K2/') echo 'selected'; ?>>Children ages 7-11 /K2/</option>

                            <option value="Teenagers ages 12-17 /TA1/" <?php if ($product['group'] == 'Teenagers ages 12-17 /TA1/') echo 'selected'; ?>>Teenagers ages 12-17 /TA1/</option>

                            <option value="Adults/TA2/" <?php if ($product['group'] == 'Adults/TA2/') echo 'selected'; ?>>Adults/TA2/</option>

                        </select>

                    </div>

                    <div class="mb-3">

                        <label for="information" class="form-label">Information</label>

                        <textarea class="form-control" id="information<?php echo $product['id']; ?>" name="information" rows="4"><?php echo $product['information']; ?></textarea>

                    </div>



                    <!-- Checkbox for Type -->

                    <div class="mb-3 form-check">

                        <input type="checkbox" class="form-check-input" id="type_teacher<?php echo $product['id']; ?>" name="type_teacher" <?php if ($product['type'] == 'teacher') echo 'checked'; ?>>

                        <label class="form-check-label" for="type_teacher<?php echo $product['id']; ?>">For Teachers</label>

                    </div>



                    <button type="submit" class="btn btn-primary">Save Changes</button>

                </form>

            </div>

        </div>

    </div>

</div>





                <!-- Modal for Deleting Product -->

                <div class="modal fade" id="deleteModal<?php echo $product['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $product['id']; ?>" aria-hidden="true">

                    <div class="modal-dialog">

                        <div class="modal-content">

                            <div class="modal-header">

                                <h5 class="modal-title" id="deleteModalLabel<?php echo $product['id']; ?>">Delete Product</h5>

                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                            </div>

                            <div class="modal-body">

                                Are you sure you want to delete the product "<?php echo $product['title']; ?>"?

                            </div>

                            <div class="modal-footer">

                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

                                <a href="programmsadmin.php?delete_id=<?php echo $product['id']; ?>" class="btn btn-danger">Delete</a>

                            </div>

                        </div>

                    </div>

                </div>



                <?php endwhile; ?>

            </tbody>

        </table>

    </div>

</div>



<?php include 'footeradmin.php'; ?>



<!-- Bootstrap JS -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>



<script>

    function setInformation(productId = '') {

        var group = document.getElementById("group" + productId).value;

        var informationField = document.getElementById("information" + productId);



        if (group === "Children ages 3-6 /K1/") {

            informationField.value = "64 lesson materials ( K1 )\nThe first lesson is free\nPPTs for each lesson topic\n1 hour long\nworksheets\nmusic\nvideos\ngames";

        } else if (group === "Children ages 7-11 /K2/") {

            informationField.value = "64 lesson materials ( K2 )\nThe first lesson is free\nPPTs for each lesson topic\n1 hour long\nworksheets\nmusic\nvideos\ngames";

        } else if (group === "Teenagers ages 12-17 /TA1/" || group === "Adults/TA2/") {

            informationField.value = "5 weeks\n10 lessons\n2 times a week\n1 hour long";

        }

    }

</script>



</body>

</html>


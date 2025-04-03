<?php
// Start the session only if it's not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start session only if none exists
}

// Check if the user is logging out
if (isset($_GET['logout'])) {
    session_destroy(); // Destroy session on logout
    header("Location: index.php"); // Redirect to index after logout
    exit();
}

// Include necessary files
include 'db_connect.php'; // Include your database connection

// Check if the session is valid
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if no valid session
    exit();
}

$user_id = $_SESSION['user_id'];

// Set the character encoding
$conn->set_charset("utf8mb4");

// Fetch user data
$sql = "SELECT * FROM students WHERE id = '$user_id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc(); // Fetch the user's data as an associative array
} else {
    echo "User not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(90deg, #4b6cb7, #182848); /* Background gradient */
            color: white;
            margin: 0;
            padding: 0;
        }

        .content {
            margin-top: 100px; /* Space for fixed header */
            padding: 20px;
        }

        .profile-container {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 900px;
            margin: auto;
        }

        .profile-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .profile-header img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid #3498db;
            object-fit: cover;
        }

        .profile-header h2 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .profile-info {
            margin-top: 20px;
        }

        .profile-info table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .profile-info th, .profile-info td {
            padding: 15px;
            text-align: left;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .profile-info th {
            background-color: rgba(0, 123, 255, 0.8);
            color: #fff;
        }

        .profile-info td {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .edit-icon {
            cursor: pointer;
            margin-left: 10px;
            color: #3498db;
        }

        .edit-icon:hover {
            color: #2980b9;
        }

        .btn-logout {
            display: inline-block;
            background-color: #e74c3c;
            color: #fff;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }

        .btn-logout:hover {
            background-color: #c0392b;
        }

        /* Modal Styles */
        .modal {
            display: none;
            align-items: center;
            justify-content: center;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.85);
            z-index: 1000;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: linear-gradient(135deg, #4b6cb7, #182848);
            padding: 30px;
            border-radius: 15px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            color: white;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
        }

        .close:hover {
            background-color: #c0392b;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
        }
    </style>
</head>
<body>

<!-- Header -->
<?php include 'headeruser.php'; ?>

<div class="content">
    <div class="profile-container">
        <div class="profile-header">
            <img src="resource/img/profile.png" alt="User Image">
            <div>
                <h2><?php echo $user['email']; ?></h2>
                <p>ID: <?php echo $user['id']; ?></p>
            </div>
        </div>

        <div class="profile-info">
            <table>
                <tr>
                    <th>First Name</th>
                    <td>
                        <?php echo isset($user['first_name']) ? $user['first_name'] : 'N/A'; ?>
                        <i class="fas fa-edit edit-icon" onclick="openEditPopup('first_name', '<?php echo $user['first_name']; ?>')"></i>
                    </td>
                </tr>
                <tr>
                    <th>Last Name</th>
                    <td>
                        <?php echo isset($user['last_name']) ? $user['last_name'] : 'N/A'; ?>
                        <i class="fas fa-edit edit-icon" onclick="openEditPopup('last_name', '<?php echo $user['last_name']; ?>')"></i>
                    </td>
                </tr>
                <tr>
                    <th>Phone Number</th>
                    <td>
                        <?php echo isset($user['phone_number']) ? $user['phone_number'] : 'N/A'; ?>
                        <i class="fas fa-edit edit-icon" onclick="openEditPopup('phone_number', '<?php echo $user['phone_number']; ?>')"></i>
                    </td>
                </tr>
                <tr>
                    <th>Address</th>
                    <td>
                        <?php echo isset($user['address']) ? $user['address'] : 'N/A'; ?>
                        <i class="fas fa-edit edit-icon" onclick="openEditPopup('address', '<?php echo $user['address']; ?>')"></i>
                    </td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?php echo $user['email']; ?></td>
                </tr>
                <tr>
                    <th>Password</th>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick="openEditPopup('password', '****')">Change Password</button>
                    </td>
                </tr>
            </table>
        </div>

        <a href="profile.php?logout=true" class="btn-logout">Logout</a>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEditPopup()">&times;</span>
        <h3>Edit <span id="editField"></span></h3>
        <form id="editForm" method="POST" action="update_profile.php">
            <input type="hidden" name="field" id="field">
            <div class="form-group">
                <label for="value" class="form-label">New Value</label>
                <input type="text" name="value" id="value" class="form-control" placeholder="Enter new value" required>
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-secondary" onclick="closeEditPopup()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Function to open edit popup
    function openEditPopup(field, currentValue) {
        document.getElementById('editField').innerText = field.replace('_', ' ').toUpperCase();
        document.getElementById('field').value = field;
        document.getElementById('value').value = currentValue;
        document.getElementById('editModal').classList.add('active');
    }

    // Function to close edit popup
    function closeEditPopup() {
        document.getElementById('editModal').classList.remove('active');
    }
</script>

<!-- Include Footer -->
<?php include 'footerhome.php'; ?>

</body>
</html>

<?php
// Close the connection at the end of the script
$conn->close();
?>

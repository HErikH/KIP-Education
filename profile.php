<?php
require_once 'helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start session if not already started
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection
include 'db_connect.php';

// Устанавливаем кодировку для соединения с базой данных
$conn->set_charset("utf8mb4");

// Проверяем, задан ли ID пользователя в сессии
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    echo "User ID is not set in the session.";
    exit();
}

// Получаем данные пользователя из таблицы users
$sql = "SELECT id, email, first_last_name, phone_number, country, date_register, role, blocked 
        FROM users 
        WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error preparing the query: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Проверяем, найден ли пользователь
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "No user found for this ID.";
    exit();
}

// Получаем IP адреса из таблицы login_history
$ip_sql = "SELECT DISTINCT ip_address FROM login_history WHERE user_id = ?";
$ip_stmt = $conn->prepare($ip_sql);

if ($ip_stmt === false) {
    die("Error preparing the IP query: " . $conn->error);
}

$ip_stmt->bind_param("i", $user['id']);
$ip_stmt->execute();
$ip_result = $ip_stmt->get_result();

// Закрытие запросов (не базы данных)
$ip_stmt->close();
$stmt->close();

// Закрываем соединение с базой данных только после завершения всех запросов
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="icon" href="<?= addMediaBaseUrl('resource/img/favicon.png') ?>" type="image/png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background: linear-gradient(90deg, #4b6cb7 0%, #182848 100%);
            color: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding-top: 150px; /* Increased top padding for desktops */
        }

        /* Adding space for footer */
        body::after {
            content: "";
            display: block;
            height: 100px;
        }
        
        /* Apply specific margins for mobile devices */
    @media only screen and (max-width: 768px) {
        body {
            margin-top: 70px;
        }

        .container, .content {
            margin-top: 100px;
            margin-bottom: 100px;
        }
    }
    

        header, footer {
            background-color: #182848;
            padding: 20px;
            text-align: center;
            color: white;
        }

        /* Flexbox layout for the containers */
        .container-layout {
            display: flex;
            justify-content: space-between;
            gap: 20px; /* Space between the containers */
            width: 80%; /* Adjust the width to your preference */
            margin: 0 auto;
        }

        .profile-container, .extra-container {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            min-height: 300px; /* Adjusted for better appearance */
            flex: 1; /* All containers will share the available space */
        }

        .profile-container img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid #3498db;
            margin-bottom: 20px;
        }

        .profile-container h2, .extra-container h3 {
            font-size: 22px;
            margin-bottom: 10px;
        }

        .profile-container p {
            font-size: 16px;
            margin-bottom: 5px;
            color: #ecf0f1;
        }

        /* Personal Information Section Styling */
        .personal-info {
            text-align: left;
            position: relative;
        }

        .personal-info p {
            font-size: 16px;
            margin: 10px 0;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.3); /* Add a line under each item */
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .personal-info label {
            margin-bottom: 5px;
            display: block;
        }

        .edit-icon {
            color: #3498db;
            cursor: pointer;
        }

        /* Modal Styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.7);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #182848;
            padding: 30px;
            border-radius: 10px;
            width: 400px;
            max-width: 100%;
            color: white;
            text-align: left;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h5 {
            margin: 0;
        }

        .modal-header .close {
            color: white;
            font-size: 20px;
            cursor: pointer;
        }

        .modal-body {
            margin-top: 20px;
        }

        .modal-body input {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #3498db;
            margin-bottom: 15px;
            background-color: #fff;
            color: #000;
        }

        /* Mobile adjustments */
        @media (max-width: 768px) {
            body {
                padding-top: 50px;
            }

            .container-layout {
                flex-direction: column; /* Stack containers vertically on mobile */
            }

            .profile-container, .extra-container {
                width: 100%;
                margin-bottom: 20px;
            }
        }
        .profile-container p span {
    color: black;
    font-weight: bold;
}
.extra-container {
    position: relative; /* Ensure that child elements can be positioned relative to this container */
    background-color: rgba(255, 255, 255, 0.1);
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
    min-height: 150px; /* Adjusted for better appearance */
}

 /* Mobile specific styling */
    @media only screen and (max-width: 768px) {
        .extra-container {
            min-height: 250px; /* Ավելի բարձր բարձրություն բջջային սարքերի համար */
        }
    }

/* Additional containers for the bottom */
        .bottom-container-layout {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            width: 80%;
            margin: 50px auto 0 auto; /* Spacing at the bottom */
        }

        .bottom-container {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            min-height: 150px;
            flex: 1;
        }

        /* Mobile adjustments */
        @media (max-width: 768px) {
            body {
                padding-top: 50px;
            }

            .container-layout, .bottom-container-layout {
                flex-direction: column;
            }

            .profile-container, .extra-container, .bottom-container {
                width: 100%;
                margin-bottom: 20px;
            }
            
            .logout-button-container {
    position: relative; /* Allow absolute positioning of the button within the container */
    margin-top: 20px; /* Space above the button */
}
   /* Mobile specific styling */
    @media only screen and (max-width: 768px) {
        .logout-button-container {
            margin-top: 80px; /* Իջեցնում ենք կոճակը ներքև */
        }

    }

    </style>
</head>
<body>

<?php
// Include the header
include 'header.php';
?>

<!-- Main Layout -->
<div class="container-layout">
    <!-- Profile Container -->
<div class="profile-container">
    <img src="<?= addMediaBaseUrl('resource/img/profile.png') ?>" alt="Profile Picture">
    <h2><?php echo htmlspecialchars($user['first_last_name']); ?></h2> <!-- Թարմացվել է այստեղ -->
    <p><?php echo htmlspecialchars($user['email']); ?></p>
    <p>ID: <?php echo htmlspecialchars($user['id']); ?></p>
    <p>Role: <?php echo htmlspecialchars($user['role']); ?></p>
    <p>Date Registered: <?php echo htmlspecialchars($user['date_register']); ?></p>
</div>

    <!-- Personal Information Container -->
<div class="extra-container">
    <h3>Personal Information</h3>
    <div class="personal-info">
        <p><label for="email">Email:</label> <?php echo htmlspecialchars($user['email']); ?></p>
        
        <p><label for="first_last_name">Full Name:</label> <?php echo htmlspecialchars($user['first_last_name']); ?>
            <i class="fas fa-edit edit-icon" data-field="first_last_name"></i> <!-- Թարմացվել է այստեղ -->
        </p>
        
        <p><label for="phone_number">Phone Number:</label> <?php echo isset($user['phone_number']) ? htmlspecialchars($user['phone_number']) : 'Not provided'; ?>
            <i class="fas fa-edit edit-icon" data-field="phone_number"></i>
        </p>
    </div>
</div>


<!-- My Program Container -->
<div class="extra-container">
    <h3>My Program</h3>
    <p>You do not have a selected package yet.</p>
    <a href="programms" class="btn btn-primary">Buy Program</a>

    <!-- Log Out Button -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <div class="logout-button-container">
        <a href="logout" class="btn btn-danger" style="position: absolute; bottom: 10px; right: 10px;">
            Log Out
        </a>
    </div>
    <?php endif; ?>
</div>
</div> <!-- End of Main Layout -->

<!-- Bottom Containers Layout -->
<div class="bottom-container-layout">
    <!-- Change Password Container -->
    <div class="bottom-container">
        <h3>Change Password</h3>
        <button id="changePasswordBtn" class="btn btn-warning">Change Password</button>
    </div>

   <!-- Вывод IP адресов -->
    <div class="extra-container">
        <h3>Accessed Devices</h3>
        <p>Your IP address: <strong><?php echo $_SERVER['REMOTE_ADDR']; ?></strong></p>
        <p>Other IP addresses used to access this page:</p>
        <ul>
            <?php
            if ($ip_result->num_rows > 0) {
                while ($ip_row = $ip_result->fetch_assoc()) {
                    echo "<li>" . htmlspecialchars($ip_row['ip_address']) . "</li>";
                }
            } else {
                echo "<li>No IP addresses found</li>";
            }
            ?>
        </ul>
    </div>
</div>

<!-- Modal Structure -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h5>Edit Field</h5>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <form id="editForm">
                <label for="fieldValue">Edit Value:</label>
                <input type="text" id="fieldValue" name="fieldValue">
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div>
</div>

<!-- Modal Structure for Changing Password -->
<div id="changePasswordModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h5>Change Password</h5>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <form id="changePasswordForm">
                <label for="currentPassword">Current Password:</label>
                <input type="password" id="currentPassword" name="currentPassword" required>

                <label for="newPassword">New Password:</label>
                <input type="password" id="newPassword" name="newPassword" required>

                <label for="confirmPassword">Confirm New Password:</label>
                <input type="password" id="confirmPassword" name="confirmPassword" required>

                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
</div>

<script>
// Get elements for edit modal
const editIcons = document.querySelectorAll('.edit-icon');
const editModal = document.getElementById('editModal');
const closeEditModal = document.querySelector('#editModal .close'); // Specific close button for the edit modal
const fieldValueInput = document.getElementById('fieldValue');
let currentField = ''; // To store the current field being edited

// Add event listener to all edit icons
editIcons.forEach(icon => {
    icon.addEventListener('click', function() {
        currentField = this.getAttribute('data-field'); // Get the field that needs to be edited
        const currentValue = this.previousSibling.textContent.trim(); // Get the current value (assuming it's in the previous sibling)
        fieldValueInput.value = currentValue; // Set the value in the input field
        editModal.style.display = 'flex'; // Show the modal
    });
});

// Close edit modal when the close button is clicked
closeEditModal.onclick = function() {
    editModal.style.display = 'none';
}

// Close edit modal when clicking outside the modal
window.onclick = function(event) {
    if (event.target == editModal) {
        editModal.style.display = 'none';
    }
}

// Handle the form submission for edit modal
document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Prevent default form submission

    const newValue = fieldValueInput.value.trim(); // Get the new value from the input field and trim any whitespace

    if (newValue === "") {
        alert("Field value cannot be empty.");
        return;
    }

    // Send AJAX request to update the value on the server
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'update_user_field.php', true); // Replace with your actual update URL
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
        if (xhr.status === 200) {
            // Check for server response
            if (xhr.responseText.trim() === "Success") {
                // Successfully updated
                editModal.style.display = 'none'; // Hide the modal
                location.reload(); // Reload the page to show the updated value (optional)
            } else {
                alert('Error updating field: ' + xhr.responseText); // Show server error
            }
        } else {
            alert('Error connecting to the server.');
        }
    };

    // Send the new value, the field, and the user ID
    xhr.send(`field=${encodeURIComponent(currentField)}&newValue=${encodeURIComponent(newValue)}&userId=${encodeURIComponent(<?php echo json_encode($user['id']); ?>)}`);
});


// Change Password Modal
const changePasswordBtn = document.getElementById("changePasswordBtn");
const changePasswordModal = document.getElementById("changePasswordModal");
const closeChangePasswordModal = document.querySelector('#changePasswordModal .close'); // Specific close button for the change password modal

// Open modal when "Change Password" button is clicked
changePasswordBtn.onclick = function() {
    changePasswordModal.style.display = 'flex';
}

// Close change password modal when the close button is clicked
closeChangePasswordModal.onclick = function() {
    changePasswordModal.style.display = 'none';
}

// Close change password modal when clicking outside the modal
window.onclick = function(event) {
    if (event.target == changePasswordModal) {
        changePasswordModal.style.display = 'none';
    }
}

// Handle the form submission for change password
document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Prevent the default form submission

    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    // Check if the new passwords match
    if (newPassword !== confirmPassword) {
        alert("New passwords do not match!");
        return;
    }

    // Send AJAX request to update the password
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'change_password.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
        if (xhr.status === 200) {
            alert('Password changed successfully!');
            changePasswordModal.style.display = 'none';
        } else {
            alert('Error changing password: ' + xhr.responseText);
        }
    };

    // Send the current password, new password to the server
    xhr.send(`currentPassword=${currentPassword}&newPassword=${newPassword}`);
});


 
</script>

<?php
// Include the footer
include 'footerhome.php';
?>

</body>
</html>

<?php
session_start();
include 'db_connect.php';

// Session check
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}

// Fetching users and admins data
$usersResult = $conn->query("SELECT * FROM users");
$adminsResult = $conn->query("SELECT * FROM admins");

// Fetching login history
$loginRecordsQuery = "SELECT email, ip_address, login_time FROM login_history ORDER BY login_time DESC";
$loginRecordsResult = $conn->query($loginRecordsQuery);

include 'headeradmin.php';

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users and Admins Management</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">    
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .content-header {
            text-align: center;
            margin-bottom: 30px;
        }
        h1 {
            font-size: 32px;
            color: #007bff;
            font-weight: bold;
            margin-bottom: 10px;
        }
        p {
            font-size: 16px;
            color: #555;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #007bff;
            color: #fff;
            padding: 15px;
            border-radius: 10px 10px 0 0;
            font-weight: bold;
            cursor: pointer;
        }
        .card-body {
            padding: 20px;
            background-color: #fff;
            border-radius: 0 0 10px 10px;
        }
        .table-striped {
            border-radius: 10px;
            overflow: hidden;
        }
        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-size: 16px;
        }
        .table tbody td {
            padding: 12px 15px;
            vertical-align: middle;
            border-top: 1px solid #dee2e6;
        }
        .table tbody tr:hover {
            background-color: #f1f3f5;
            transition: background-color 0.3s ease;
        }
        .btn {
            border-radius: 5px;
            font-size: 14px;
        }
        .btn-danger {
            background-color: #dc3545;
            border: none;
        }
        .btn-danger:hover {
            background-color: #c82333;
            transition: background-color 0.3s;
        }
        .btn-secondary {
            background-color: #6c757d;
            border: none;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            transition: background-color 0.3s;
        }
        .btn-success {
            background-color: #28a745;
            border: none;
        }
        .btn-success:hover {
            background-color: #218838;
            transition: background-color 0.3s;
        }
        /* Responsive Design */
        @media (max-width: 768px) {
            h1 {
                font-size: 24px;
            }
            p {
                font-size: 14px;
            }
            .card-header {
                font-size: 16px;
                padding: 10px;
            }
            .table thead th, .table tbody td {
                font-size: 14px;
            }
        }
.email-cell {
    max-width: 150px; /* սահմանեք ֆիքսված չափը */
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    cursor: pointer; /* Մկնիկի ցուցիչը դարձնել ցույց տվող */
}

.email-cell.expanded {
    white-space: normal; /* Ամբողջական հասցեն տեսնելու համար */
    overflow: visible;
    max-width: none; /* Ջնջում ենք ֆիքսված չափը */
}


    </style>
</head>
<body>
    <div class="content">
        <div class="container mt-4">
            <div class="content-header">
                <h1>Users and Admins Management</h1>
                <p>Manage all users and admins in the system here.</p>
            </div>

            <!-- Add User Button -->
            <div class="text-end mb-3">
                <button class="btn btn-success btn-lg shadow-sm" onclick="openAddUserModal()">
                    <i class="fas fa-user-plus"></i> Add New User
                </button>
                <button class="btn btn-success btn-lg shadow-sm ms-2" onclick="openAddAdminModal()">
                    <i class="fas fa-user-shield"></i> Add New Admin
                </button>
            </div>
            
            <!-- Add User Modal -->
            <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                           <form id="addUserForm" method="POST" action="add_user.php">
    <div class="row mb-3">
        <div class="col">
            <label for="email" class="form-label">Email*</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="col">
            <label for="password" class="form-label">Password*</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col">
            <label for="first_last_name" class="form-label">First and Last Name*</label>
            <input type="text" class="form-control" id="first_last_name" name="first_last_name" required>
        </div>
        <div class="col">
            <label for="phone_number" class="form-label">Phone Number</label>
            <input type="text" class="form-control" id="phone_number" name="phone_number" value="0">
        </div>
    </div>
    <div class="row mb-3">
        <div class="col">
            <label for="role" class="form-label">Role*</label>
            <select class="form-select" id="role" name="role" required>
                <option value="guest">Guest</option>
                <option value="teacher">Teacher</option>
                <option value="student">Student</option>
            </select>
        </div>
        <div class="col">
            <label for="blocked" class="form-label">Blocked*</label>
            <select class="form-select" id="blocked" name="blocked">
                <option value="No">No</option>
                <option value="Yes">Yes</option>
            </select>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col">
            <label for="product_name" class="form-label">Product Name</label>
            <input type="text" class="form-control" id="product_name" name="product_name" value="null">
        </div>
        <div class="col">
            <label for="product_id" class="form-label">Product ID</label>
            <input type="text" class="form-control" id="product_id" name="product_id" value="null">
        </div>
    </div>
    <div class="row mb-3">
        <div class="col">
            <label for="balance" class="form-label">Balance</label>
            <input type="number" class="form-control" id="balance" name="balance" value="0.00">
        </div>
        <div class="col">
            <label for="country" class="form-label">Country</label>
            <input type="text" class="form-control" id="country" name="country" value="Armenia">
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-success">Add User</button>
    </div>
</form>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Admin Modal -->
            <div class="modal fade" id="addAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title" id="addAdminModalLabel">Add New Admin</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="addAdminForm" method="POST" action="add_admin.php">
                                <div class="mb-3">
                                    <label for="admin_email" class="form-label">Email*</label>
                                    <input type="email" class="form-control" id="admin_email" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="admin_password" class="form-label">Password*</label>
                                    <input type="password" class="form-control" id="admin_password" name="password" required>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success">Add Admin</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        <div class="card mt-4">
    <div class="card-header" data-bs-toggle="collapse" data-bs-target="#usersTable">
        <h5>Users List</h5>
    </div>
    <div id="usersTable" class="collapse show">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Email</th>
                        <th>Phone Number</th>
                        <th>Registration Date</th>
                        <th>Role</th>
                        <th>Balance</th>
                        <th>Country</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $usersResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td class="email-cell" data-full-email="<?php echo htmlspecialchars($user['email']); ?>">
                            <?php echo strlen($user['email']) > 20 ? substr(htmlspecialchars($user['email']), 0, 20) . '...' : htmlspecialchars($user['email']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($user['phone_number']); ?></td> <!-- Phone Number Field -->
                        <td><?php echo htmlspecialchars($user['date_register']); ?></td> <!-- Registration Date -->
                        <td><?php echo htmlspecialchars($user['role']); ?></td> <!-- Role -->
                        <td><?php echo htmlspecialchars($user['balance']); ?></td> <!-- Balance -->
                        <td><?php echo htmlspecialchars($user['country']); ?></td> <!-- Country -->
                        <td class="action-buttons">
                            <button class="btn btn-danger btn-sm" onclick="confirmDelete('<?php echo $user['id']; ?>', '<?php echo htmlspecialchars($user['email']); ?>')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                            <button class="btn btn-secondary btn-sm" onclick="openSettings(
                                '<?php echo htmlspecialchars($user['id']); ?>', 
                                '<?php echo htmlspecialchars($user['email']); ?>', 
                                '<?php echo htmlspecialchars($user['first_last_name']); ?>', 
                                '<?php echo htmlspecialchars($user['phone_number']); ?>', 
                                '<?php echo htmlspecialchars($user['role']); ?>', 
                                '<?php echo htmlspecialchars($user['balance']); ?>', 
                                '<?php echo htmlspecialchars($user['country']); ?>')">
                                <i class="fas fa-cog"></i> Settings
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


          <div class="card mt-4">
    <div class="card-header" data-bs-toggle="collapse" data-bs-target="#adminsTable">
        <h5>Admins List</h5>
    </div>
    <div id="adminsTable" class="collapse">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created At</th>
                        <th>Actions</th> <!-- Оставляем только Delete -->
                    </tr>
                </thead>
                <tbody>
                    <?php while ($admin = $adminsResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $admin['id']; ?></td>
                        <td><?php echo $admin['email']; ?></td>
                        <td><?php echo $admin['role']; ?></td>
                        <td><?php echo $admin['created_at']; ?></td>
                        <td class="action-buttons">
                            <button class="btn btn-danger btn-sm" onclick="confirmDelete('<?php echo $admin['id']; ?>', '<?php echo $admin['email']; ?>')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

            <!-- Last Login Records Table -->
            <div class="card mt-4">
                <div class="card-header" data-bs-toggle="collapse" data-bs-target="#lastLoginTable">
                    <h5>Last Login Records</h5>
                </div>
                <div id="lastLoginTable" class="collapse">
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>User Email</th>
                                    <th>IP Address</th>
                                    <th>Login Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($record = $loginRecordsResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($record['email']); ?></td>
                                    <td><?php echo htmlspecialchars($record['ip_address']); ?></td>
                                    <td><?php echo htmlspecialchars($record['login_time']); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete user <span id="deleteUserEmail"></span>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Yes, delete</button>
                </div>
            </div>
        </div>
    </div>

<!-- Settings Modal -->
<div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="settingsModalLabel">User Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="userSettingsForm" method="POST" action="update_user.php">
                    <input type="hidden" name="user_id" id="settingsUserId">

                    <div class="row mb-3">
                        <div class="col">
                            <label for="settings_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="settings_email" name="email" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="settings_first_last_name" class="form-label">First and Last Name</label>
                            <input type="text" class="form-control" id="settings_first_last_name" name="first_last_name" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="settings_phone_number" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="settings_phone_number" name="phone_number">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="settings_role" class="form-label">Role</label>
                            <select class="form-select" id="settings_role" name="role">
                                <option value="guest">Guest</option>
                                <option value="teacher">Teacher</option>
                                <option value="student">Student</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="settings_balance" class="form-label">Balance</label>
                            <input type="number" class="form-control" id="settings_balance" name="balance">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="settings_country" class="form-label">Country</label>
                            <input type="text" class="form-control" id="settings_country" name="country">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    // Add event listener for user_type changes (if applicable)
    var userTypeElement = document.getElementById('user_type');
    if (userTypeElement) {
        userTypeElement.addEventListener('change', function() {
            const companyField = document.getElementById('companyField');
            if (this.value === 'legal') {
                companyField.classList.remove('d-none');
            } else {
                companyField.classList.add('d-none');
                document.getElementById('company_name').value = ''; // Clear the field if hidden
            }
        });
    }

    // Email cell toggle expansion
    document.querySelectorAll('.email-cell').forEach(cell => {
        cell.addEventListener('click', function() {
            if (this.classList.contains('expanded')) {
                this.classList.remove('expanded');
                this.innerHTML = this.getAttribute('data-full-email').substring(0, 20) + '...';
            } else {
                this.classList.add('expanded');
                this.innerHTML = this.getAttribute('data-full-email');
            }
        });
    });

    // Handle Add Admin Form submission
    document.getElementById('addAdminForm').onsubmit = function(event) {
        event.preventDefault();
        var formData = new FormData(this);
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "add_admin.php", true);

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                if (xhr.responseText.trim() === 'success') {
                    var addAdminModal = bootstrap.Modal.getInstance(document.getElementById('addAdminModal'));
                    addAdminModal.hide();
                    window.location.reload();
                } else {
                    var adminMessage = document.getElementById('adminMessage');
                    adminMessage.className = 'alert alert-danger';
                    adminMessage.textContent = 'Failed to add admin. Please try again.';
                    adminMessage.classList.remove('d-none');
                }
            }
        };

        xhr.send(formData);
    };

    // Handle Add User Form submission
document.getElementById('addUserForm').onsubmit = function(event) {
    event.preventDefault(); // Предотвращаем отправку формы

    var formData = new FormData(this);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "add_user.php", true); // Отправляем форму на add_user.php через POST

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var response = xhr.responseText.trim(); // Получаем ответ от сервера

            if (response === 'success') {
                // Если успешно, перезагружаем страницу
                window.location.reload();
            } else {
                // Если произошла ошибка, выводим её
                alert(response); // Выводим текст ошибки
            }
        }
    };

    xhr.send(formData); // Отправляем данные формы
};

// Open Settings Modal
window.openSettings = function(userId, userEmail, firstLastName, phoneNumber, role, balance, country) {
    // Set values in the modal for editing
    document.getElementById('settingsUserId').value = userId;
    document.getElementById('settings_email').value = userEmail;
    document.getElementById('settings_first_last_name').value = firstLastName; // Use the consolidated field
    document.getElementById('settings_phone_number').value = phoneNumber || ''; // Default to empty if null
    document.getElementById('settings_role').value = role;
    document.getElementById('settings_balance').value = balance;
    document.getElementById('settings_country').value = country || 'Armenia'; // Default to 'Armenia' if null

    // Show the modal
    var settingsModal = new bootstrap.Modal(document.getElementById('settingsModal'));
    settingsModal.show();
};

    // Open Add User Modal
    window.openAddUserModal = function() {
        var addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'));
        addUserModal.show();
    };

    // Open Add Admin Modal
    window.openAddAdminModal = function() {
        var addAdminModal = new bootstrap.Modal(document.getElementById('addAdminModal'));
        addAdminModal.show();
    };
});
function confirmDelete(userId, userEmail) {
    if (confirm("Are you sure you want to delete user " + userEmail + "?")) {
        // Отправляем AJAX-запрос на удаление
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "delete_user.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var response = xhr.responseText.trim();
                if (response === 'success') {
                    // Успешное удаление, перезагружаем страницу
                    window.location.reload();
                } else {
                    // Ошибка при удалении
                    alert('Failed to delete user. Please try again.');
                }
            }
        };

        // Отправляем id пользователя для удаления
        xhr.send("id=" + userId);
    }
}

</script>


</body>
</html>
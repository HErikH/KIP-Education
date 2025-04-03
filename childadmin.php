<?php
session_start();
include 'db_connect.php';

// Session check
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}

// Fetching children data
$childrenResult = $conn->query("SELECT * FROM children");

include 'headeradmin.php';

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Children Management</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
    </style>
</head>
<body>
    <div class="content">
        <div class="container mt-4">
            <div class="content-header">
                <h1>Children Management</h1>
                <p>Manage all children in the system here.</p>
            </div>

            <!-- Add Child Button -->
            <div class="text-end mb-3">
                <button class="btn btn-success btn-lg shadow-sm" onclick="openAddChildModal()">
                    <i class="fas fa-user-plus"></i> Add New Child
                </button>
            </div>

            <!-- Children Table -->
            <div class="card mt-4">
                <div class="card-header" data-bs-toggle="collapse" data-bs-target="#childrenTable">
                    <h5>Children List</h5>
                </div>
                <div id="childrenTable" class="collapse show">
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Company Name</th>
                                    <th>Phone Number</th>
                                    <th>Points</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($child = $childrenResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $child['id']; ?></td>
                                    <td><?php echo $child['first_name']; ?></td>
                                    <td><?php echo $child['last_name']; ?></td>
                                    <td><?php echo $child['company_name']; ?></td>
                                    <td><?php echo $child['phone_number']; ?></td>
                                    <td><?php echo $child['points']; ?></td>
                                    <td class="action-buttons">
                                        <button class="btn btn-danger btn-sm" onclick="confirmDelete('<?php echo $child['id']; ?>', '<?php echo $child['first_name']; ?>')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                        <button class="btn btn-secondary btn-sm" onclick="openSettings('<?php echo $child['id']; ?>', '<?php echo $child['first_name']; ?>', '<?php echo $child['last_name']; ?>', '<?php echo $child['company_name']; ?>', '<?php echo $child['phone_number']; ?>', '<?php echo $child['points']; ?>')">
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
        </div>
    </div>

<!-- Add Child Modal -->
<div class="modal fade" id="addChildModal" tabindex="-1" aria-labelledby="addChildModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addChildModalLabel">Add New Child</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addChildForm">
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name*</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name*</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="company_name" class="form-label">Company Name</label>
                        <input type="text" class="form-control" id="company_name" name="company_name">
                    </div>
                    <div class="mb-3">
                        <label for="phone_number" class="form-label">Phone Number*</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                    </div>
                    <div class="mb-3">
                        <label for="points" class="form-label">Points*</label>
                        <input type="number" class="form-control" id="points" name="points" value="0" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Add Child</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Settings Modal -->
<div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="settingsModalLabel">Edit Child Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editChildForm">
                    <input type="hidden" id="settingsChildId" name="child_id">
                    <div class="mb-3">
                        <label for="settings_first_name" class="form-label">First Name*</label>
                        <input type="text" class="form-control" id="settings_first_name" name="first_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="settings_last_name" class="form-label">Last Name*</label>
                        <input type="text" class="form-control" id="settings_last_name" name="last_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="settings_company_name" class="form-label">Company Name</label>
                        <input type="text" class="form-control" id="settings_company_name" name="company_name">
                    </div>
                    <div class="mb-3">
                        <label for="settings_phone_number" class="form-label">Phone Number*</label>
                        <input type="text" class="form-control" id="settings_phone_number" name="phone_number" required>
                    </div>
                    <div class="mb-3">
                        <label for="settings_points" class="form-label">Points*</label>
                        <input type="number" class="form-control" id="settings_points" name="points" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function openAddChildModal() {
        var addChildModal = new bootstrap.Modal(document.getElementById('addChildModal'));
        addChildModal.show();
    }

    function confirmDelete(childId, childName) {
        if (confirm("Are you sure you want to delete " + childName + "?")) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "delete_child.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    window.location.reload();
                }
            };

            xhr.send("id=" + childId);
        }
    }
    
    // Handle the edit child form submission with AJAX
document.getElementById('editChildForm').onsubmit = function(event) {
    event.preventDefault(); // Prevent the default form submission

    var formData = new FormData(this); // Collect form data
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "edit_child.php", true); // Send the data to edit_child.php

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            if (xhr.responseText.trim() === 'success') {
                // If successfully edited, reload the children list
                fetchChildren();
                // Hide the modal
                var settingsModal = bootstrap.Modal.getInstance(document.getElementById('settingsModal'));
                settingsModal.hide();
            } else {
                alert('Failed to update child. Please try again.');
            }
        }
    };

    xhr.send(formData); // Send form data via AJAX
};

// Open the settings modal with current data
function openSettings(childId, firstName, lastName, companyName, phoneNumber, points) {
    document.getElementById('settingsChildId').value = childId;
    document.getElementById('settings_first_name').value = firstName;
    document.getElementById('settings_last_name').value = lastName;
    document.getElementById('settings_company_name').value = companyName;
    document.getElementById('settings_phone_number').value = phoneNumber;
    document.getElementById('settings_points').value = points;

    var settingsModal = new bootstrap.Modal(document.getElementById('settingsModal'));
    settingsModal.show();
}
    </script>
<script>
// Add Child form submission with AJAX
document.getElementById('addChildForm').onsubmit = function(event) {
    event.preventDefault(); // Prevent the default form submission

    var formData = new FormData(this); // Collect form data
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "add_child.php", true); // Send the data to add_child.php

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            // Handle the response from the server
            if (xhr.responseText.trim() === 'success') {
                // If successfully added, reload the children list
                fetchChildren();
                // Hide the modal
                var addChildModal = bootstrap.Modal.getInstance(document.getElementById('addChildModal'));
                addChildModal.hide();
            } else {
                alert('Failed to add child. Please try again.');
            }
        }
    };

    xhr.send(formData); // Send form data via AJAX
};

// Function to fetch and reload the children list
function fetchChildren() {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "fetch_children.php", true); // Assume fetch_children.php returns the updated table HTML

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            // Replace the children table body with the new data
            document.querySelector('tbody').innerHTML = xhr.responseText;
        }
    };

    xhr.send();
}
</script>
</body>
</html>

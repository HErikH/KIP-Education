<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
  header("Location: login");
  exit();
}

if ($_SESSION["role"] !== "admin") {
  header("Location: index");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Admin Dashboard</title>
    <link rel="stylesheet" href="manageStudents.css">
</head>

<body>
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="room-info">
                <div class="room-details">
                    <h1 id="roomName">Loading...</h1>
                    <p>Class ID: <span class="class-id" id="classId">-</span></p>
                    <p>Teacher: <span id="teacherName">-</span></p>
                </div>
                <div class="room-stats">
                    <div class="stat">
                        <div class="number" id="currentStudents">0</div>
                        <div class="label">Current</div>
                    </div>
                    <div class="stat">
                        <div class="number" id="maxStudents">0</div>
                        <div class="label">Maximum</div>
                    </div>
                </div>
                <a href="rooms_dashboard.php" class="btn-back">← Back to Rooms</a>
            </div>
        </div>

        <!-- Two Column Layout -->
        <div class="content-grid">
            <!-- Current Students -->
            <div class="students-section">
                <div class="section-header">
                    <h2>Current Students</h2>
                    <button class="btn-bulk" id="bulkRemoveBtn" onclick="bulkRemoveStudents()" disabled>
                        Remove Selected
                    </button>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th width="30">
                                    <input type="checkbox" class="checkbox" id="selectAllCurrent"
                                        onchange="toggleAllCurrent()">
                                </th>
                                <th>Student Name</th>
                                <th>Email</th>
                                <th>Enrolled</th>
                                <th width="80">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="currentStudentsBody">
                            <tr>
                                <td colspan="5" class="loading">Loading current students...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Add Students -->
            <div class="students-section">
                <div class="section-header">
                    <h2>Add Students</h2>
                    <button class="btn-bulk" style="background: #28a745;" id="bulkAddBtn" onclick="bulkAddStudents()"
                        disabled>
                        Add Selected
                    </button>
                </div>

                <div class="search-box">
                    <input type="text" id="studentSearch" placeholder="Search students by name or email..."
                        onkeyup="searchStudents()">
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th width="30">
                                    <input type="checkbox" class="checkbox" id="selectAllAvailable"
                                        onchange="toggleAllAvailable()">
                                </th>
                                <th>Student Name</th>
                                <th>Email</th>
                                <th width="80">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="availableStudentsBody">
                            <tr>
                                <td colspan="4" class="loading">Loading available students...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        let roomId = null;
        let currentStudents = [];
        let availableStudents = [];
        let selectedCurrent = new Set();
        let selectedAvailable = new Set();

        document.addEventListener('DOMContentLoaded', function () {
            // Get room ID from URL
            const urlParams = new URLSearchParams(window.location.search);
            roomId = urlParams.get('room_id');

            if (!roomId) {
                showNotification('Room ID not provided', 'error');
                return;
            }

            loadRoomData();
            loadCurrentStudents();
            loadAvailableStudents();
        });

        // Load room information
        async function loadRoomData() {
            try {
                const response = await fetch(`ajax/get_room_details.php?id=${roomId}`);
                const data = await response.json();

                if (data.success) {
                    const room = data.room;
                    document.getElementById('roomName').textContent = room.room_name;
                    document.getElementById('classId').textContent = room.class_id;
                    document.getElementById('teacherName').textContent = room.teacher_name || 'Not assigned';
                    document.getElementById('maxStudents').textContent = room.max_students;
                    document.getElementById('currentStudents').textContent = room.current_students;
                } else {
                    showNotification('Error loading room data: ' + data.message, 'error');
                }
            } catch (error) {
                console.error('Error loading room data:', error);
                showNotification('Error loading room data', 'error');
            }
        }

        // Load current students
        async function loadCurrentStudents() {
            try {
                const response = await fetch(`ajax/get_room_students.php?room_id=${roomId}`);
                const data = await response.json();

                if (data.success) {
                    currentStudents = data.students;
                    displayCurrentStudents();
                    updateStudentCount();
                } else {
                    document.getElementById('currentStudentsBody').innerHTML =
                        '<tr><td colspan="5" class="empty-state">Error loading students</td></tr>';
                }
            } catch (error) {
                console.error('Error loading current students:', error);
                document.getElementById('currentStudentsBody').innerHTML =
                    '<tr><td colspan="5" class="empty-state">Error loading students</td></tr>';
            }
        }

        // Load available students
        async function loadAvailableStudents() {
            try {
                const response = await fetch(`ajax/get_available_students.php?room_id=${roomId}`);
                const data = await response.json();

                if (data.success) {
                    availableStudents = data.students;
                    displayAvailableStudents();
                } else {
                    document.getElementById('availableStudentsBody').innerHTML =
                        '<tr><td colspan="4" class="empty-state">Error loading students</td></tr>';
                }
            } catch (error) {
                console.error('Error loading available students:', error);
                document.getElementById('availableStudentsBody').innerHTML =
                    '<tr><td colspan="4" class="empty-state">Error loading students</td></tr>';
            }
        }

        // Display current students
        function displayCurrentStudents() {
            const tbody = document.getElementById('currentStudentsBody');

            if (currentStudents.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="empty-state">No students enrolled in this room</td></tr>';
                return;
            }

            tbody.innerHTML = currentStudents.map(student => `
                <tr>
                    <td>
                        <input type="checkbox" class="checkbox" value="${student.id}" 
                               onchange="toggleCurrentStudent(${student.id})">
                    </td>
                    <td>${student.first_last_name}</td>
                    <td>${student.email}</td>
                    <td>${formatDate(student.enrolled_at)}</td>
                    <td>
                        <button class="btn-sm btn-remove" onclick="removeStudent(${student.id})" title="Remove">
                            Remove
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        // Display available students  
        function displayAvailableStudents(students = availableStudents) {
            const tbody = document.getElementById('availableStudentsBody');

            if (students.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="empty-state">No available students found</td></tr>';
                return;
            }

            tbody.innerHTML = students.map(student => `
                <tr>
                    <td>
                        <input type="checkbox" class="checkbox" value="${student.id}"
                               onchange="toggleAvailableStudent(${student.id})">
                    </td>
                    <td>${student.first_last_name}</td>
                    <td>${student.email}</td>
                    <td>
                        <button class="btn-sm btn-add" onclick="addStudent(${student.id})" title="Add">
                            Add
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        // Search students
        function searchStudents() {
            const search = document.getElementById('studentSearch').value.toLowerCase();
            const filtered = availableStudents.filter(student =>
                student.first_last_name.toLowerCase().includes(search) ||
                student.email.toLowerCase().includes(search)
            );
            displayAvailableStudents(filtered);
        }

        // Toggle current student selection
        function toggleCurrentStudent(studentId) {
            if (selectedCurrent.has(studentId)) {
                selectedCurrent.delete(studentId);
            } else {
                selectedCurrent.add(studentId);
            }
            updateBulkButtons();
        }

        // Toggle available student selection
        function toggleAvailableStudent(studentId) {
            if (selectedAvailable.has(studentId)) {
                selectedAvailable.delete(studentId);
            } else {
                selectedAvailable.add(studentId);
            }
            updateBulkButtons();
        }

        // Toggle all current students
        function toggleAllCurrent() {
            const checkbox = document.getElementById('selectAllCurrent');
            const checkboxes = document.querySelectorAll('#currentStudentsBody input[type="checkbox"]');

            checkboxes.forEach(cb => {
                cb.checked = checkbox.checked;
                const studentId = parseInt(cb.value);
                if (checkbox.checked) {
                    selectedCurrent.add(studentId);
                } else {
                    selectedCurrent.delete(studentId);
                }
            });
            updateBulkButtons();
        }

        // Toggle all available students
        function toggleAllAvailable() {
            const checkbox = document.getElementById('selectAllAvailable');
            const checkboxes = document.querySelectorAll('#availableStudentsBody input[type="checkbox"]');

            checkboxes.forEach(cb => {
                cb.checked = checkbox.checked;
                const studentId = parseInt(cb.value);
                if (checkbox.checked) {
                    selectedAvailable.add(studentId);
                } else {
                    selectedAvailable.delete(studentId);
                }
            });
            updateBulkButtons();
        }

        // Update bulk action buttons
        function updateBulkButtons() {
            document.getElementById('bulkRemoveBtn').disabled = selectedCurrent.size === 0;
            document.getElementById('bulkAddBtn').disabled = selectedAvailable.size === 0;
        }

        // Add single student
        async function addStudent(studentId) {
            try {
                const response = await fetch('ajax/enroll_student.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ room_id: roomId, student_id: studentId })
                });

                const result = await response.json();

                if (result.success) {
                    showNotification('Student added successfully', 'success');
                    refreshData();
                } else {
                    showNotification(result.message, 'error');
                }
            } catch (error) {
                console.error('Error adding student:', error);
                showNotification('Error adding student', 'error');
            }
        }

        // Remove single student
        async function removeStudent(studentId) {
            if (!confirm('Are you sure you want to remove this student from the room?')) return;

            try {
                const response = await fetch('ajax/remove_student.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ room_id: roomId, student_id: studentId })
                });

                const result = await response.json();

                if (result.success) {
                    showNotification('Student removed successfully', 'success');
                    refreshData();
                } else {
                    showNotification(result.message, 'error');
                }
            } catch (error) {
                console.error('Error removing student:', error);
                showNotification('Error removing student', 'error');
            }
        }

        // Bulk add students
        async function bulkAddStudents() {
            if (selectedAvailable.size === 0) return;

            if (!confirm(`Add ${selectedAvailable.size} students to this room?`)) return;

            try {
                const studentIds = Array.from(selectedAvailable);
                const response = await fetch('ajax/bulk_enroll_students.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ room_id: roomId, student_ids: studentIds })
                });

                const result = await response.json();

                if (result.success) {
                    showNotification(`${result.added_count} students added successfully`, 'success');
                    selectedAvailable.clear();
                    refreshData();
                } else {
                    showNotification(result.message, 'error');
                }
            } catch (error) {
                console.error('Error adding students:', error);
                showNotification('Error adding students', 'error');
            }
        }

        // Bulk remove students
        async function bulkRemoveStudents() {
            if (selectedCurrent.size === 0) return;

            if (!confirm(`Remove ${selectedCurrent.size} students from this room?`)) return;

            try {
                const studentIds = Array.from(selectedCurrent);
                const response = await fetch('ajax/bulk_remove_students.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ room_id: roomId, student_ids: studentIds })
                });

                const result = await response.json();

                if (result.success) {
                    showNotification(`${result.removed_count} students removed successfully`, 'success');
                    selectedCurrent.clear();
                    refreshData();
                } else {
                    showNotification(result.message, 'error');
                }
            } catch (error) {
                console.error('Error removing students:', error);
                showNotification('Error removing students', 'error');
            }
        }

        // Refresh all data
        function refreshData() {
            loadRoomData();
            loadCurrentStudents();
            loadAvailableStudents();

            // Clear selections
            selectedCurrent.clear();
            selectedAvailable.clear();
            document.getElementById('selectAllCurrent').checked = false;
            document.getElementById('selectAllAvailable').checked = false;
            updateBulkButtons();
        }

        // Update student count display
        function updateStudentCount() {
            document.getElementById('currentStudents').textContent = currentStudents.length;
        }

        // Format date
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }

        // Show notification
        function showNotification(message, type) {
            // Remove existing notifications
            const existing = document.querySelector('.notification');
            if (existing) existing.remove();

            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;

            document.body.appendChild(notification);

            // Remove after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    </script>
</body>

</html>
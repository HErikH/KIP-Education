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
    <title>Create Room - Admin Dashboard</title>
    <link rel="stylesheet" href="createRoom.css">
</head>

<body>
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 id="pageTitle">Create New Room</h1>
            <a href="rooms_dashboard.php" class="btn-back">← Back to Rooms</a>
        </div>

        <!-- Success/Error Messages -->
        <div id="messageContainer"></div>

        <!-- Form Container -->
        <div class="form-container">
            <!-- Class ID Preview (only for create) -->
            <div class="class-id-preview" id="classIdPreview" style="display: none;">
                <h3>Class ID will be auto-generated:</h3>
                <div class="class-id" id="classIdDisplay">classId-20240915-ABC123</div>
            </div>

            <form id="roomForm">
                <input type="hidden" id="roomId" name="room_id">

                <div class="form-group">
                    <label for="roomName">Room Name *</label>
                    <input type="text" id="roomName" name="room_name" required>
                    <small>Enter a descriptive name for the room (e.g., "Beginner English A1", "Advanced
                        Conversation")</small>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"
                        placeholder="Optional description of the room..."></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="teacherId">Assign Teacher *</label>
                        <select id="teacherId" name="teacher_id" required>
                            <option value="">Select a teacher...</option>
                        </select>
                        <small>Choose the teacher who will manage this room</small>
                    </div>

                    <div class="form-group">
                        <label for="maxStudents">Maximum Students</label>
                        <input type="number" id="maxStudents" name="max_students" value="5" min="1" max="20">
                        <small>Maximum number of students allowed in this room</small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="expiresAt">Expiration Date</label>
                        <input type="datetime-local" id="expiresAt" name="expires_at" required>
                        <small>Leave empty for rooms that don't expire</small>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <small>Room status - inactive rooms are not accessible to students</small>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="history.back()">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Create Room</button>
                    <button type="submit" class="btn btn-success" id="saveAndManageBtn" style="display: none;">Save &
                        Manage Students</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let isEditMode = false;
        let currentRoomId = null;

        document.addEventListener('DOMContentLoaded', async function () {
            // Check if editing existing room
            const urlParams = new URLSearchParams(window.location.search);
            currentRoomId = urlParams.get('id');

            await loadTeachers();

            if (currentRoomId) {
                isEditMode = true;
                document.getElementById('pageTitle').textContent = 'Edit Room';
                document.getElementById('saveBtn').textContent = 'Update Room';
                document.getElementById('saveAndManageBtn').style.display = 'inline-block';
                await loadRoomData(currentRoomId);
            } else {
                document.getElementById('classIdPreview').style.display = 'block';
                generateClassIdPreview();
            }

            // Get the current date
            const today = new Date();

            // Get the date 7 days from today
            const sevenDaysFromNow = new Date();
            sevenDaysFromNow.setDate(today.getDate() + 7);

            // Format dates to YYYY-MM-DD (ISO format)
            const todayFormatted = today.toISOString().slice(0, 16);
            const sevenDaysFormatted = sevenDaysFromNow.toISOString().slice(0, 16);

            // Set the min and max attributes of the expiresAt date input field
            const dateInput = document.getElementById('expiresAt');

            dateInput.setAttribute('min', todayFormatted);  // today's date
            dateInput.setAttribute('max', sevenDaysFormatted);  // 7 days from today

            // Regenerate class ID preview every few seconds (only for create mode)
            if (!isEditMode) {
                setInterval(generateClassIdPreview, 10000);
            }

            const studentsInput = document.getElementById("maxStudents");

            studentsInput.addEventListener('input', (e) => {
                if (Number(e.target.value) < Number(e.target.min)) {
                    e.target.value = e.target.min
                } else if (Number(e.target.value) > Number(e.target.max)) {
                    e.target.value = e.target.max
                }
            })
        });

        // Generate class ID preview
        function generateClassIdPreview() {
            const date = new Date();
            const dateStr = date.getFullYear() + String(date.getMonth() + 1).padStart(2, '0') + String(date.getDate()).padStart(2, '0');
            const randomStr = Math.random().toString(36).substring(2, 8).toUpperCase();
            const classId = `classId-${dateStr}-${randomStr}`;
            document.getElementById('classIdDisplay').textContent = classId;
        }

        // Load teachers
        async function loadTeachers() {
            try {
                const response = await fetch('ajax/get_teachers.php');
                const data = await response.json();

                if (data.success) {
                    const teacherSelect = document.getElementById('teacherId');
                    data.teachers.forEach(teacher => {
                        const option = document.createElement('option');
                        option.value = teacher.id;
                        option.textContent = `${teacher.first_last_name} (${teacher.email})`;
                        teacherSelect.appendChild(option);
                    });
                } else {
                    showMessage('Error loading teachers: ' + data.message, 'error');
                }
            } catch (error) {
                console.error('Error loading teachers:', error);
                showMessage('Error loading teachers', 'error');
            }
        }

        // Load room data for editing
        async function loadRoomData(roomId) {
            try {
                const response = await fetch(`ajax/get_room_details.php?id=${roomId}`);
                const data = await response.json();

                if (data.success) {
                    const room = data.room;

                    document.getElementById('roomId').value = room.room_id;
                    document.getElementById('roomName').value = room.room_name;
                    document.getElementById('description').value = room.description || '';
                    document.getElementById('teacherId').value = room.teacher_id;
                    document.getElementById('maxStudents').value = room.max_students;
                    document.getElementById('status').value = room.status;

                    if (room.expires_at) {
                        const date = new Date(room.expires_at);
                        document.getElementById('expiresAt').value = date.toISOString().slice(0, 16);
                    }

                    // Show current class ID
                    if (room.class_id) {
                        document.getElementById('classIdPreview').style.display = 'block';
                        document.getElementById('classIdDisplay').textContent = room.class_id;
                        document.querySelector('.class-id-preview h3').textContent = 'Current Class ID:';
                    }
                } else {
                    showMessage('Error loading room data: ' + data.message, 'error');
                }
            } catch (error) {
                console.error('Error loading room data:', error);
                showMessage('Error loading room data', 'error');
            }
        }

        // Handle form submission
        document.getElementById('roomForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());

            // Remove empty values
            Object.keys(data).forEach(key => {
                if (data[key] === '') {
                    delete data[key];
                }
            });

            const saveAndManage = e.submitter.id === 'saveAndManageBtn';

            try {
                const url = isEditMode ? 'ajax/update_room.php' : 'ajax/create_room.php';
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    showMessage(result.message, 'success');

                    if (saveAndManage) {
                        const roomId = isEditMode ? currentRoomId : result.room_id;
                        window.location.href = `manage_students.php?room_id=${roomId}`;
                    } else {
                        window.location.href = 'rooms_dashboard.php';
                    }
                } else {
                    showMessage(result.message, 'error');
                }
            } catch (error) {
                console.error('Error saving room:', error);
                showMessage('Error saving room', 'error');
            }
        });

        // Show message
        function showMessage(message, type) {
            const container = document.getElementById('messageContainer');
            const alertClass = type === 'success' ? 'alert-success' : 'alert-error';

            container.innerHTML = `
                <div class="alert ${alertClass}">
                    ${message}
                </div>
            `;

            // Auto-hide after 5 seconds for success messages
            if (type === 'success') {
                setTimeout(() => {
                    container.innerHTML = '';
                }, 5000);
            }
        }
    </script>
</body>

</html>
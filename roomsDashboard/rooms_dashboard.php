<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login");
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: index");
    exit();
}

include '../headeradmin.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Management - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
</head>

<body>
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>Room Management</h1>
            <p>Manage English learning rooms, assign teachers and students</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid" id="statsGrid">
            <div class="stat-card active">
                <h3>Active Rooms</h3>
                <div class="number" id="activeRooms">-</div>
            </div>
            <div class="stat-card inactive">
                <h3>Inactive Rooms</h3>
                <div class="number" id="inactiveRooms">-</div>
            </div>
            <div class="stat-card expired">
                <h3>Expired Rooms</h3>
                <div class="number" id="expiredRooms">-</div>
            </div>
            <div class="stat-card warning">
                <h3>Expiring Soon</h3>
                <div class="number" id="expiringSoon">-</div>
            </div>
        </div>

        <!-- Controls -->
        <div class="controls">
            <div class="controls-top">
                <a href="create_room.php" class="btn-primary">Create New Room</a>
                <button class="btn-secondary" onclick="refreshData()">Refresh</button>
                <button class="btn-secondary" onclick="cleanupExpired()">Cleanup Expired</button>
            </div>

            <div class="filters">
                <div class="filter-group">
                    <label>Status</label>
                    <select id="statusFilter" onchange="filterRooms()">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Teacher</label>
                    <select id="teacherFilter" onchange="filterRooms()">
                        <option value="">All Teachers</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Search</label>
                    <input type="text" id="searchFilter" placeholder="Room name, Class ID, Teacher..."
                        onkeyup="filterRooms()">
                </div>
            </div>
        </div>

        <!-- Rooms Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Room Name</th>
                        <th>Class ID</th>
                        <th>Teacher</th>
                        <th>Students</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Expires</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="roomsTableBody">
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: #666;">
                            Loading rooms...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Delete</h3>
                <button class="modal-close" onclick="closeModal('deleteModal')">&times;</button>
            </div>
            <p>Are you sure you want to delete this room? This action cannot be undone and will remove all student
                enrollments.</p>
            <div style="margin-top: 20px; text-align: right;">
                <button class="btn-secondary" onclick="closeModal('deleteModal')"
                    style="margin-right: 10px;">Cancel</button>
                <button class="btn-danger" onclick="confirmDelete()">Delete Room</button>
            </div>
        </div>
    </div>

    <script>
        let currentRooms = [];
        let deleteRoomId = null;

        // Load initial data
        document.addEventListener('DOMContentLoaded', function () {
            loadDashboardData();
            loadTeachers();
        });

        // Load dashboard statistics and rooms
        async function loadDashboardData() {
            try {
                // Load stats
                const statsResponse = await fetch('ajax/get_dashboard_stats.php');
                const stats = await statsResponse.json();
                updateStatsDisplay(stats);

                // Load rooms
                await loadRooms();
            } catch (error) {
                console.error('Error loading dashboard data:', error);
            }
        }

        // Update statistics display
        function updateStatsDisplay(stats) {
            document.getElementById('activeRooms').textContent = stats.rooms_by_status?.active || 0;
            document.getElementById('inactiveRooms').textContent = stats.rooms_by_status?.inactive || 0;
            document.getElementById('expiredRooms').textContent = stats.rooms_by_status?.expired || 0;
            document.getElementById('expiringSoon').textContent = stats.expiring_soon || 0;
        }

        // Load rooms data
        async function loadRooms() {
            try {
                const response = await fetch('ajax/get_rooms.php');
                const data = await response.json();

                if (data.success) {
                    currentRooms = data.rooms;
                    displayRooms(currentRooms);
                } else {
                    console.error('Error loading rooms:', data.message);
                }
            } catch (error) {
                console.error('Error loading rooms:', error);
                document.getElementById('roomsTableBody').innerHTML =
                    '<tr><td colspan="8" style="text-align: center; padding: 40px; color: #dc3545;">Error loading rooms</td></tr>';
            }
        }

        // Load teachers for filter
        async function loadTeachers() {
            try {
                const response = await fetch('ajax/get_teachers.php');
                const data = await response.json();

                if (data.success) {
                    const teacherFilter = document.getElementById('teacherFilter');
                    data.teachers.forEach(teacher => {
                        const option = document.createElement('option');
                        option.value = teacher.id;
                        option.textContent = teacher.first_last_name;
                        teacherFilter.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading teachers:', error);
            }
        }

        // Display rooms in table
        function displayRooms(rooms) {
            const tbody = document.getElementById('roomsTableBody');

            if (rooms.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 40px; color: #666;">No rooms found</td></tr>';
                return;
            }

            tbody.innerHTML = rooms.map(room => `
                <tr>
                    <td><strong>${room.room_name}</strong></td>
                    <td><code>${room.class_id}</code></td>
                    <td>${room.teacher_name || 'Not assigned'}</td>
                    <td>${room.current_students}/${room.max_students}</td>
                    <td><span class="status ${room.status}">${room.status}</span></td>
                    <td>${formatDate(room.created_at)}</td>
                    <td>${room.expires_at ? formatDate(room.expires_at) : 'Never'}</td>
                    <td>
                        <div class="actions">
                            <button class="btn-sm btn-edit" onclick="editRoom(${room.room_id})" title="Edit">Edit</button>
                            <button class="btn-sm btn-students" onclick="manageStudents(${room.room_id})" title="Students">Students</button>
                            <button class="btn-sm btn-delete" onclick="deleteRoom(${room.room_id})" title="Delete">Delete</button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        // Filter rooms
        function filterRooms() {
            const statusFilter = document.getElementById('statusFilter').value;
            const teacherFilter = document.getElementById('teacherFilter').value;
            const searchFilter = document.getElementById('searchFilter').value.toLowerCase();

            let filteredRooms = currentRooms.filter(room => {
                const statusMatch = !statusFilter || room.status === statusFilter;
                const teacherMatch = !teacherFilter || room.teacher_id == teacherFilter;
                const searchMatch = !searchFilter ||
                    room.room_name.toLowerCase().includes(searchFilter) ||
                    room.class_id.toLowerCase().includes(searchFilter) ||
                    (room.teacher_name && room.teacher_name.toLowerCase().includes(searchFilter));

                return statusMatch && teacherMatch && searchMatch;
            });

            displayRooms(filteredRooms);
        }

        // Format date
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }

        // Edit room
        function editRoom(roomId) {
            window.location.href = `create_room.php?id=${roomId}`;
        }

        // Manage students
        function manageStudents(roomId) {
            window.location.href = `manage_students.php?room_id=${roomId}`;
        }

        // Delete room
        function deleteRoom(roomId) {
            deleteRoomId = roomId;
            document.getElementById('deleteModal').classList.add('active');
        }

        // Confirm delete
        async function confirmDelete() {
            if (!deleteRoomId) return;

            try {
                const response = await fetch('ajax/delete_room.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ room_id: deleteRoomId })
                });

                const result = await response.json();

                if (result.success) {
                    closeModal('deleteModal');
                    loadDashboardData(); // Reload data
                    showNotification('Room deleted successfully', 'success');
                } else {
                    showNotification(result.message, 'error');
                }
            } catch (error) {
                console.error('Error deleting room:', error);
                showNotification('Error deleting room', 'error');
            }

            deleteRoomId = null;
        }

        // Close modal
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        // Refresh data
        function refreshData() {
            loadDashboardData();
        }

        // Cleanup expired rooms
        async function cleanupExpired() {
            if (!confirm('This will mark all expired rooms as expired. Continue?')) return;

            try {
                const response = await fetch('ajax/cleanup_expired.php', { method: 'POST' });
                const result = await response.json();

                if (result.success) {
                    showNotification(result.message, 'success');
                    loadDashboardData();
                } else {
                    showNotification(result.message, 'error');
                }
            } catch (error) {
                console.error('Error cleaning up expired rooms:', error);
                showNotification('Error cleaning up expired rooms', 'error');
            }
        }

        // Show notification
        function showNotification(message, type) {
            // Create notification element
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 6px;
                color: white;
                font-weight: 500;
                z-index: 10000;
                box-shadow: 0 4px 8px rgba(0,0,0,0.2);
                background: ${type === 'success' ? '#28a745' : '#dc3545'};
            `;
            notification.textContent = message;

            document.body.appendChild(notification);

            // Remove after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Close modal when clicking outside
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('modal')) {
                e.target.classList.remove('active');
            }
        });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
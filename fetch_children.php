<?php
include 'db_connect.php';

$childrenResult = $conn->query("SELECT * FROM children");

while ($child = $childrenResult->fetch_assoc()) {
    echo "<tr>
            <td>{$child['id']}</td>
            <td>{$child['first_name']}</td>
            <td>{$child['last_name']}</td>
            <td>{$child['company_name']}</td>
            <td>{$child['phone_number']}</td>
            <td>{$child['points']}</td>
            <td class='action-buttons'>
                <button class='btn btn-danger btn-sm' onclick=\"confirmDelete('{$child['id']}', '{$child['first_name']}')\">
                    <i class='fas fa-trash'></i> Delete
                </button>
                <button class='btn btn-secondary btn-sm' onclick=\"openSettings('{$child['id']}', '{$child['first_name']}', '{$child['last_name']}', '{$child['company_name']}', '{$child['phone_number']}', '{$child['points']}')\">
                    <i class='fas fa-cog'></i> Settings
                </button>
            </td>
        </tr>";
}

$conn->close();
?>

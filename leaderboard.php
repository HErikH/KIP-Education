<?php
// Start a separate session for the quiz
session_name('quiz_session');
session_start();
include 'db_connect.php';
require_once 'helpers.php';

// Include the separated header
include 'headerchild.php';

// Fetch leaderboard data from the database (excluding phone numbers)
$leaderboardQuery = "SELECT first_name, last_name, company_name, points FROM children ORDER BY points DESC";
$result = $conn->query($leaderboardQuery);
?>

<?php
// Start a separate session for the quiz
session_name('quiz_session');
session_start();
include 'db_connect.php';

// Include the separated header
include 'headerchild.php';

// Fetch leaderboard data from the database (excluding phone numbers)
$leaderboardQuery = "SELECT first_name, last_name, company_name, points FROM children ORDER BY points DESC";
$result = $conn->query($leaderboardQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard</title>
    <link rel="icon" href="<?= addMediaBaseUrl('resource/img/favicon.png') ?>" type="image/png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Body styling */
        body {
            background: linear-gradient(90deg, #4b6cb7 0%, #182848 100%);
            color: #ffffff;
            font-family: 'Comic Sans MS', cursive, sans-serif;
            margin: 0;
            padding: 0;
        }

        /* Adding space for footer */
        body::after {
            content: "";
            display: block;
            height: 100px;
        }
        .main-content {
            padding-top: 100px;
            text-align: center;
        }
        .leaderboard-title {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #ffffff;
        }
        .leaderboard-table {
            width: 40%;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #4b6cb7;
            color: #ffffff;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:first-child td:first-child {
            color: #ffd700; /* Gold for 1st place */
        }
        tr:nth-child(2) td:first-child {
            color: #c0c0c0; /* Silver for 2nd place */
        }
        tr:nth-child(3) td:first-child {
            color: #cd7f32; /* Bronze for 3rd place */
        }
        td:first-child {
            font-weight: bold;
            font-size: 22px;
        }
        td:last-child {
            font-size: 16px;
            text-align: right;
        }

        /* Media query for small screens */
        @media (max-width: 768px) {
            .leaderboard-table {
                width: 100%;
            }
            th, td {
                padding: 8px;
                font-size: 14px;
            }
            td:first-child {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>

    <!-- Main Content -->
    <div class="main-content">
        <div class="leaderboard-title">Leaderboard</div>
        
        <!-- Leaderboard Table -->
        <div class="leaderboard-table">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Participant</th>
                        <th>Company</th>
                        <th>Points</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $position = 1;
                    while ($row = $result->fetch_assoc()) {
                        $companyName = !empty($row['company_name']) ? $row['company_name'] : '—'; // Show '—' if company name is empty
                        echo "<tr>";
                        echo "<td>{$position}</td>";
                        echo "<td>{$row['first_name']} {$row['last_name']}</td>";
                        echo "<td>{$companyName}</td>";
                        echo "<td>{$row['points']}</td>";
                        echo "</tr>";
                        $position++;
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

</body>
</html>

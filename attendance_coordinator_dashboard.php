<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include the database connection file
require 'db.php';

session_start();
$coordinatorName = $_SESSION['coordinator_name'] ?? ''; // Assuming coordinator's name is stored in session

try {
    // Fetch approved leave forms
    $sqlLeave = "SELECT * FROM leave_form WHERE Status = 'Approved'";
    $stmtLeave = $conn->prepare($sqlLeave);
    $stmtLeave->execute();
    $leaveForms = $stmtLeave->get_result();

    // Fetch approved individual event forms
    $sqlIndividual = "SELECT * FROM individual_event_form WHERE Status = 'Approved'";
    $stmtIndividual = $conn->prepare($sqlIndividual);
    $stmtIndividual->execute();
    $individualForms = $stmtIndividual->get_result();

    // Fetch approved team event forms
    $sqlTeam = "SELECT * FROM team_event_form WHERE Status = 'Approved'";
    $stmtTeam = $conn->prepare($sqlTeam);
    $stmtTeam->execute();
    $teamForms = $stmtTeam->get_result();
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
} finally {
    // Close prepared statements and database connection
    $stmtLeave->close();
    $stmtIndividual->close();
    $stmtTeam->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Coordinator Dashboard</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f4f6f9;
        }
        .dashboard-title {
            text-align: center;
            margin: 20px 0;
            font-size: 2rem;
            font-weight: bold;
        }
        .form-container {
            margin: 20px auto;
            max-width: 900px;
        }
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: scale(1.02);
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2);
        }
        .form-list {
            margin-bottom: 30px;
        }
        .search-box {
            margin: 20px auto;
            width: 80%;
            text-align: center;
        }
    </style>
</head>
<body>

    <h1 class="dashboard-title">Attendance Coordinator Dashboard</h1>

    <!-- Search Box -->
    <div class="search-box">
        <input type="text" id="searchInput" class="form-control" placeholder="Search Forms...">
    </div>

    <div class="container form-container">
        <!-- Approved Leave Forms -->
        <div class="form-list">
            <h2>Approved Leave Forms</h2>
            <?php if ($leaveForms->num_rows > 0): ?>
                <?php while ($row = $leaveForms->fetch_assoc()): ?>
                    <div class="card p-3 mb-3">
                        <h4>Student: <?= $row['Name'] ?> (SRN: <?= $row['SRN'] ?>)</h4>
                        <p>Course: <?= $row['Course'] ?> | Section: <?= $row['Section'] ?></p>
                        <p>Email: <?= $row['Reva_Email'] ?> | Mentor: <?= $row['Mentor_Name'] ?></p>
                        <p>Description: <?= $row['Description'] ?></p>
                        <?php if (!empty($row['Supporting_files'])): ?>
                            <p>Supporting File: <a href="download_file.php?name=<?= $row['Name'] ?>&srn=<?= $row['SRN'] ?>">Download</a></p>
                        <?php endif; ?>
                        <form action="leave_attendance.php" method="post">
                            <button type="submit" class="btn btn-primary">View and Approve</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No approved leave forms found.</p>
            <?php endif; ?>
        </div>

        <!-- Approved Individual Event Forms -->
        <div class="form-list">
            <h2>Approved Individual Event Forms</h2>
            <?php if ($individualForms->num_rows > 0): ?>
                <?php while ($row = $individualForms->fetch_assoc()): ?>
                    <div class="card p-3 mb-3">
                        <h4>Student: <?= $row['Student_Name'] ?> (SRN: <?= $row['SRN'] ?>)</h4>
                        <p>Email: <?= $row['Student_Email'] ?> | Phone: <?= $row['Student_phone'] ?></p>
                        <p>Event Type: <?= $row['Event_Type'] ?> | Date: <?= $row['Date'] ?></p>
                        <form action="attendanceeventindividual.php" method="post">
                            <button type="submit" class="btn btn-primary">View and Approve</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No approved individual event forms found.</p>
            <?php endif; ?>
        </div>

        <!-- Approved Team Event Forms -->
        <div class="form-list">
            <h2>Approved Team Event Forms</h2>
            <?php if ($teamForms->num_rows > 0): ?>
                <?php while ($row = $teamForms->fetch_assoc()): ?>
                    <div class="card p-3 mb-3">
                        <h4>Team Members: <?= $row['Student_Names'] ?> (SRNs: <?= $row['SRNs'] ?>)</h4>
                        <p>Event Type: <?= $row['Event_Type'] ?> | Date: <?= $row['Date'] ?></p>
                        <form action="attendanceeventteam.php" method="post">
                            <button type="submit" class="btn btn-primary">View and Approve</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No approved team event forms found.</p>
            <?php endif; ?>
        </div>

        <!-- Download Table Data Section -->
        <div class="form-list">
            <h2>Download Table Data</h2>
            <ul class="list-group">
                <li class="list-group-item"><a href="export_table.php?table=leave_form" class="btn btn-success">Download Leave Form Data</a></li>
                <li class="list-group-item"><a href="export_table.php?table=individual_event_form" class="btn btn-success">Download Individual Event Form Data</a></li>
                <li class="list-group-item"><a href="export_table.php?table=team_event_form" class="btn btn-success">Download Team Event Form Data</a></li>
            </ul>
        </div>
    </div>

    <script>
        document.getElementById("searchInput").addEventListener("keyup", function () {
            let filter = this.value.toLowerCase();
            let cards = document.querySelectorAll(".card");
            cards.forEach(card => {
                let text = card.textContent.toLowerCase();
                card.style.display = text.includes(filter) ? "block" : "none";
            });
        });
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

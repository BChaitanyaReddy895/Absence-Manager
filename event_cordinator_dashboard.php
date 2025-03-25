<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include the database connection file
require 'db.php';

// Start session to get event coordinator details
session_start();
$eventCoordinatorName = $_SESSION['event_coordinator_name'] ?? '';
$eventCoordinatorEmail = $_SESSION['event_coordinator_email'] ?? '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Coordinator Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            text-align: center;
        }
        .container {
            width: 60%;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .search-bar {
            margin: 20px 0;
            text-align: center;
        }
        .search-bar input {
            width: 80%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-list {
            margin-top: 20px;
        }
        .form-item {
            background: #fff;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            text-align: left;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .form-item:hover {
            transform: scale(1.02);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }
        .form-item h3 {
            margin: 0 0 10px;
            color: #333;
        }
        .form-item p {
            margin: 5px 0;
            color: #666;
        }
        button {
            padding: 8px 15px;
            background-color: orange;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background-color: darkorange;
        }
    </style>
    <script>
        function searchForms() {
            let input = document.getElementById("searchInput").value.toLowerCase();
            let formItems = document.getElementsByClassName("form-item");

            for (let i = 0; i < formItems.length; i++) {
                let textContent = formItems[i].textContent.toLowerCase();
                if (textContent.includes(input)) {
                    formItems[i].style.display = "block";
                } else {
                    formItems[i].style.display = "none";
                }
            }
        }
    </script>
</head>
<body>

<div class="container">
    <h1>Event Coordinator Dashboard</h1>

    <div class="search-bar">
        <input type="text" id="searchInput" onkeyup="searchForms()" placeholder="Search for students, SRNs, or event types...">
    </div>

    <?php
    try {
        // Fetch pending individual event forms
        $sqlIndividual = "SELECT * FROM individual_event_form WHERE event_cordinator_name = ? AND Status = 'Pending'";
        $stmtIndividual = $conn->prepare($sqlIndividual);
        $stmtIndividual->bind_param("s", $eventCoordinatorName);
        $stmtIndividual->execute();
        $individualForms = $stmtIndividual->get_result();

        // Fetch pending team event forms
        $sqlTeam = "SELECT * FROM team_event_form WHERE event_cordinator_name = ? AND Status = 'Pending'";
        $stmtTeam = $conn->prepare($sqlTeam);
        $stmtTeam->bind_param("s", $eventCoordinatorName);
        $stmtTeam->execute();
        $teamForms = $stmtTeam->get_result();

        // Pending Individual Event Forms
        echo "<div class='form-list'><h2>Pending Individual Event Forms</h2>";
        if ($individualForms->num_rows > 0) {
            while ($row = $individualForms->fetch_assoc()) {
                echo "
                <div class='form-item'>
                    <h3>Student: " . htmlspecialchars($row['Student_Name']) . " (SRN: " . htmlspecialchars($row['SRN']) . ")</h3>
                    <p>Email: " . htmlspecialchars($row['Student_Email']) . " | Phone: " . htmlspecialchars($row['Student_phone']) . "</p>
                    <p>Event Type: " . htmlspecialchars($row['Event_Type']) . " | Date: " . htmlspecialchars($row['Date']) . "</p>
                    <p>Event Coordinator: " . htmlspecialchars($row['event_cordinator_name']) . "</p>
                    <form action='eventcoordinatorformindividual.php' method='post'>
                        <input type='hidden' name='student_name' value='" . htmlspecialchars($row['Student_Name']) . "'>
                        <input type='hidden' name='student_srn' value='" . htmlspecialchars($row['SRN']) . "'>
                        <input type='hidden' name='event_type' value='" . htmlspecialchars($row['Event_Type']) . "'>
                        <input type='hidden' name='event_date' value='" . htmlspecialchars($row['Date']) . "'>
                        <button type='submit'>Approve or Disapprove</button>
                    </form>
                </div>";
            }
        } else {
            echo "<p>No pending individual event forms found for you.</p>";
        }
        echo "</div>";

        // Pending Team Event Forms
        echo "<div class='form-list'><h2>Pending Team Event Forms</h2>";
        if ($teamForms->num_rows > 0) {
            while ($row = $teamForms->fetch_assoc()) {
                echo "
                <div class='form-item'>
                    <h3>Team Members: " . htmlspecialchars($row['Student_Names']) . " (SRNs: " . htmlspecialchars($row['SRNs']) . ")</h3>
                    <p>Event Type: " . htmlspecialchars($row['Event_Type']) . " | Date: " . htmlspecialchars($row['Date']) . "</p>
                    <p>Event Coordinator: " . htmlspecialchars($row['event_cordinator_name']) . "</p>
                    <form action='eventcoordinatorformteam.php' method='post'>
                        <input type='hidden' name='team_members' value='" . htmlspecialchars($row['Student_Names']) . "'>
                        <input type='hidden' name='srns' value='" . htmlspecialchars($row['SRNs']) . "'>
                        <input type='hidden' name='event_type' value='" . htmlspecialchars($row['Event_Type']) . "'>
                        <input type='hidden' name='event_date' value='" . htmlspecialchars($row['Date']) . "'>
                        <button type='submit'>Approve or Disapprove</button>
                    </form>
                </div>";
            }
        } else {
            echo "<p>No pending team event forms found for you.</p>";
        }
        echo "</div>";

    } catch (Exception $e) {
        echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    } finally {
        $stmtIndividual->close();
        $stmtTeam->close();
        $conn->close();
    }
    ?>

</div>

</body>
</html>

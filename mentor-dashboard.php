<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include the database connection file
require 'db.php';

// Start session to get mentor details
session_start();
$mentorName = $_SESSION['mentor_name'] ?? '';
$mentorEmail = $_SESSION['mentor_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentor Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: Arial, sans-serif;
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
        .search-box {
            margin: 20px auto;
            width: 80%;
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
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .form-item:hover {
            transform: scale(1.02);
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2);
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
</head>
<body>

<div class="container">
    <h1>Mentor Dashboard</h1>

    <!-- Search Box -->
    <div class="search-box">
        <input type="text" id="searchInput" class="form-control" placeholder="Search Forms...">
    </div>

    <?php
    try {
        // Fetch only pending leave forms for the mentor
        $sqlLeave = "SELECT * FROM leave_form WHERE Mentor_Name = ? AND Mentor_Email = ? AND Status = 'Pending'";
        $stmtLeave = $conn->prepare($sqlLeave);
        $stmtLeave->bind_param("ss", $mentorName, $mentorEmail);
        $stmtLeave->execute();
        $leaveForms = $stmtLeave->get_result();

        echo "<div class='form-list'><h2>Leave Forms</h2>";

        if ($leaveForms->num_rows > 0) {
            while ($row = $leaveForms->fetch_assoc()) {
                echo "
                <div class='form-item'>
                    <h3>Student: " . htmlspecialchars($row['Name']) . " (SRN: " . htmlspecialchars($row['SRN']) . ")</h3>
                    <p>Course: " . htmlspecialchars($row['Course']) . " | Section: " . htmlspecialchars($row['Section']) . "</p>
                    <p>Description: " . nl2br(htmlspecialchars($row['Description'])) . "</p>";

                if (!empty($row['Supporting_files'])) {
                    echo "<p>Supporting File: 
                        <a href='download_file.php?name=" . urlencode($row['Name']) . "&srn=" . urlencode($row['SRN']) . "'>Download</a></p>";
                }

                echo "
                    <form action='mentor_approval.php' method='post'>
                        <input type='hidden' name='student_name' value='" . htmlspecialchars($row['Name']) . "'>
                        <input type='hidden' name='student_srn' value='" . htmlspecialchars($row['SRN']) . "'>
                        <input type='hidden' name='course' value='" . htmlspecialchars($row['Course']) . "'>
                        <input type='hidden' name='section' value='" . htmlspecialchars($row['Section']) . "'>
                        <input type='hidden' name='description' value='" . htmlspecialchars($row['Description']) . "'>
                        <input type='hidden' name='mentor_name' value='" . htmlspecialchars($mentorName) . "'>
                        <input type='hidden' name='mentor_email' value='" . htmlspecialchars($mentorEmail) . "'>
                        <button type='submit'>View and Approve</button>
                    </form>
                </div>";
            }
        } else {
            echo "<p>No pending leave forms found for you.</p>";
        }

        echo "</div>";

    } catch (Exception $e) {
        echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    } finally {
        $stmtLeave->close();
        $conn->close();
    }
    ?>

</div>

<script>
    // Search Functionality
    document.getElementById("searchInput").addEventListener("keyup", function () {
        let filter = this.value.toLowerCase();
        let items = document.querySelectorAll(".form-item");
        items.forEach(item => {
            let text = item.textContent.toLowerCase();
            item.style.display = text.includes(filter) ? "block" : "none";
        });
    });
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

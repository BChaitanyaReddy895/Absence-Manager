<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include the database connection file
require 'db.php';

session_start();

// Fetch the submitted form data
$studentName = $_POST['student_name'] ?? '';
$studentSRN = $_POST['student_srn'] ?? '';
$course = $_POST['course'] ?? '';
$section = $_POST['section'] ?? '';
$description = $_POST['description'] ?? '';
$mentorName = $_POST['mentor_name'] ?? '';


// Validation check
if (empty($studentName) || empty($studentSRN)) {
    die("Error: Missing leave form details.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Attendance Approval</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
        }
        textarea, input, button {
            width: 100%;
            margin: 5px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            background-color: #007bff;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Leave Attendance Approval</h1>
        <form action="process_leave1.php" method="POST">
            <input type="hidden" name="student_name" value="<?= htmlspecialchars($studentName) ?>">
            <input type="hidden" name="student_srn" value="<?= htmlspecialchars($studentSRN) ?>">
            <input type="hidden" name="course" value="<?= htmlspecialchars($course) ?>">
            <input type="hidden" name="section" value="<?= htmlspecialchars($section) ?>">
            <input type="hidden" name="description" value="<?= htmlspecialchars($description) ?>">

            <label for="name">Student Name:</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($studentName) ?>" readonly>

            <label for="srn">Student SRN:</label>
            <input type="text" id="srn" name="srn" value="<?= htmlspecialchars($studentSRN) ?>" readonly>

            <label for="course">Course:</label>
            <input type="text" id="course" name="course" value="<?= htmlspecialchars($course) ?>" readonly>

            <label for="section">Section:</label>
            <input type="text" id="section" name="section" value="<?= htmlspecialchars($section) ?>" readonly>

            <label for="description">Description:</label>
            <textarea id="description" name="description" readonly><?= htmlspecialchars($description) ?></textarea>

            <label>Approval Status:</label>
            <input type="radio" id="approve" name="approval" value="approve" required>
            <label for="approve">Approve</label>
            <input type="radio" id="disapprove" name="approval" value="disapprove" required>
            <label for="disapprove">Disapprove</label>

            <label for="disapproval-reason">Reason for Disapproval (if any):</label>
            <textarea id="disapproval-reason" name="disapproval-reason"></textarea>

            <button type="submit">Submit</button>
        </form>
    </div>
</body>
</html>

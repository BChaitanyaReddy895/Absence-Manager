<?php
// Start session
session_start();

// Include database connection
require 'db.php';

// Ensure the event coordinator is logged in
if (!isset($_SESSION['event_coordinator_email'])) {
    die("Error: Event coordinator not logged in.");
}

// Fetch the data from the POST request
if (isset($_POST['student_name']) && isset($_POST['student_srn'])) {
    $studentName = $_POST['student_name'];
    $srn = $_POST['student_srn'];
    
    // Query to get individual event details from individual_event_form
    $sql = "SELECT * FROM `individual_event_form` WHERE `Student_Name` = ? AND `SRN` = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $studentName, $srn);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $student = $result->fetch_assoc();
    } else {
        die("Error: Student event details not found.");
    }
} else {
    die("Error: No student data provided.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Individual Event Coordinator Form</title>
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
        <h1>Individual Event Coordinator Form</h1>
        <form action="process_approval.php" method="POST">
            <input type="hidden" name="student_name" value="<?php echo htmlspecialchars($student['Student_Name']); ?>">
            <input type="hidden" name="student_srn" value="<?php echo htmlspecialchars($student['SRN']); ?>">
            <input type="hidden" name="event_type" value="<?php echo htmlspecialchars($student['Event_Type']); ?>">
            <input type="hidden" name="event_date" value="<?php echo htmlspecialchars($student['Date']); ?>">
            <input type="hidden" name="event_coordinator_name" value="<?php echo htmlspecialchars($student['event_cordinator_name']); ?>">
            <input type="hidden" name="event_coordinator_email" value="<?php echo htmlspecialchars($student['event_cordinator_email']); ?>">

            <label for="student_name">Student Name:</label>
            <input type="text" id="student_name" name="student_name" value="<?php echo htmlspecialchars($student['Student_Name']); ?>" readonly>

            <label for="student_srn">SRN:</label>
            <input type="text" id="student_srn" name="student_srn" value="<?php echo htmlspecialchars($student['SRN']); ?>" readonly>

            <label for="event_type">Event Type:</label>
            <input type="text" id="event_type" name="event_type" value="<?php echo htmlspecialchars($student['Event_Type']); ?>" readonly>

            <label for="event_date">Event Date:</label>
            <input type="text" id="event_date" name="event_date" value="<?php echo htmlspecialchars($student['Date']); ?>" readonly>

            <label for="event_coordinator_name">Event Coordinator Name:</label>
            <input type="text" id="event_coordinator_name" name="event_coordinator_name" value="<?php echo htmlspecialchars($student['event_cordinator_name']); ?>" readonly>

            <label>Approval Status:</label>
            <input type="radio" id="approve" name="approval" value="approve" required>
            <label for="approve">Approve</label>
            <input type="radio" id="disapprove" name="approval" value="disapprove" required>
            <label for="disapprove">Disapprove</label>

            <label for="disapproval_reason">Reason for Disapproval (if any):</label>
            <textarea id="disapproval_reason" name="disapproval_reason"></textarea>

            <button type="submit">Submit</button>
        </form>
    </div>
</body>
</html>

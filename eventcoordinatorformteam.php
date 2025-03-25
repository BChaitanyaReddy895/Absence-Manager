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
if (isset($_POST['team_members']) && isset($_POST['srns'])) {
    $teamMembers = $_POST['team_members'];
    $srns = $_POST['srns'];

    // Query to get team event details from team_event_form
    $sql = "SELECT * FROM `team_event_form` WHERE `Student_Names` = ? AND `SRNs` = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $teamMembers, $srns);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $team = $result->fetch_assoc();
    } else {
        die("Error: Team event details not found.");
    }
} else {
    die("Error: No team data provided.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Event Coordinator Form</title>
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
        <h1>Team Event Coordinator Form</h1>
        <form action="process_approval_team.php" method="POST">
            <input type="hidden" name="team_members" value="<?php echo htmlspecialchars($team['Student_Names']); ?>">
            <input type="hidden" name="srns" value="<?php echo htmlspecialchars($team['SRNs']); ?>">
            <input type="hidden" name="event_type" value="<?php echo htmlspecialchars($team['Event_Type']); ?>">
            <input type="hidden" name="event_date" value="<?php echo htmlspecialchars($team['Date']); ?>">
            <input type="hidden" name="event_coordinator_name" value="<?php echo htmlspecialchars($team['event_cordinator_name']); ?>">
            <input type="hidden" name="event_coordinator_email" value="<?php echo htmlspecialchars($team['event_cordinator_email']); ?>">

            <label for="team_members">Team Members:</label>
            <input type="text" id="team_members" name="team_members" value="<?php echo htmlspecialchars($team['Student_Names']); ?>" readonly>

            <label for="srns">SRNs:</label>
            <input type="text" id="srns" name="srns" value="<?php echo htmlspecialchars($team['SRNs']); ?>" readonly>

            <label for="event_type">Event Type:</label>
            <input type="text" id="event_type" name="event_type" value="<?php echo htmlspecialchars($team['Event_Type']); ?>" readonly>

            <label for="event_date">Event Date:</label>
            <input type="text" id="event_date" name="event_date" value="<?php echo htmlspecialchars($team['Date']); ?>" readonly>

            <label for="event_coordinator_name">Event Coordinator Name:</label>
            <input type="text" id="event_coordinator_name" name="event_coordinator_name" value="<?php echo htmlspecialchars($team['event_cordinator_name']); ?>" readonly>

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

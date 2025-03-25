<?php
// Include database connection
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and fetch form data
    $names = implode(", ", [
        $_POST['Name1'], $_POST['Name2'], $_POST['Name3'], $_POST['Name4'], $_POST['Name5']
    ]);
    $srns = implode(", ", [
        $_POST['Srn1'], $_POST['Srn2'], $_POST['Srn3'], $_POST['Srn4'], $_POST['Srn5']
    ]);
    $date = $conn->real_escape_string(trim($_POST['date']));
    $event = $conn->real_escape_string(trim($_POST['event']));
    $studentEmail = $conn->real_escape_string(trim($_POST['student_email']));
    $event_coordinator_name = $conn->real_escape_string(trim($_POST['event_cordinator_name']));
    $event_coordinator_email = $conn->real_escape_string(trim($_POST['event_cordinator_email']));

    // SQL Insert Query for 'team_event_form' table
    $sql = "INSERT INTO team_event_form (Student_Names, SRNs, Date, Event_Type,student_email, event_cordinator_name, event_cordinator_email) 
            VALUES (?, ?, ?, ?,?, ?, ?)";

    // Prepare and bind parameters
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $names, $srns, $date, $event,$studentEmail, $event_coordinator_name, $event_coordinator_email);

    // Execute the query and check if it was successful
    if ($stmt->execute()) {
        echo "<h3 style='color: green; text-align: center;'>Team details submitted successfully!</h3>";
    } else {
        echo "<h3 style='color: red; text-align: center;'>Error: " . $stmt->error . "</h3>";
    }

    // Close the prepared statement and database connection
    $stmt->close();
    $conn->close();
} else {
    echo "<h3 style='color: red; text-align: center;'>Invalid request method.</h3>";
}
?>

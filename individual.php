<?php
// Include database connection
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and fetch form data
    $name = $conn->real_escape_string(trim($_POST['name']));
    $srn = $conn->real_escape_string(trim($_POST['srn']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $phone = $conn->real_escape_string(trim($_POST['phone']));
    $date = $conn->real_escape_string(trim($_POST['date']));
    $event = $conn->real_escape_string(trim($_POST['event']));
    $event_coordinator_name = $conn->real_escape_string(trim($_POST['event_cordinator_name']));
    $event_coordinator_email = $conn->real_escape_string(trim($_POST['event_cordinator_email']));

    // SQL Insert Query for 'individual_event_form' table
    $sql = "INSERT INTO individual_event_form (Student_Name, SRN, Student_Email, Student_phone, Date, Event_Type, event_cordinator_name, event_cordinator_email) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    // Prepare and bind parameters
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssss", $name, $srn, $email, $phone, $date, $event, $event_coordinator_name, $event_coordinator_email);

    // Execute the query and check if it was successful
    if ($stmt->execute()) {
        echo "<h3 style='color: green; text-align: center;'>Event details submitted successfully!</h3>";
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

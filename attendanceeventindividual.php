<?php
// Include the database connection file
include 'db.php';
 // Include PHPMailer
 require 'PHPMailer-master/src/PHPMailer.php';
 require 'PHPMailer-master/src/SMTP.php';
 require 'PHPMailer-master/src/Exception.php';
 use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();
// Fetch coordinator details from the attendance_coordinator_registration table
$coordinatorRollNumber = $_SESSION['coordinator_roll_number'] ?? ''; // Assuming session holds the roll number
$coordinatorName = '';
$coordinatorEmail = '';

// Fetch coordinator details from the database
$sqlCoordinator = "SELECT Name, `REVA Email ID` FROM attendance_coordinator_registration WHERE `Roll Number` = ?";
$stmtCoordinator = $conn->prepare($sqlCoordinator);
$stmtCoordinator->bind_param("s", $coordinatorRollNumber);
$stmtCoordinator->execute();
$stmtCoordinator->store_result();
$stmtCoordinator->bind_result($coordinatorName, $coordinatorEmail);
$stmtCoordinator->fetch();

// Check if the coordinator's email is valid
if (empty($coordinatorEmail) || !filter_var($coordinatorEmail, FILTER_VALIDATE_EMAIL)) {
    $coordinatorEmail = 'anandshankar@reva.edu.in'; // Fallback email
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentName = $_POST['student_name'] ?? '';
    $studentSRN = $_POST['student_srn'] ?? '';
    $eventType = $_POST['event_type'] ?? '';
    $eventDate = $_POST['event_date'] ?? '';

    try {
        // Fetch event details from the database
        $sql = "SELECT * FROM individual_event_form WHERE Student_Name = ? AND SRN = ? AND Event_Type = ? AND Date = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $studentName, $studentSRN, $eventType, $eventDate);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $eventDetails = $result->fetch_assoc();

            // Display event details
            echo "<h1>Event Details</h1>";
            echo "<p>Student Name: {$eventDetails['Student_Name']}</p>";
            echo "<p>SRN: {$eventDetails['SRN']}</p>";
            echo "<p>Email: {$eventDetails['Student_Email']}</p>";
            echo "<p>Phone: {$eventDetails['Student_phone']}</p>";
            echo "<p>Event Type: {$eventDetails['Event_Type']}</p>";
            echo "<p>Date: {$eventDetails['Date']}</p>";
            echo "<p>Event Coordinator: {$eventDetails['event_cordinator_name']}</p>";

            // Form to approve/disapprove event
            echo "
                <form method='post'>
                    <input type='hidden' name='student_name' value='{$eventDetails['Student_Name']}'>
                    <input type='hidden' name='student_email' value='{$eventDetails['Student_Email']}'>
                    <input type='hidden' name='event_type' value='{$eventDetails['Event_Type']}'>
                    <input type='hidden' name='event_date' value='{$eventDetails['Date']}'>
                    <button type='submit' name='action' value='approve'>Approve</button>
                    <button type='submit' name='action' value='disapprove'>Disapprove</button>
                    <textarea name='disapproval_reason' placeholder='Enter reason for disapproval'></textarea>
                </form>
            ";
        } else {
            echo "<p>No event details found.</p>";
        }

        $stmt->close();
    } catch (Exception $e) {
        echo "<p>Error: " . $e->getMessage() . "</p>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $studentName = $_POST['student_name'];
    $studentEmail = $_POST['student_email'];
    $eventType = $_POST['event_type'];
    $eventDate = $_POST['event_date'] ?? '';
    $disapprovalReason = $_POST['disapproval_reason'] ?? 'No reason provided';

    if ($action === 'approve') {
        // Handle approval
        $approvalDate = date('Y-m-d H:i:s');
        $sqlUpdate = "UPDATE individual_event_form SET Status = 'Approved by attendance coordinator', Approval_Date = ? WHERE Student_Name = ? AND Event_Type = ? AND Date = ?";
        $stmt = $conn->prepare($sqlUpdate);
        $stmt->bind_param("ssss", $approvalDate, $studentName, $eventType, $eventDate);
        $stmt->execute();

        // Send approval email
        $subject = "Event Approved";
        $body = "Your event ({$eventType}) on {$eventDate} has been approved by attendance coordinator!. Best Regards.";
        sendEmail($studentEmail, $coordinatorEmail, $subject, $body);

        echo "<p>Event approved and email sent to the student.</p>";
    } elseif ($action === 'disapprove') {
        // Handle disapproval
        $sqlUpdate = "UPDATE individual_event_form SET Status = 'Disapproved by attendance coordinator', Disapproval_Reason = ? WHERE Student_Name = ? AND Event_Type = ? AND Date = ?";
        $stmt = $conn->prepare($sqlUpdate);
        $stmt->bind_param("ssss", $disapprovalReason, $studentName, $eventType, $eventDate);
        $stmt->execute();

        // Send disapproval email
        $subject = "Event Disapproved";
        $body = "Your event ({$eventType}) on {$eventDate} has been disapproved. Reason: {$disapprovalReason}. Please contact the attendance coordinator for further clarifications.Best Regards.";
        sendEmail($studentEmail, $coordinatorEmail, $subject, $body);

        echo "<p>Event disapproved and email sent to the student.</p>";
    }

    $stmt->close();
    $conn->close();
}

// Function to send emails
function sendEmail($to, $from, $subject, $body)
{
   


    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'chaituchaithanyareddy895@gmail.com'; // Your email
        $mail->Password = 'dsxn pdcu qauz pjiw'; // Your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom($from, 'Attendance Coordinator');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
    } catch (Exception $e) {
        echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>

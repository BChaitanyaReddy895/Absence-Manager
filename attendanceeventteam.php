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
    $coordinatorEmail = 'anandshankar@reva.edu.in'; // Use a fallback email if the coordinator's email is invalid
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $srns = $_POST['srns'] ?? '';

    try {
        // Fetch team event details from the database
        $sql = "SELECT * FROM team_event_form WHERE SRNs = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $srns);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $eventDetails = $result->fetch_assoc();

            // Display event details
            echo "<h1>Team Event Details</h1>";
            echo "<p>Team Members: {$eventDetails['Student_Names']}</p>";
            echo "<p>SRNs: {$eventDetails['SRNs']}</p>";
            echo "<p>Event Type: {$eventDetails['Event_Type']}</p>";
            echo "<p>Date: {$eventDetails['Date']}</p>";
            echo "<p>Event Coordinator: {$eventDetails['event_cordinator_name']}</p>";

            // Form to approve/disapprove event
            echo "
                <form method='post'>
                    <input type='hidden' name='srns' value='{$eventDetails['SRNs']}'>
                    <button type='submit' name='action' value='approve'>Approve</button>
                    <button type='submit' name='action' value='disapprove'>Disapprove</button>
                </form>
            ";
        } else {
            echo "<p>No team event details found.</p>";
        }

        $stmt->close();
    } catch (Exception $e) {
        echo "<p>Error: " . $e->getMessage() . "</p>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $srns = $_POST['srns'] ?? '';

    try {
        if ($action === 'approve') {
            // Handle approval
            $approvalDate = date('Y-m-d H:i:s');
            $sqlUpdate = "UPDATE team_event_form SET Status = 'Approved by attendance coordinator', Approval_Date = ? WHERE SRNs = ?";
            $stmt = $conn->prepare($sqlUpdate);
            $stmt->bind_param("ss", $approvalDate, $srns);
            $stmt->execute();

            // Send approval email to team
            $sqlTeamEmails = "SELECT `Student_Email` FROM team_event_form WHERE SRNs = ?";
            $stmtEmails = $conn->prepare($sqlTeamEmails);
            $stmtEmails->bind_param("s", $srns);
            $stmtEmails->execute();
            $stmtEmails->store_result();
            $stmtEmails->bind_result($studentEmails);
            $stmtEmails->fetch();

            $teamEmails = explode(',', $studentEmails);
            foreach ($teamEmails as $email) {
                $subject = "Event Approved";
                $body = "Your team event ({$eventDetails['Event_Type']}) on {$eventDetails['Date']} has been approved.";
                sendEmail(trim($email), 'attendance_coordinator@example.com', $subject, $body);
            }

            echo "<p>Event approved and email sent to the team.</p>";
        } elseif ($action === 'disapprove') {
            // Handle disapproval
            $disapprovalReason = $_POST['disapproval_reason'] ?? 'No reason provided';
            $sqlUpdate = "UPDATE team_event_form SET Status = 'Disapproved by attendance coordinator', Disapproval_Reason = ? WHERE SRNs = ?";
            $stmt = $conn->prepare($sqlUpdate);
            $stmt->bind_param("ss", $disapprovalReason, $srns);
            $stmt->execute();

            // Send disapproval email to team
            $sqlTeamEmails = "SELECT `Student_Email` FROM team_event_form WHERE SRNs = ?";
            $stmtEmails = $conn->prepare($sqlTeamEmails);
            $stmtEmails->bind_param("s", $srns);
            $stmtEmails->execute();
            $stmtEmails->store_result();
            $stmtEmails->bind_result($studentEmails);
            $stmtEmails->fetch();

            $teamEmails = explode(',', $studentEmails);
            foreach ($teamEmails as $email) {
                $subject = "Event Disapproved";
                $body = "Your team event ({$eventDetails['Event_Type']}) on {$eventDetails['Date']} has been disapproved. Reason: {$disapprovalReason}";
                sendEmail(trim($email), 'attendance_coordinator@example.com', $subject, $body);
            }

            echo "<p>Event disapproved and email sent to the team.</p>";
        }

        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        echo "<p>Error: " . $e->getMessage() . "</p>";
    }
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
        $mail->Password = 'dsxn pdcu qauz pjiw'; // Your email password or app password
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

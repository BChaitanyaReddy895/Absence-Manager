<?php
require 'db.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Retrieve submitted data
$studentSRN = $_POST['student_srn'] ?? '';
$studentName = $_POST['student_name'] ?? '';
$studentEmail = $_POST['student_email'] ?? '';
$eventType = $_POST['event_type'] ?? '';
$eventDate = $_POST['event_date'] ?? '';
$approvalStatus = $_POST['approval'] ?? '';
$disapprovalReason = $_POST['disapproval_reason'] ?? '';
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
 
// Validate inputs
if (empty($studentSRN) || empty($approvalStatus)) {
    die("Error: Missing required data.");
}

try {
    if ($approvalStatus === 'approve') {
        // Approve the event
        $sql = "UPDATE individual_event_form SET Status = 'Approved', Approval_Date = NOW() WHERE SRN = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $studentSRN);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo "Approval successfully updated.";
            header("Location: attendance_cordinator_dashboard.php?message=Approved successfully");
        } else {
            throw new Exception("Approval update failed.");
        }
    } elseif ($approvalStatus === 'disapprove') {
        // Disapprove the event
        $sql = "UPDATE individual_event_form SET Status = 'Disapproved', Approval_Date = NOW(), Disapproval_Reason = ? WHERE SRN = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $disapprovalReason, $studentSRN);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            sendDisapprovalEmail($studentEmail, $studentName, $eventType, $eventDate, $disapprovalReason);
            header("Location: attendance_cordinator_dashboard.php?message=Disapproved successfully");
        } else {
            throw new Exception("Disapproval update failed.");
        }
    } else {
        throw new Exception("Invalid approval status.");
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Function to send disapproval email
function sendDisapprovalEmail($toEmail, $studentName, $eventType, $eventDate, $disapprovalReason) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'chaituchaithanyareddy895@gmail.com'; // Replace with your email
        $mail->Password = 'dsxn pdcu qauz pjiw';    // Replace with your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('your_email@gmail.com', 'Attendance Coordinator'); // Replace with your email
        $mail->addAddress($toEmail);

        $mail->Subject = 'Event Disapproval Notification';
        $mail->Body = "Dear $studentName,\n\nYour event '$eventType' scheduled for $eventDate has been disapproved.\n\nReason: $disapprovalReason\n\nBest regards,\nAttendance Coordinator";

        $mail->send();
    } catch (Exception $e) {
        echo "Mailer Error: {$mail->ErrorInfo}";
    }
}
?>

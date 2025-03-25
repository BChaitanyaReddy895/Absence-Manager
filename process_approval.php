<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include necessary files
require 'db.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Fetch submitted data
$studentName = $_POST['student_name'] ?? '';
$srn = $_POST['student_srn'] ?? '';
$eventType = $_POST['event_type'] ?? '';
$eventDate = $_POST['event_date'] ?? '';
$approvalStatus = $_POST['approval'] ?? '';
$disapprovalReason = $_POST['disapproval_reason'] ?? '';

if (empty($studentName) || empty($srn) || empty($approvalStatus)) {
    die("Error: Missing required form data.");
}

// Fetch the student's email and event coordinator's email from the database
$sql = "SELECT Student_Email, event_cordinator_email FROM individual_event_form WHERE SRN = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $srn);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    die("Error: No matching student found in the database.");
}

$studentEmail = $data['Student_Email'];
$eventCoordinatorEmail = $data['event_cordinator_email'];

try {
    if ($approvalStatus === 'approve') {
        // Approve the event and update the database
        $sql = "UPDATE individual_event_form SET Status = 'Approved', Approval_Date = NOW() WHERE SRN = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $srn);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            sendApprovalEmail($eventCoordinatorEmail, $studentEmail, $studentName, $eventType, $eventDate);
            header("Location: event_cordinator_dashboard.php?message=Approved successfully");
        } else {
            throw new Exception("Approval failed. Please try again later.");
        }
    } elseif ($approvalStatus === 'disapprove') {
        // Disapprove the event and update the database
        $sql = "UPDATE individual_event_form SET Status = 'Disapproved', Approval_Date = NOW(), Disapproval_Reason = ? WHERE SRN = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $disapprovalReason, $srn);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            sendDisapprovalEmail($eventCoordinatorEmail, $studentEmail, $studentName, $eventType, $eventDate, $disapprovalReason);
            header("Location: event_cordinator_dashboard.php?message=Disapproved successfully");
        } else {
            throw new Exception("Disapproval failed. Please try again later.");
        }
    } else {
        throw new Exception("Invalid approval status.");
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Function to send approval email
function sendApprovalEmail($fromEmail, $toEmail, $studentName, $eventType, $eventDate) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'chaituchaithanyareddy895@gmail.com'; // Your Gmail address
        $mail->Password = 'dsxn pdcu qauz pjiw';       // Your Gmail password or App password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($fromEmail, 'Event Coordinator');
        $mail->addAddress($toEmail);
        $mail->addReplyTo($fromEmail);

        $mail->Subject = 'Individual Event Approval Notification';
        $mail->Body = "Dear $studentName,\n\nYour individual event with type '$eventType' scheduled for $eventDate has been approved.\n\nRegards,\nEvent Coordinator";

        $mail->send();
    } catch (Exception $e) {
        echo "Mailer Error: {$mail->ErrorInfo}";
    }
}

// Function to send disapproval email
function sendDisapprovalEmail($fromEmail, $toEmail, $studentName, $eventType, $eventDate, $disapprovalReason) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'chaituchaithanyareddy895@gmail.com'; // Your Gmail address
        $mail->Password = 'dsxn pdcu qauz pjiw';       // Your Gmail password or App password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($fromEmail, 'Event Coordinator');
        $mail->addAddress($toEmail);
        $mail->addReplyTo($fromEmail);

        $mail->Subject = 'Individual Event Disapproval Notification';
        $mail->Body = "Dear $studentName,\n\nYour individual event with type '$eventType' scheduled for $eventDate has been disapproved.\n\nReason: $disapprovalReason\n\nRegards,\nEvent Coordinator";

        $mail->send();
    } catch (Exception $e) {
        echo "Mailer Error: {$mail->ErrorInfo}";
    }
}
?>

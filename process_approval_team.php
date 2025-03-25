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
$teamMembers = $_POST['team_members'] ?? '';
$srns = $_POST['srns'] ?? '';
$eventType = $_POST['event_type'] ?? '';
$eventDate = $_POST['event_date'] ?? '';
$approvalStatus = $_POST['approval'] ?? '';
$disapprovalReason = $_POST['disapproval_reason'] ?? '';

if (empty($teamMembers) || empty($srns) || empty($approvalStatus)) {
    die("Error: Missing required form data.");
}

// Fetch emails from the database
$sql = "SELECT Student_Email, event_cordinator_email FROM team_event_form WHERE SRNs = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $srns);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    die("Error: No matching team found in the database.");
}

$teamEmail = $data['Student_Email'];
$eventCoordinatorEmail = $data['event_cordinator_email'];

try {
    if ($approvalStatus === 'approve') {
        $sql = "UPDATE team_event_form SET Status = 'Approved', Approval_Date = NOW() WHERE SRNs = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $srns);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            sendApprovalEmail($eventCoordinatorEmail, $teamEmail, $teamMembers, $eventType, $eventDate,$disapprovalReason);
            header("Location: event_cordinator_dashboard.php?message=Approved successfully");
        } else {
            throw new Exception("Approval failed. Please try again later.");
        }
    } elseif ($approvalStatus === 'disapprove') {
        $sql = "UPDATE team_event_form SET Status = 'Disapproved', Approval_Date = NOW(), Disapproval_Reason = ? WHERE SRNs = ?";
        $stmt->prepare($sql);
        $stmt->bind_param("ss", $disapprovalReason, $srns);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            sendDisapprovalEmail($eventCoordinatorEmail, $teamEmail, $teamMembers, $eventType, $eventDate,$disapprovalReason);
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

// Function to send emails is similar to the individual approval case

// sendApprovalEmail() and sendDisapprovalEmail() as in individual events
function sendDisapprovalEmail($eventCoordinatorEmail, $teamEmail, $studentName, $eventType, $eventDate, $disapprovalReason) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'chaituchaithanyareddy895@gmail.com'; // Your Gmail address
        $mail->Password = 'dsxn pdcu qauz pjiw';       // Your Gmail password or App password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($eventCoordinatorEmail, 'Event Coordinator');
        $mail->addAddress($teamEmail);
        $mail->addReplyTo($eventCoordinatorEmail);

        $mail->Subject = 'Team Event Disapproval Notification';
        $mail->Body = "Dear $teamMembers,\n\nYour team event with type '$eventType' scheduled for $eventDate has been disapproved.\n\nReason: $disapprovalReason\n\nRegards,\nEvent Coordinator";

        $mail->send();
    }
    catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
function sendApprovalEmail($eventCoordinatorEmail, $teamEmail, $studentName, $eventType, $eventDate,$disapprovalReason) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'chaituchaithanyareddy895@gmail.com'; // Your Gmail address
        $mail->Password = 'dsxn pdcu qauz pjiw';       // Your Gmail password or App password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($eventCoordinatorEmail, 'Event Coordinator');
        $mail->addAddress($teamEmail);
        $mail->addReplyTo($eventCoordinatorEmail);

        $mail->Subject = 'Team Event Approval Notification';
        $mail->Body = "Dear $teamMembers,\n\nYour team event with type '$eventType' scheduled for $eventDate has been approved.\n\nReason: $disapprovalReason\n\nRegards,\nEvent Coordinator";

        $mail->send();
    }
    catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include the database connection file
require 'db.php';

// Include PHPMailer files
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Fetch the form data
$studentName = $_POST['student_name'] ?? '';
$studentSRN = $_POST['student_srn'] ?? '';
$approvalStatus = $_POST['approval'] ?? '';
$disapprovalReason = $_POST['disapproval-reason'] ?? '';

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

// Fetch student details (email) from the leave_form table
$sqlStudent = "SELECT `Reva_Email` FROM leave_form WHERE `SRN` = ?";
$stmtStudent = $conn->prepare($sqlStudent);
$stmtStudent->bind_param("s", $studentSRN);
$stmtStudent->execute();
$stmtStudent->store_result();
$stmtStudent->bind_result($studentEmail);
$stmtStudent->fetch();

// Check if the student's email is valid
if (empty($studentEmail) || !filter_var($studentEmail, FILTER_VALIDATE_EMAIL)) {
    die("Error: Invalid student email.");
}


try {
    if ($approvalStatus === 'approve') {
        // Update the leave form status to approved
        $sqlApprove = "UPDATE leave_form SET Status = 'Approved by attendance coordinator', Approval_Date = NOW() WHERE Name = ? AND SRN = ?";
        $stmtApprove = $conn->prepare($sqlApprove);
        $stmtApprove->bind_param("ss", $studentName, $studentSRN);
        $stmtApprove->execute();
        echo "Student approved successfully!";

        if ($stmtApprove->affected_rows > 0) {
            // Redirect to dashboard or success page
            header("Location:attendance_coordinator_dashboard.php?message=Leave approved successfully");
            exit;
        } else {
            throw new Exception("Unable to approve leave. Please try again.");
        }
    }else if ($approvalStatus === 'disapprove') {
    
    $mail = new PHPMailer(true);
    try{
    // Set SMTP configuration
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';  // Gmail SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = 'chaituchaithanyareddy895@gmail.com';  // Your Gmail email address
    $mail->Password = 'dsxn pdcu qauz pjiw';  // Your generated app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    // Set sender's email address
    $mail->setFrom($coordinatorEmail, $coordinatorName);

    // Add recipient's email address
    $mail->addAddress($studentEmail);

    // Email content
    $mail->isHTML(true);
    $mail->Subject = 'Leave Request Disapproval';
    $mail->Body = 'Dear ' . htmlspecialchars($studentName) . ',<br>Your leave request has been disapproved by the attendance Coordinator!. For further clarifications contact the attendance coordinator.<br>Reason: ' . htmlspecialchars($disapprovalReason) . '<br>Regards,<br>' . htmlspecialchars($coordinatorName);

    // Send the email
    $mail->send();

    // Email sent successfully message
    $emailStatusMessage = "Disapproval email sent to the student successfully.";
    }
    catch (Exception $e) {
        echo "Error: {$mail->ErrorInfo}";
    }
    // Update the leave form status to disapproved in the database
    $sqlDisapprove = "UPDATE leave_form SET Status = 'Disapproved by attendance coordinator', Disapproval_Reason = ?, Approval_Date = NOW() WHERE SRN = ?";
    $stmtDisapprove = $conn->prepare($sqlDisapprove);
    $stmtDisapprove->bind_param("ss", $disapprovalReason, $studentSRN);
    $stmtDisapprove->execute();

    if ($stmtDisapprove->affected_rows > 0) {
        header("Location: attendance_coordinator_dashboard.php?message=Leave disapproved successfully.");
    } else {
        throw new Exception("Unable to update leave status. Please try again.");
    }

} }catch (Exception $e) {
    // Email sending failed message
    $emailStatusMessage = "Error: Unable to send email. {$mail->ErrorInfo}";
    header("Location: attendance_coordinator_dashboard.php?message=Leave disapproved successfully. Error: $emailStatusMessage");
    exit;
}
?>

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
// Start session to fetch mentor details if needed
session_start();

// Fetch form data
$studentName = $_POST['student_name'] ?? '';
$studentSRN = $_POST['student_srn'] ?? '';
$course = $_POST['course'] ?? '';
$section = $_POST['section'] ?? '';
$description = $_POST['description'] ?? '';
$approvalStatus = $_POST['approval'] ?? '';
$disapprovalReason = $_POST['disapproval-reason'] ?? '';
$mentorName = $_SESSION['mentor_name'] ?? '';
$mentorEmail = $_SESSION['mentor_email'] ?? '';

// Validation check
if (empty($studentName) || empty($studentSRN) || empty($approvalStatus)) {
    die("Invalid form submission. Please ensure all fields are filled.");
}

try {
    if ($approvalStatus === 'approve') {
        // Update the leave form status to approved
        $sqlApprove = "UPDATE leave_form SET Status = 'Approved', Approval_Date = NOW() WHERE Name = ? AND SRN = ?";
        $stmtApprove = $conn->prepare($sqlApprove);
        $stmtApprove->bind_param("ss", $studentName, $studentSRN);
        $stmtApprove->execute();
        echo "Student approved successfully!";

        if ($stmtApprove->affected_rows > 0) {
            // Redirect to dashboard or success page
            header("Location: mentor-dashboard.php?message=Leave approved successfully");
            exit;
        } else {
            throw new Exception("Unable to approve leave. Please try again.");
        }
    } elseif ($approvalStatus === 'disapprove') {
        // Fetch mentor's details (email and name) from the leave_form table
        $sqlMentorDetails = "SELECT Mentor_Name, Mentor_Email, Reva_Email FROM leave_form WHERE Name = ? AND SRN = ?";
        $stmtMentorDetails = $conn->prepare($sqlMentorDetails);
        $stmtMentorDetails->bind_param("ss", $studentName, $studentSRN);
        $stmtMentorDetails->execute();
        $result = $stmtMentorDetails->get_result();
        $row = $result->fetch_assoc();

        $mentorName = $row['Mentor_Name'] ?? ''; // Fetch mentor's name
        $mentorEmail = $row['Mentor_Email'] ?? ''; // Fetch mentor's email
        $studentEmail = $row['Reva_Email'] ?? ''; // Fetch student's email

        if (empty($studentEmail)) {
            throw new Exception("Student email not found. Unable to send disapproval notification.");
        }

        // Send disapproval email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Set SMTP configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';  // Gmail SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'chaituchaithanyareddy895@gmail.com';  // Your Gmail email address
            $mail->Password = 'dsxn pdcu qauz pjiw';  // Your generated app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Set sender's email address to mentor's email
            $mail->setFrom($mentorEmail, $mentorName);
            
            // Add recipient's email address (student's email)
            $mail->addAddress($studentEmail);

            // Email content
            $mail->isHTML(true);
            $mail->Subject = 'Leave Request Disapproval';
            $mail->Body    = 'Dear ' . htmlspecialchars($studentName) . ',<br>Your leave request has been disapproved.<br>Reason: ' . htmlspecialchars($disapprovalReason) . '<br>Regards,<br>' . htmlspecialchars($mentorName);

            // Send the email
            $mail->send();
            echo 'Disapproval email has been sent successfully.';
        } catch (Exception $e) {
            echo "Error: {$mail->ErrorInfo}";
        }

        // Update the leave form status to disapproved
        $sqlDisapprove = "UPDATE leave_form SET Status = 'Disapproved', Disapproval_Reason = ?, Approval_Date = NOW() WHERE Name = ? AND SRN = ?";
        $stmtDisapprove = $conn->prepare($sqlDisapprove);
        $stmtDisapprove->bind_param("sss", $disapprovalReason, $studentName, $studentSRN);
        $stmtDisapprove->execute();

        if ($stmtDisapprove->affected_rows > 0) {
            header("Location: mentor-dashboard.php?message=Leave disapproved successfully");
            exit;
        } else {
            throw new Exception("Unable to disapprove leave. Please try again.");
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>



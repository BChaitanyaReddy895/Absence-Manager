<?php
// Include the PHPMailer files from the downloaded PHPMailer-master folder
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendDisapprovalEmail($studentEmail, $studentName) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                         // Set the SMTP server to Gmail
        $mail->SMTPAuth   = true;                                     // Enable SMTP authentication
        $mail->Username   = 'your_email@gmail.com';                   // SMTP username (your Gmail address)
        $mail->Password   = 'your_email_password';                    // SMTP password (your Gmail password or App Password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;           // Enable TLS encryption
        $mail->Port       = 587;                                      // TCP port to connect to

        // Recipients
        $mail->setFrom('your_email@gmail.com', 'Event Coordinator');  // From address (use your Gmail)
        $mail->addAddress($studentEmail, $studentName);               // Add the student's email address

        // Content
        $mail->isHTML(true);                                          // Set email format to HTML
        $mail->Subject = 'Your Event Form Disapproved';
        $mail->Body    = "Dear $studentName,<br><br>Your event submission has been disapproved by the Event Coordinator. Please contact the coordinator for more details.<br><br>Best regards,<br>Event Coordination Team";

        // Send email
        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>

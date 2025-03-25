<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer classes
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'db.php';
    $email = trim($_POST['email']);

    // Check if the email exists
    $stmt = $conn->prepare("SELECT * FROM student_signup WHERE REVA_Email_id = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);

        // Save the token in the database
        $stmt = $conn->prepare("UPDATE student_signup SET reset_token = ?, reset_token_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE REVA_Email_id = ?");
        $stmt->bind_param("ss", $hashedToken, $email);
        $stmt->execute();

        // Prepare the reset link
        $resetLink = "http://localhost/safersideproject123/project123/reset-password.php?email=" . urlencode($email) . "&token=" . $token;

        // Send the email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'chaituchaithanyareddy895@gmail.com'; // Your Gmail address
            $mail->Password = 'dsxn pdcu qauz pjiw'; // Google App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Email settings
            $mail->setFrom('no-reply@reva.edu.in', 'Reva University');
            $mail->addAddress($email);
            $mail->Subject = 'Password Reset Request';
            $mail->Body = "Click the link to reset your password: $resetLink";

            $mail->send();
            echo "Reset email sent successfully.";
        } catch (Exception $e) {
            echo "Failed to send reset email: {$mail->ErrorInfo}";
        }
    } else {
        echo "Email not found.";
    }
}
?>

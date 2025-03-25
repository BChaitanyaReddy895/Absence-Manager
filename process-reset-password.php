<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if email and token are passed in POST request
    if (!isset($_POST['email'], $_POST['token'])) {
        echo "Invalid request: Missing email or token.";
        exit;
    }

    // Get email, token, and new password from the form
    $email = $_POST['email'];
    $token = $_POST['token'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if the new password and confirm password match
    if ($new_password !== $confirm_password) {
        echo "Passwords do not match.";
        exit;
    }

    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

    // Verify the token again
    $stmt = $conn->prepare("SELECT * FROM student_signup WHERE REVA_Email_id = ? AND reset_token = ? AND reset_token_expires > NOW()");
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update the password in the database
        $stmt = $conn->prepare("UPDATE student_signup SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE REVA_Email_id = ?");
        $stmt->bind_param("ss", $hashed_password, $email);
        $stmt->execute();

        echo "Password reset successfully. You can now log in.";
    } else {
        echo "Invalid or expired token.";
    }
}
?>

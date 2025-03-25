<?php
session_start();
require 'db.php';  // Include the database connection

// Check if the reset token and email are passed via GET (should be through POST when navigating)
if (isset($_GET['email'], $_GET['token'])) {
    $email = $_GET['email'];
    $token = $_GET['token'];

    // Fetch the reset token and expiry time from the database for the provided email
    $sql = "SELECT reset_token, reset_token_expires FROM student_signup WHERE REVA_Email_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($db_token, $db_token_expires);
    $stmt->fetch();

    // Check if the token exists and hasn't expired
    if ($db_token === $token && new DateTime() < new DateTime($db_token_expires)) {
        // Token is valid and not expired, show the password reset form
    } else {
        echo "Invalid or expired token.";
        exit;
    }
} else {
    echo "Invalid request.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>
<body>
    <h1>Reset Your Password</h1>
    <form method="POST" action="process-reset-password.php">
        <!-- Hidden fields to pass the email and token to process-reset-password.php -->
        <input type="hidden" name="email" value="<?php echo $email; ?>">
        <input type="hidden" name="token" value="<?php echo $token; ?>">

        <label for="new_password">New Password:</label>
        <input type="password" name="new_password" id="new_password" required><br>

        <label for="confirm_password">Confirm Password:</label>
        <input type="password" name="confirm_password" id="confirm_password" required><br>

        <button type="submit">Reset Password</button>
    </form>
</body>
</html>

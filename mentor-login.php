<?php
// Enable error reporting for debugging during development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start session to store user information after login
session_start();
include('db.php');  // Include database connection file

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize the form input to prevent SQL injection
    $email = mysqli_real_escape_string($conn, $_POST['REVA_Email_ID']);
    $password = $_POST['password']; // Plain text password input

    // Query to check if a mentor exists with the given email
    $query = "SELECT * FROM `mentor registration` WHERE `REVA Email ID` = '$email'";
    $result = mysqli_query($conn, $query);

    // Check if the query ran successfully
    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }

    // Check if the email exists in the database
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        // Verify if the entered password matches the hashed password
        if (password_verify($password, $row['Password'])) {
            // Correct password, start session and redirect to the mentor dashboard
            $_SESSION['mentor_name'] = $row['Name'];
            $_SESSION['mentor_email'] = $row['REVA Email ID'];
            header('Location: mentor-dashboard.php');  // Redirect to mentor dashboard
            exit();
        } else {
            // Incorrect password
            echo "<script>alert('Incorrect password. Please try again.'); window.location.href='mentor-login.html';</script>";
        }
    } else {
        // No user found with that email
        echo "<script>alert('No account found with this email. Please sign up.'); window.location.href='mentor_registration.html';</script>";
    }
}

// Display error message if set
if (isset($error_message)) {
    echo "<p class='error-message'>$error_message</p>";
}
?>

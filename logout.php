<?php
// Start the session
session_start();

// Destroy all session variables
session_unset();

// Destroy the session itself
session_destroy();

// Redirect the user to the login page after logout
header("Location: student-login.html");
exit();
?>

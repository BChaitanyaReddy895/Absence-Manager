<?php
// Start output buffering and session
ob_start();
session_start();

// Include the database connection file
include 'db.php';

// Validate that the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize user input
    $full_name = trim($_POST['full_name'] ?? '');
    $SRN = trim($_POST['SRN'] ?? '');
    $REVA_Email_id = trim($_POST['REVA_Email_id'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');

    // Validate required fields
    if (empty($full_name) || empty($SRN) || empty($REVA_Email_id) || empty($password) || empty($phone_number)) {
        echo "All fields are required. Please fill out the form completely.";
        exit;
    }

    // Validate email format
    if (!filter_var($REVA_Email_id, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format.";
        exit;
    }

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if the email already exists
    $checkQuery = $conn->prepare("SELECT * FROM student_signup WHERE REVA_Email_id = ?");
    $checkQuery->bind_param("s", $REVA_Email_id);
    $checkQuery->execute();
    $result = $checkQuery->get_result();

    if ($result->num_rows > 0) {
        echo "Email is already registered. Please try a different one.";
    } else {
        // Insert the new user into the database
        $stmt = $conn->prepare("INSERT INTO student_signup (full_name, SRN, REVA_Email_id, password, phone_number) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $full_name, $SRN, $REVA_Email_id, $hashed_password, $phone_number);

        if ($stmt->execute()) {
            echo "Signup successful! You can now <a href='student-login.html'>login</a>.";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }

    // Close database connections
    $checkQuery->close();
    $conn->close();
} else {
    // Display a message if the request method is not POST
    echo "Invalid request method. Please submit the form.";
    exit;
}

<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['REVA_Email_id'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Prepare SQL query
    $sql = "SELECT full_name, SRN, password FROM student_signup WHERE REVA_Email_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($full_name, $srn, $dbPassword);
            $stmt->fetch();

            // Verify password
            if (password_verify($password, $dbPassword)) {
                // Store user data in session
                $_SESSION['user_name'] = $full_name;
                $_SESSION['user_srn'] = $srn;
                $_SESSION['user_email'] = $email;

                // Return success response
                $response = [
                    'type' => 'login_response',
                    'success' => true,
                    'message' => 'Login successful! Welcome ' . $full_name . ' SRN: ' . $srn,
                    'redirect' => 'option-page.html' // Redirect URL
                ];
            } else {
                // Return error response for incorrect password
                $response = [
                    'type' => 'login_response',
                    'success' => false,
                    'message' => 'Incorrect password. Please try again.'
                ];
            }
        } else {
            // Return error response for no account found
            $response = [
                'type' => 'login_response',
                'success' => false,
                'message' => 'No account found with this email. Please sign up.',
                'redirect' => 'student-signup.html' // Redirect URL
            ];
        }

        $stmt->close();
    } else {
        // Return error response for database error
        $response = [
            'type' => 'login_response',
            'success' => false,
            'message' => 'Database error. Please try again later.'
        ];
    }

    $conn->close();
} else {
    // Return error response for invalid request method
    $response = [
        'type' => 'login_response',
        'success' => false,
        'message' => 'Invalid request method.'
    ];
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
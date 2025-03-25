<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'db.php'; // Ensure this file contains the database connection logic.

    // Retrieve input and sanitize
    $email = trim($_POST['Reva_Email'] ?? '');
    $password = trim($_POST['Password'] ?? '');

    if (!empty($email) && !empty($password)) {
        $sql = "SELECT Password FROM professor_signup_form WHERE Reva_Email = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($dbPassword);
                $stmt->fetch();

                if (password_verify($password, $dbPassword)) {
                    $response = [
                        'type' => 'login_response',
                        'success' => true,
                        'message' => 'Login successful! Welcome Professor',
                        'redirect' => 'professor_option.html'
                    ];
                } else {
                    $response = [
                        'type' => 'login_response',
                        'success' => false,
                        'message' => 'Incorrect password. Please try again.',
                        'redirect' => 'professor-login.html'
                    ];
                }
            } else {
                $response = [
                    'type' => 'login_response',
                    'success' => false,
                    'message' => 'No account found with this email. Please sign up.',
                    'redirect' => 'proffsignup.html'
                ];
            }

            $stmt->close();
        } else {
            $response = [
                'type' => 'login_response',
                'success' => false,
                'message' => 'Database error. Please try again later.'
            ];
        }
    } else {
        $response = [
            'type' => 'login_response',
            'success' => false,
            'message' => 'Please fill in all fields.',
            'redirect' => 'professor-login.html'
        ];
    }

    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($response);

    $conn->close();
}
?>
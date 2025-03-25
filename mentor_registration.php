<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include database connection
require 'db.php';

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Retrieve and sanitize form data
    $name = trim($data['name'] ?? '');
    $rollNo = trim($data['profNo'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = password_hash(trim($data['password'] ?? ''), PASSWORD_DEFAULT); // Hash the password
    $phone = trim($data['phone'] ?? '');

    $stmt = null; // Initialize $stmt to null to prevent undefined variable error

    try {
        // Prepare SQL to insert data into `mentor registration` table
        $sql = "INSERT INTO `mentor registration` (`Name`, `Roll Number`, `REVA Email ID`, `Password`, `Phone Number`) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        // Bind parameters to the query
        $stmt->bind_param("sssss", $name, $rollNo, $email, $password, $phone);

        // Execute the query
        if ($stmt->execute()) {
            // Registration successful
            $response = [
                'type' => 'registration_response',
                'success' => true,
                'message' => 'Registration successful! Redirecting to login page...',
                'redirect' => 'mentor-login.html'
            ];
        } else {
            // Error during execution
            throw new Exception("Execution failed: " . $stmt->error);
        }
    } catch (Exception $e) {
        // Handle exceptions and show error messages
        $response = [
            'type' => 'registration_response',
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    } finally {
        // Close the statement if it was successfully initialized
        if ($stmt) {
            $stmt->close();
        }
        // Close the database connection
        $conn->close();
    }
} else {
    $response = [
        'type' => 'registration_response',
        'success' => false,
        'message' => 'No POST data received.'
    ];
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
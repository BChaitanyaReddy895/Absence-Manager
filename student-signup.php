<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'db.php'; // Include the database connection file

    // Retrieve form data
    $fullName = $_POST['full_name'] ?? '';
    $srn = $_POST['SRN'] ?? '';
    $email = $_POST['REVA_Email_id'] ?? '';
    $password = $_POST['password'] ?? '';
    $phone = $_POST['phone_number'] ?? '';

    // Validate input
    if (empty($fullName) || empty($srn) || empty($email) || empty($password) || empty($phone)) {
        $response = [
            'type' => 'signup_response',
            'success' => false,
            'message' => 'Please fill in all fields.'
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // Check if SRN already exists
    $checkSQL = "SELECT SRN FROM student_signup WHERE SRN = ?";
    $checkStmt = $conn->prepare($checkSQL);
    $checkStmt->bind_param("s", $srn);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $response = [
            'type' => 'signup_response',
            'success' => false,
            'message' => 'This SRN already exists. Please use a different SRN or log in.'
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        $checkStmt->close();
        exit;
    }
    $checkStmt->close();

    try {
        // Hash the password before storing it
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert data into the database
        $sql = "INSERT INTO student_signup (full_name, SRN, REVA_Email_id, password, phone_number) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $fullName, $srn, $email, $hashedPassword, $phone);

        if ($stmt->execute()) {
            $response = [
                'type' => 'signup_response',
                'success' => true,
                'message' => 'Signup successful! Redirecting to login page.',
                'redirect' => 'student-login.html' // Redirect URL
            ];
        } else {
            $response = [
                'type' => 'signup_response',
                'success' => false,
                'message' => 'An error occurred while signing up. Please try again.'
            ];
        }
    } catch (mysqli_sql_exception $e) {
        $response = [
            'type' => 'signup_response',
            'success' => false,
            'message' => 'An error occurred: ' . $e->getMessage()
        ];
    }

    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($response);

    // Close the database connection
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
} else {
    // Handle invalid request method
    $response = [
        'type' => 'signup_response',
        'success' => false,
        'message' => 'Invalid request method.'
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
}
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'db.php'; // Include the database connection file

    // Retrieve JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Retrieve form data
    $name = $data['name'] ?? '';
    $rollNumber = $data['roll_number'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    // Validate input
    if (empty($name) || empty($rollNumber) || empty($email) || empty($password)) {
        $response = [
            'type' => 'signup_response',
            'success' => false,
            'message' => 'Please fill in all fields.'
        ];
        echo json_encode($response);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response = [
            'type' => 'signup_response',
            'success' => false,
            'message' => 'Please enter a valid email address.'
        ];
        echo json_encode($response);
        exit;
    }

    // Check if Roll Number already exists
    $checkSQL = "SELECT `Roll Number` FROM professor_signup_form WHERE `Roll Number` = ?";
    $checkStmt = $conn->prepare($checkSQL);
    $checkStmt->bind_param("s", $rollNumber);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $response = [
            'type' => 'signup_response',
            'success' => false,
            'message' => 'This Roll Number already exists. Please use a different Roll Number or log in.'
        ];
        echo json_encode($response);
        $checkStmt->close();
        exit;
    }
    $checkStmt->close();

    try {
        // Hash the password before storing it
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert data into the database
        $sql = "INSERT INTO professor_signup_form (`Name`, `Roll Number`, `Reva_Email`, `Password`) 
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $rollNumber, $email, $hashedPassword);

        $stmt->execute();
        $response = [
            'type' => 'signup_response',
            'success' => true,
            'message' => 'Signup successful! Redirecting to login page.',
            'redirect' => 'professor-login.html'
        ];
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

    $stmt->close();
    $conn->close();
}
?>
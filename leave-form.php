<?php
// Include the database connection file
include 'db.php';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and fetch form data
    $name = $conn->real_escape_string(trim($_POST['name'] ?? ''));
    $srn = $conn->real_escape_string(trim($_POST['srn'] ?? ''));
    $course = $conn->real_escape_string(trim($_POST['course'] ?? ''));
    $email = $conn->real_escape_string(trim($_POST['email'] ?? ''));
    $section = $conn->real_escape_string(trim($_POST['section'] ?? ''));
    $mentorName = $conn->real_escape_string(trim($_POST['mentorName'] ?? ''));
    $mentorEmail = $conn->real_escape_string(trim($_POST['mentorEmail'] ?? ''));
    $description = $conn->real_escape_string(trim($_POST['description'] ?? ''));

    // Validate input
    if (empty($name) || empty($srn) || empty($course) || empty($email) || empty($section) || empty($mentorName) || empty($mentorEmail) || empty($description)) {
        $response = [
            'type' => 'leave_response',
            'success' => false,
            'message' => 'Please fill in all fields.'
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // Handle file upload
    $supportingFiles = null;
    if (isset($_FILES['upload']) && $_FILES['upload']['error'] === UPLOAD_ERR_OK) {
        $supportingFiles = file_get_contents($_FILES['upload']['tmp_name']);
    }

    // Insert data into the database
    $sql = "INSERT INTO leave_form (Name, SRN, Course, Reva_Email, Section, Mentor_Name, Mentor_Email, Description, Supporting_files) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sssssssss", $name, $srn, $course, $email, $section, $mentorName, $mentorEmail, $description, $supportingFiles);

        if ($stmt->execute()) {
            $response = [
                'type' => 'leave_response',
                'success' => true,
                'message' => 'Leave form submitted successfully!'
            ];
        } else {
            $response = [
                'type' => 'leave_response',
                'success' => false,
                'message' => 'Error: ' . $stmt->error
            ];
        }
        $stmt->close();
    } else {
        $response = [
            'type' => 'leave_response',
            'success' => false,
            'message' => 'Database error. Please try again later.'
        ];
    }

    $conn->close();
} else {
    $response = [
        'type' => 'leave_response',
        'success' => false,
        'message' => 'Invalid request method.'
    ];
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
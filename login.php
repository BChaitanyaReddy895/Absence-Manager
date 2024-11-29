<?php
include 'db.php'; // Include the database connection file

session_start(); // Start a session to store user data once logged in

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize user input
    $REVA_Email_id = filter_var($_POST['REVA_Email_id'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!empty($REVA_Email_id) && !empty($password)) {
        // Fetch user record from the database
        $stmt = $conn->prepare("SELECT * FROM student_signup WHERE REVA_Email_id = ?");
        $stmt->bind_param("s", $REVA_Email_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify the password
            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true); // Regenerate session ID for security
                $_SESSION['user_id'] = $user['SRN'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['REVA_Email_id'];
                $_SESSION['user_srn'] = $user['SRN'];
                $_SESSION['user_phone'] = $user['phone_number'];

                header("Location: dashboard.php");
                exit;
            } else {
                $_SESSION['error'] = "Invalid email or password.";
            }
        } else {
            $_SESSION['error'] = "User not found.";
        }

        $stmt->close();
    } else {
        $_SESSION['error'] = "Please fill in all fields.";
    }

    // Redirect to the login page with error messages
    header("Location: student-login.html");
    exit;
}

$conn->close();
?>

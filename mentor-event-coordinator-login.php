<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Include database connection
require 'db.php';

// Initialize error message
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch form inputs
    $email = trim($_POST['Reva_Email'] ?? '');
    $password = trim($_POST['Password'] ?? '');

    if (empty($email) || empty($password)) {
        // JavaScript alert for empty fields
        echo "<script>
            alert('Please fill in all fields.');
            window.history.back();
        </script>";
        exit();
    } else {
        // Prepare SQL query to fetch user details
        $sql = "SELECT * FROM `mentor and event cordinator registration` WHERE `REVA Email ID` = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                // Verify the password
                if (password_verify($password, $user['Password'])) {
                    // Set session variables
                    $_SESSION['event_coordinator_name'] = $user['Name'];
                    $_SESSION['event_coordinator_email'] = $user['REVA Email ID'];

                    // Redirect to the dashboard
                    header("Location: event_cordinator_dashboard.php");
                    exit();
                } else {
                    // JavaScript alert for invalid password
                    echo "<script>
                        alert('Invalid password. Please try again.');
                        window.history.back();
                    </script>";
                    exit();
                }
            } else {
                // JavaScript alert for unregistered email
                echo "<script>
                    alert('No account found with this email. Please sign up.');
                    window.location.href = 'mentor_eventcordinator_registration.html';
                </script>";
                exit();
            }
        } else {
            // SQL query preparation error
            echo "<script>
                alert('Something went wrong. Please try again later.');
                window.history.back();
            </script>";
            exit();
        }
    }
}
?>

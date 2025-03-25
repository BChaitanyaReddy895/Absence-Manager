// Include the database connection file
include 'db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve the submitted form data
    $email = $_POST['REVA_Email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validate input data
    if (!empty($email) && !empty($password)) {
        // Prepare the SQL statement to fetch the user details
        // Correct column name with backticks for spaces
        $sql = "SELECT `Roll Number Primary`, `Password` FROM attendance_coordinator_registration WHERE `REVA Email ID` = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($roll_number_primary, $hashed_password);
                $stmt->fetch();

                if (password_verify($password, $hashed_password)) {
                    // Set session variables
                    $_SESSION['user_email'] = $email;
                    $_SESSION['coordinator_roll_number'] = $roll_number_primary;

                    echo "<script>
                            alert('Login Successful! Welcome, Attendance Coordinator.');
                            window.location.href = 'attendance_coordinator_dashboard.php';
                          </script>";
                    exit();
                } else {
                    echo "<script>
                            alert('Incorrect password. Please try again.');
                            window.location.href = 'attendance_coordinator_login.html';
                          </script>";
                }
            } else {
                echo "<script>
                        alert('No account found with this email. Please sign up.');
                        window.location.href = 'attendance_coordinator_signup.html';
                      </script>";
            }
            $stmt->close();
        } else {
            error_log("Failed to prepare SQL statement for login.");
            echo "<script>
                    alert('Internal server error. Please try again later.');
                  </script>";
        }
    } else {
        echo "<script>
                alert('Both fields are required.');
                window.location.href = 'attendance_coordinator_login.html';
              </script>";
    }
} else {
    echo "<script>
            alert('Invalid request method.');
          </script>";
}

// Close the database connection
$conn->close();

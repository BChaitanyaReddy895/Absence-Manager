<?php
// Include the database connection file
include 'db.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve the submitted form data
    $name = $_POST['name'];
    $roll_number = $_POST['roll_number'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validate the input data (optional but recommended)
    if (!empty($name) && !empty($roll_number) && !empty($email) && !empty($password)) {
        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare the SQL statement to insert the data
        $sql = "INSERT INTO attendance_coordinator_registration (Name, `Roll Number`, `REVA Email ID`, Password) 
                VALUES (?, ?, ?, ?)";

        // Create a prepared statement
        if ($stmt = $conn->prepare($sql)) {
            // Bind the parameters to the statement
            $stmt->bind_param("ssss", $name, $roll_number, $email, $hashed_password);

            // Execute the statement
            if ($stmt->execute()) {
                echo "Registration successful. You can now <a href='attendance_coordinator_login.html'>login</a>.";
            } else {
                echo "Error: " . $stmt->error;
            }

            // Close the statement
            $stmt->close();
        } else {
            echo "Error: Could not prepare the SQL statement.";
        }
    } else {
        echo "All fields are required!";
    }

    // Close the database connection
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>

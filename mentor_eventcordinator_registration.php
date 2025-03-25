<?php
// Include the database connection file
include('db.php'); // Make sure db.php is in the same directory or provide the correct path

// Get form data
$name = $_POST['name'];
$profNo = $_POST['profNo'];
$email = $_POST['email'];
$password = $_POST['password'];
$phone = $_POST['phone'];

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert query
$sql = "INSERT INTO `mentor and event cordinator registration` (`Name`, `Roll Number`, `REVA Email ID`, `Password`, `Phone Number`) 
        VALUES ('$name', '$profNo', '$email', '$hashedPassword', '$phone')";

// Execute SQL statement and check if the data was inserted
if ($conn->query($sql) === TRUE) {
    echo "Registration successful!";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Close connection
$conn->close();
?>

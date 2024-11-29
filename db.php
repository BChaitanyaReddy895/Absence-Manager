<?php
$servername = "localhost"; // Use your server's IP if not localhost
$username = "root";        // MySQL Workbench username
$password = "Chaitu895@";  // MySQL Workbench password
$dbname = "reva_university";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


?>

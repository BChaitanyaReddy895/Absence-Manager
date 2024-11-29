<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login if not logged in
    header("Location: student-login.html");
    exit;
}

// Display user information securely
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            text-align: center;
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            width: 600px;
        }
        h1 {
            margin-bottom: 20px;
            color: #333;
        }
        .button {
            padding: 10px 20px;
            margin: 10px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background-color: orange;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
        <p>Email: <?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
        <p>SRN: <?php echo htmlspecialchars($_SESSION['user_srn']); ?></p>
        <p>Phone: <?php echo htmlspecialchars($_SESSION['user_phone']); ?></p>
        <p><a href="logout.php" class="button">Logout</a></p>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
</head>
<body>
    <h1>Forgot Password</h1>
    <form action="process-forgot-password.php" method="POST">
        <label for="email">Enter your registered email:</label>
        <input type="email" id="email" name="email" required>
        <button type="submit">Send Reset Link</button>
    </form>
</body>
</html>

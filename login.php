<?php

session_start();
include 'config.php';

//  direct to search.php
if (isset($_SESSION['username'])) {
    header("Location: search.php");
    exit();
}

// seccess
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $successMessage = "Registration successful! Please log in.";
}

// form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if (empty($username) || empty($password)) {
        $error = "Please enter your username and password.";
    } else {
        // require
        $stmt = $conn->prepare("SELECT id, username, email, password, created_at FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id, $username, $email, $hashed_password, $created_at);
        $stmt->fetch();

        if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
            $_SESSION["user_id"] = $id;
            $_SESSION["username"] = $username;
            $_SESSION["email"] = $email;
            $_SESSION["created_at"] = $created_at;

            header("Location: search.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - YumGuide</title>
</head>
<body>

<h2>Login to YumGuide</h2>

<?php 
if (isset($successMessage)) echo "<p style='color: green;'>$successMessage</p>";
if (isset($error)) echo "<p style='color: red;'>$error</p>"; 
?>

<form action="login.php" method="POST">
    <label>Username:</label>
    <input type="text" name="username" required>

    <label>Password:</label>
    <input type="password" name="password" required>

    <button type="submit">Login</button>
</form>

<p>Don't have an account? <a href="register.php">Sign Up</a></p>
<p><a href="forgot_password.php">Forgot Password?</a></p>

</body>
</html>

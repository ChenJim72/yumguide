<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "This email is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);

            if ($stmt->execute()) {
                header("Location: index.php?success=1");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - YumGuide</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #F6F4EB;
        }
        .main-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            padding: 20px;
        }
        .register-box {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }
        h2 {
            color: #4682A9;
            margin-bottom: 20px;
        }
        label {
            display: block;
            text-align: left;
            margin-top: 15px;
            font-weight: bold;
            color: #4682A9;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 50px;
            font-size: 16px;
        }
        input:focus {
            border-color: #4682A9;
            outline: none;
            box-shadow: 0 0 6px rgba(70,130,169,0.5);
        }
        button {
            width: 100%;
            margin-top: 25px;
            padding: 12px;
            background-color: #4682A9;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        button:hover {
            background-color: #5C93B7;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .message {
            font-size: 14px;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
        }
        .message.error {
            background: #FDEDEC;
            color: #C0392B;
            border-left: 4px solid #E74C3C;
            text-align: left;
        }
        p {
            margin-top: 20px;
            font-size: 14px;
        }
        a {
            color: #4682A9;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
  <div class="main-container">
    <div class="register-box">
      <h2><i class="fa fa-user-plus"></i> Sign Up</h2>
      <?php if (isset($error)) echo "<div class='message error'>$error</div>"; ?>
      <form action="register.php" method="POST">
        <label>Username:</label>
        <input type="text" name="username" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <button type="submit">Register</button>
      </form>
      <p>Already have an account? <a href="index.php">Login here</a>.</p>
    </div>
  </div>
</body>
</html>

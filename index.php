<?php
session_start();
include 'config.php';

// direct to search.php
if (isset($_SESSION['username'])) {
    header("Location: search.php");
    exit();
}

if (isset($_GET['success']) && $_GET['success'] == 1) {
    $successMessage = "Registration successful! Please log in.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if (empty($username) || empty($password)) {
        $error = "Please enter your username and password.";
    } else {
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    * {
      box-sizing: border-box;
    }
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
      flex-wrap: wrap;
      padding: 20px;
    }
    .content {
      flex: 1;
      max-width: 500px;
      padding: 20px;
      margin: 10px;
    }
    .introduction-container,
    .login-container {
      background: white;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      padding: 30px;
      text-align: center;
    }
    .introduction-container h2 {
      color: #4682A9;
      margin-bottom: 10px;
    }
    .introduction-container p {
      font-size: 16px;
      line-height: 1.6;
      color: #444;
    }
    .logo-img {
      max-width: 150px;
      margin-bottom: 15px;
    }
    .login-container h2 {
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
      box-shadow: 0 0 6px rgba(70, 130, 169, 0.5);
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
    .login-container p {
      margin-top: 15px;
      font-size: 14px;
    }
    .login-container a {
      color: #4682A9;
      text-decoration: none;
    }
    .login-container a:hover {
      text-decoration: underline;
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
    .message.success {
      background: #EAF8EA;
      color: #2E7D32;
      border-left: 4px solid #27AE60;
    }
    @media (max-width: 768px) {
      .main-container {
        flex-direction: column;
      }
      .content {
        max-width: 100%;
      }
    }
  </style>
</head>
<body>
  <div class="main-container">
    <div class="content">
      <div class="introduction-container">
        <img src="pictures/logo.png" alt="YumGuide Logo" class="logo-img">
        <h2>Login to YumGuide</h2>
        <h4>Explore · Discover · Enjoy</h4>
        <p>
            Explore your flavorful adventure, discover delicious finds, 
            and enjoy the right food together with the passionate Yum Guys community!
        </p>
      </div>
    </div>
    <div class="content">
      <div class="login-container">
        <?php if (isset($error)): ?>
        <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($successMessage)): ?>
        <div class="message success"><?php echo $successMessage; ?></div>
        <?php endif; ?>

        <form action="index.php" method="POST">
          <label><i class="fa fa-user"></i> Username:</label>
          <input type="text" name="username" required>

          <label><i class="fa fa-lock"></i> Password:</label>
          <input type="password" name="password" required>

          <button type="submit">Login</button>
        </form>

        <p>Don't have an account? <a href="register.php">Sign Up</a></p>
        <p><a href="forgot_password.php">Forgot Password?</a></p>
      </div>
    </div>
  </div>
</body>
</html>

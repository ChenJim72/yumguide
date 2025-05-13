<?php
$servername = "localhost";   
$username = "root";
$password = "example_password";
$dbname = "yumguide";

if (!isset($conn)) { 
    $conn = mysqli_connect($servername, $username, $password, $dbname);
    if (!$conn) {
        die("Database connection failed: " . mysqli_connect_error());
    }
}
?>

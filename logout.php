<?php
session_start();

// clean session 
$_SESSION = array();

// destroy session
session_destroy();

// redirect
header("Location: index.php");
exit();
?>

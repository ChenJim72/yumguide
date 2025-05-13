<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 5) {
    header('Location: index.php');
    exit();
}
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productId = intval($_POST['product_id']);
    $adminId = $_SESSION['user_id'];

    $sql = "UPDATE products 
            SET is_approved = 1, approved_by = $adminId, approved_at = NOW() 
            WHERE id = $productId";
    mysqli_query($conn, $sql);
}

header('Location: admin_review.php');
exit();

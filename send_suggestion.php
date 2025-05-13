<?php
session_start();
header('Content-Type: application/json');
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$suggestion = isset($_POST['suggestion']) ? trim($_POST['suggestion']) : '';

if ($product_id <= 0 || empty($suggestion)) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit();
}

$stmt = $conn->prepare("INSERT INTO suggestions (product_id, user_id, suggestion_text, created_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iis", $product_id, $user_id, $suggestion);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $stmt->error]);
}
?>

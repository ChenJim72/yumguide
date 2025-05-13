<?php
// ✅ submit_review.php (AJAX)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit();
}

$user_id     = $_SESSION['user_id'];
$product_id  = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$rating      = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
$review_text = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';

if ($product_id <= 0 || $rating <= 0 || empty($review_text)) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit();
}

$stmt = $conn->prepare("INSERT INTO reviews (user_id, product_id, review_text, rating, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
$stmt->bind_param("iisi", $user_id, $product_id, $review_text, $rating);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Review submitted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $stmt->error]);
}
?>

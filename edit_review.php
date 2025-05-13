<?php
// ✅ edit_review.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');
include 'config.php';

// ✅ verify
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// ✅ connection check
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit();
}

// ✅ recieve form
$user_id     = $_SESSION['user_id'];
$review_id   = isset($_POST['review_id']) ? intval($_POST['review_id']) : 0;
$rating      = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
$review_text = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';

if ($review_id <= 0 || $rating <= 0 || empty($review_text)) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit();
}

// ✅ update
$update_sql = "UPDATE reviews SET review_text = ?, rating = ?, updated_at = NOW() WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($update_sql);
$stmt->bind_param("siii", $review_text, $rating, $review_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Review updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $stmt->error]);
}
?>

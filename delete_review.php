<?php
session_start();
header('Content-Type: application/json');
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$review_id = intval($_POST['review_id']);

$stmt = $conn->prepare("DELETE FROM reviews WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $review_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

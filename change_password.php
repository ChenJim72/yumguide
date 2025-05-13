<?php
ob_start();
session_start();
header('Content-Type: application/json');
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (!$current_password || !$new_password || !$confirm_password) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit();
}

if ($new_password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'New passwords do not match.']);
    exit();
}

$stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo json_encode(['success' => false, 'message' => 'User not found.']);
    exit();
}

if (!password_verify($current_password, $row['password'])) {
    echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
    exit();
}

$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$update_stmt->bind_param("si", $hashed_password, $user_id);

if ($update_stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update password.']);
}

ob_end_flush();
?>

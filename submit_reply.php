<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$review_id = intval($_POST['review_id']);
$reply_text = trim($_POST['reply_text']);
$parent_reply_id = isset($_POST['parent_reply_id']) ? intval($_POST['parent_reply_id']) : null;

if ($reply_text !== '') {
    $stmt = $conn->prepare("INSERT INTO review_replies (review_id, user_id, reply_text, parent_reply_id, created_at)
                            VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("iisi", $review_id, $user_id, $reply_text, $parent_reply_id);
    $stmt->execute();

    echo json_encode([
        'status' => 'success',
        'username' => $_SESSION['username'],
        'reply_text' => htmlspecialchars($reply_text),
        'created_at' => date("Y-m-d H:i")
    ]);
} else {
    echo json_encode(['status' => 'error']);
}
?>
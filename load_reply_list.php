<?php
// load_reply_list.php
session_start();
header('Content-Type: text/html; charset=UTF-8');
include "config.php";

if (!isset($_POST['review_id'])) {
    echo "<p>Error: Missing review ID</p>";
    exit;
}

$review_id = intval($_POST['review_id']);
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

$reply_sql = "SELECT rr.*, u.username FROM review_replies rr
              JOIN users u ON rr.user_id = u.id
              WHERE rr.review_id = ?
              ORDER BY rr.created_at ASC";

$stmt = $conn->prepare($reply_sql);
$stmt->bind_param("i", $review_id);
$stmt->execute();
$result = $stmt->get_result();

while ($reply = $result->fetch_assoc()) {
    echo '<div class="reply-item" data-id="' . $reply['id'] . '">';
    echo '<strong>' . htmlspecialchars($reply['username']) . '</strong>: ' . htmlspecialchars($reply['reply_text']);
    echo ' <span style="color:#999; font-size:0.85em;">' . $reply['created_at'] . '</span>';

    // ✅ if own comment add trash icon
    if ($reply['user_id'] == $user_id) {
        echo ' <span class="delete-btn" onclick="deleteReply(' . $reply['id'] . ')">
                <svg class="icon delete-icon" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" width="16" height="16">
                    <path d="M15.5 3H11V1.5a1.5 1.5 0 0 0-1.5-1.5h-3A1.5 1.5 0 0 0 5 1.5V3H0.5a0.5 0 0 0 0 1H2v10.5A1.5 1.5 0 0 0 3.5 16h9A1.5 1.5 0 0 0 14 14.5V4h1.5a0.5 0.5 0 0 0 0-1zM6 1.5a0.5 0.5 0 0 1 0.5-0.5h3a0.5 0.5 0 0 1 0.5 0.5V3H6zM13 14.5a0.5 0.5 0 0 1-0.5 0.5h-9a0.5 0.5 0 0 1-0.5-0.5V4h10zM5.5 5a0.5 0.5 0 0 1 1 0v8a0.5 0.5 0 0 1-1 0V5zm2.5 0a0.5 0.5 0 0 1 1 0v8a0.5 0.5 0 0 1-1 0V5zm2.5 0a0.5 0.5 0 0 1 1 0v8a0.5 0.5 0 0 1-1 0V5z"/>
                </svg>
              </span>';
    }

    echo '</div>';
}
$stmt->close();

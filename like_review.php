<?php
session_start();
include "config.php";

$review_id = intval($_POST['review_id']);
$user_id = $_SESSION['user_id'];

// like or not
$check = mysqli_query($conn, "SELECT * FROM review_likes WHERE review_id=$review_id AND user_id=$user_id");

if (mysqli_num_rows($check) == 0) {
    // like - not yet
    mysqli_query($conn, "INSERT INTO review_likes (review_id, user_id) VALUES ($review_id, $user_id)");
    $status = 'liked';
} else {
    // liked
    mysqli_query($conn, "DELETE FROM review_likes WHERE review_id=$review_id AND user_id=$user_id");
    $status = 'unliked';
}

// count like
$total_likes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM review_likes WHERE review_id=$review_id"))['cnt'];

echo json_encode(['status' => $status, 'likes' => $total_likes]);
?>

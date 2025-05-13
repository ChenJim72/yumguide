<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 5) {
    header('Location: index.php');
    exit();
}

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['suggestion_id']) ? intval($_POST['suggestion_id']) : 0;
    $action = $_POST['action'] ?? '';

    $status_map = [
        'approve' => 'approved',
        'reject' => 'rejected'
    ];

    if (array_key_exists($action, $status_map) && $id > 0) {
        $status_value = $status_map[$action];
        $stmt = $conn->prepare("UPDATE suggestions SET status = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("si", $status_value, $id);
            $stmt->execute();
            $stmt->close();
        } else {
            echo "SQL Error: " . $conn->error;
            exit();
        }
    }
    header("Location: admin_review.php");
    exit();
}
?>

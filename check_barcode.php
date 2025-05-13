<?php
require 'config.php';

header('Content-Type: application/json');

$barcode = $_GET['barcode'] ?? '';
$exists = false;

if (!empty($barcode)) {
    $stmt = $conn->prepare("SELECT id FROM products WHERE barcode = ?");
    $stmt->bind_param("s", $barcode);
    $stmt->execute();
    $stmt->store_result();
    $exists = ($stmt->num_rows > 0);
}

echo json_encode(['exists' => $exists]);
?>

<?php
include "config.php";

if (isset($_GET['category_id'])) {
    $category_id = intval($_GET['category_id']);
    $stmt = $conn->prepare("SELECT id, characteristic_name FROM characteristics WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $characteristics = [];
    while ($row = $result->fetch_assoc()) {
        $characteristics[] = $row;
    }
    echo json_encode($characteristics);
}
?>

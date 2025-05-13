<?php
require 'config.php'; 

header('Content-Type: application/json');

$query = "SELECT id, store_name FROM stores 
          ORDER BY CASE WHEN store_name = 'Other' THEN 1 ELSE 0 END, store_name ASC";
$result = $conn->query($query);

$stores = [];

while ($row = $result->fetch_assoc()) {
    $stores[] = [
        "id" => ($row["store_name"] === "Other") ? "Other" : $row["id"], 
        "text" => $row["store_name"]
    ];
}

echo json_encode($stores);
?>

<?php
include '../db.php';

$sql = "SELECT income_category_id, category_name FROM income_category";
$result = $conn->query($sql);

$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

echo json_encode(['status' => 'success', 'categories' => $categories]);
$conn->close();
?>

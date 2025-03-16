<?php
include '../db.php';

// Ensure the response is returned as JSON
header('Content-Type: application/json');

// Initialize an array to store categories
$categories = [];

// Execute the query
$sql = "SELECT category_id, category_name FROM Categories";
$result = $conn->query($sql);

if ($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row; // Add each category to the array
        }
    }
} else {
    // Return an error if the query fails
    echo json_encode(['error' => 'Database query failed']);
    $conn->close();
    exit();
}

// Return the JSON list of categories
echo json_encode($categories);
$conn->close();
?>

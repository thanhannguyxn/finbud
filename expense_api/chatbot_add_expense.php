<?php
session_start();
include '../db.php'; // Connect to the database

header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Retrieve data from the request
$data = json_decode(file_get_contents('php://input'), true);

$amount = $data['amount'] ?? null;
$sub_category_name = $data['sub_category_name'] ?? null;
$description = $data['description'] ?? 'Chatbot expense';
$expense_date = $data['expense_date'] ?? date('Y-m-d');

// Validate input data
if (!$amount || !$sub_category_name) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit();
}

// Find sub_category_id and category_id from the sub_category table
$stmt = $conn->prepare("SELECT sub_category_id, category_id FROM sub_category WHERE LOWER(sub_category_name) = LOWER(?)");
$stmt->bind_param("s", $sub_category_name);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $sub_category_id = $row['sub_category_id'];
    $category_id = $row['category_id'];
} else {
    echo json_encode(['status' => 'error', 'message' => 'Sub-category not found']);
    exit();
}

// Add the expense to the database
$stmt = $conn->prepare("INSERT INTO expenses_transaction (user_id, category_id, sub_category_id, amount, expense_date, description) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iiidss", $user_id, $category_id, $sub_category_id, $amount, $expense_date, $description);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Expense added successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to add expense: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>

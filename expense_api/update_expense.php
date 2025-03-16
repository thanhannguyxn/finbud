<?php
session_start();
header('Content-Type: application/json'); // Set response type to JSON
include '../db.php';

$response = [
    'status' => 'error',
    'message' => 'Invalid request'
];

// Check if the user is logged in and if the request method is POST
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(401); // Unauthorized
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

$user_id = $_SESSION['user_id'];

// Validate and retrieve data from POST
$expense_id = isset($_POST['expense_id']) ? intval($_POST['expense_id']) : null;
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : null;
$expense_date = isset($_POST['expense_date']) ? $_POST['expense_date'] : null;
$category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : null;
$sub_category_id = isset($_POST['sub_category_id']) ? intval($_POST['sub_category_id']) : null;
$description = isset($_POST['description']) ? trim($_POST['description']) : null;

// Check the validity of input data
if (!$expense_id || !$amount || !$expense_date || !$category_id || !$description) {
    http_response_code(400); // Bad Request
    $response['message'] = 'Missing or invalid input data.';
    echo json_encode($response);
    exit();
}

// Prepare SQL statement to update the transaction
$sql = "UPDATE expenses_transaction 
        SET amount = ?, expense_date = ?, category_id = ?, sub_category_id = ?, description = ? 
        WHERE expense_transaction_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500); // Internal Server Error
    $response['message'] = 'Failed to prepare the SQL statement.';
    echo json_encode($response);
    exit();
}

$stmt->bind_param("dsissii", $amount, $expense_date, $category_id, $sub_category_id, $description, $expense_id, $user_id);

// Execute the SQL statement
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        // Update successful
        $response['status'] = 'success';
        $response['message'] = 'Expense updated successfully.';
    } else {
        // No matching record found or no changes made
        http_response_code(404); // Not Found
        $response['message'] = 'Expense not found or no changes were made.';
    }
} else {
    // Error executing the SQL statement
    http_response_code(500); // Internal Server Error
    $response['message'] = 'Failed to update expense: ' . $stmt->error;
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>

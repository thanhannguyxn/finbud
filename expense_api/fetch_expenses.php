<?php
session_start();
header('Content-Type: application/json');
include '../db.php'; // Connect to the database

// Check if the user is authenticated
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "User not authenticated"]);
    exit();
}

$user_id = $_SESSION['user_id']; // Get `user_id` from the session

// Query data from the `expenses_transaction` table
$sql = "SELECT et.expense_transaction_id, et.amount, et.expense_date, et.description, 
               et.sub_category_id, c.category_name, sc.sub_category_name 
        FROM expenses_transaction et
        JOIN Categories c ON et.category_id = c.category_id
        LEFT JOIN sub_category sc ON et.sub_category_id = sc.sub_category_id
        WHERE et.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$expenses = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $expenses[] = [
            'expense_transaction_id' => $row['expense_transaction_id'],
            'amount' => $row['amount'],
            'expense_date' => $row['expense_date'],
            'description' => $row['description'],
            'category_name' => $row['category_name'],
            'sub_category_name' => $row['sub_category_name'] ?? null // Sub-category can be null
        ];
    }
}

// Return the result as JSON
echo json_encode($expenses);

$stmt->close();
$conn->close();
?>
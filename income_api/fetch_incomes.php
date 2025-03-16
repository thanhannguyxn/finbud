<?php
session_start();
include '../db.php'; // Connect to the database

header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Query data from the income and income_category tables
$sql = "SELECT i.income_id, i.user_id, i.income_category_id, ic.category_name, 
               i.amount, i.income_date, i.description 
        FROM income i
        JOIN income_category ic ON i.income_category_id = ic.income_category_id
        WHERE i.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

// Prepare the data for response
$incomes = [];
while ($row = $result->fetch_assoc()) {
    $incomes[] = [
        'income_id' => $row['income_id'],
        'user_id' => $row['user_id'],
        'income_category_id' => $row['income_category_id'],
        'category_name' => $row['category_name'],
        'amount' => (float) $row['amount'], // Cast to float
        'income_date' => $row['income_date'],
        'description' => $row['description'],
    ];
}

// Return data as JSON
echo json_encode([
    'status' => 'success',
    'incomes' => $incomes,
]);

// Close the connection
$stmt->close();
$conn->close();
?>

<?php
session_start();
include '../db.php'; // Connect to the database

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Query all Expense Transactions of the user
$sql = "SELECT et.expense_transaction_id, et.amount, et.expense_date, et.description, c.category_name, sc.sub_category_name 
        FROM `expenses_transaction` et
        JOIN Categories c ON et.category_id = c.category_id
        LEFT JOIN sub_category sc ON et.sub_category_id = sc.sub_category_id
        WHERE et.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Set headers for CSV file output
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="expenses.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Write column headers
fputcsv($output, array('Amount', 'Date', 'Category', 'Sub-Category', 'Description'));

// Write data rows
while ($row = $result->fetch_assoc()) {
    fputcsv($output, array(
        $row['amount'],
        $row['expense_date'],
        $row['category_name'],
        $row['sub_category_name'],
        $row['description']
    ));
}

// Close connection
fclose($output);
$stmt->close();
$conn->close();
exit();
?>
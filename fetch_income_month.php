<?php
session_start();
include 'db.php'; 

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$year = $_GET['year'] ?? date('Y');
$startMonth = $_GET['startMonth'] ?? "$year-01";
$endMonth = $_GET['endMonth'] ?? "$year-12";

// Query Income and Expense within the specified time range
$sql = "SELECT 
            DATE_FORMAT(i.income_date, '%Y-%m') AS month, 
            SUM(i.amount) AS total_income,
            (SELECT SUM(e.amount) 
             FROM expenses_transaction e 
             WHERE e.user_id = i.user_id 
             AND DATE_FORMAT(e.expense_date, '%Y-%m') = DATE_FORMAT(i.income_date, '%Y-%m')) AS total_expense
        FROM income i
        WHERE i.user_id = ?
          AND DATE_FORMAT(i.income_date, '%Y-%m') BETWEEN ? AND ?
        GROUP BY month
        ORDER BY month";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $user_id, $startMonth, $endMonth);
$stmt->execute();

$result = $stmt->get_result();

// Prepare the data for response
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'month' => $row['month'],
        'total_income' => (float)$row['total_income'],
        'total_expense' => (float)($row['total_expense'] ?? 0),
    ];
}

// Return the data as JSON
echo json_encode(['status' => 'success', 'data' => $data]);

$stmt->close();
$conn->close();
?>

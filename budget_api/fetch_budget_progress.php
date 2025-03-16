<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Query to calculate total budget, total spend and remaining budget
$sql = "SELECT 
            b.budget_id, 
            b.amount AS total_budget, 
            b.category_id, 
            b.start_date, 
            b.end_date, 
            COALESCE(c.category_name, 'Uncategorized') AS category_name,
            COALESCE(SUM(e.amount), 0) AS total_expense,
            (b.amount - COALESCE(SUM(e.amount), 0)) AS remaining_budget
        FROM budgets b
        LEFT JOIN expenses_transaction e
            ON b.user_id = e.user_id
            AND b.category_id = e.category_id
            AND e.expense_date BETWEEN b.start_date AND b.end_date
        LEFT JOIN categories c
            ON b.category_id = c.category_id
        WHERE b.user_id = ?
        GROUP BY b.budget_id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $data]);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
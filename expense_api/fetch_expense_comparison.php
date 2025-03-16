<?php
session_start();
header('Content-Type: application/json');
include '../db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$type = $_GET['type'] ?? 'week'; // Get the data type (week or month)

// Query data
if ($type === 'week') {
    $sql = "SELECT 
                SUM(CASE 
                        WHEN WEEK(expense_date) = WEEK(CURDATE()) THEN amount 
                        ELSE 0 
                    END) AS current_week,
                SUM(CASE 
                        WHEN WEEK(expense_date) = WEEK(CURDATE()) - 1 THEN amount 
                        ELSE 0 
                    END) AS last_week
            FROM expenses_transaction
            WHERE user_id = ?";
} else { // month
    $sql = "SELECT 
                SUM(CASE 
                        WHEN MONTH(expense_date) = MONTH(CURDATE()) THEN amount 
                        ELSE 0 
                    END) AS current_month,
                SUM(CASE 
                        WHEN MONTH(expense_date) = MONTH(CURDATE()) - 1 THEN amount 
                        ELSE 0 
                    END) AS last_month
            FROM expenses_transaction
            WHERE user_id = ?";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if ($type === 'week') {
    $response = [
        'last' => (float) $data['last_week'],
        'current' => (float) $data['current_week']
    ];
} else {
    $response = [
        'last' => (float) $data['last_month'],
        'current' => (float) $data['current_month']
    ];
}

echo json_encode(['status' => 'success', 'data' => $response]);
$stmt->close();
$conn->close();
?>
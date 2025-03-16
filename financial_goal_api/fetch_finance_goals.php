<?php
session_start();
header('Content-Type: application/json');
include '../db.php'; 

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// User ID (e.g., retrieved from session)
$user_id = $_SESSION['user_id'];

// SQL query to retrieve financial goals
$sqlGoals = "SELECT goal_id, goal_name, target_amount, current_amount, target_date 
             FROM financialgoals 
             WHERE user_id = ?";
$stmt = $conn->prepare($sqlGoals);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Response data
$goals = [];
while ($row = $result->fetch_assoc()) {
    $goals[] = $row;
}

// Return JSON data
header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'data' => $goals]);
?>

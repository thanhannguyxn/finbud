<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    echo "Error: User not logged in.";
    exit();
}

if (!isset($_GET['id'])) {
    echo "Error: No budget ID provided.";
    exit();
}

$budget_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

$delete_sql = "DELETE FROM budgets WHERE budget_id = ? AND user_id = ?";
$stmt = $conn->prepare($delete_sql);

if (!$stmt) {
    echo "Error: Unable to prepare SQL statement.";
    exit();
}

$stmt->bind_param("ii", $budget_id, $user_id);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "Error: Unable to delete budget.";
}

$stmt->close();
$conn->close();
?>
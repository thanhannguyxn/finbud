<?php
session_start();
include '../db.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$amount = $_POST['amount'];
$category_id = $_POST['category_id'];
$expense_date = $_POST['expense_date'];
$description = $_POST['description'];
$sub_category_id = $_POST['sub_category_id'];
// Insert the expense into the database
$sql = "INSERT INTO expenses_transaction (user_id, category_id, sub_category_id, amount, expense_date, description) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiidss", $user_id, $category_id, $sub_category_id, $amount, $expense_date, $description);

if ($stmt->execute()) {
    header("Location: ../expensepage.php");
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>

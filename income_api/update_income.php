<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $income_id = $_POST['income_id'];
    $income_category_id = $_POST['income_category_id'];
    $amount = $_POST['amount'];
    $income_date = $_POST['income_date'];
    $description = $_POST['description'];

    //update income 
    $sql = "UPDATE income SET income_category_id = ?, amount = ?, income_date = ?, description = ? WHERE income_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("idssi", $income_category_id, $amount, $income_date, $description, $income_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Income updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update income.";
    }

    $stmt->close();
    header("Location: ../income_page.php");
    exit();
}
?>

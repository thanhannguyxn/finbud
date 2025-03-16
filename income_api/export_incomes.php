<?php
session_start();
include '../db.php'; 

if (!isset($_SESSION['user_id'])) {
    die('Unauthorized');
}

$user_id = $_SESSION['user_id'];

// Query income data
$sql = "SELECT i.income_id, ic.category_name, i.amount, i.income_date, i.description
        FROM income i
        JOIN income_category ic ON i.income_category_id = ic.income_category_id
        WHERE i.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Set headers to force browser to download the CSV file
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=incomes.csv');

    // Create the output file
    $output = fopen('php://output', 'w');

    // Write the column headers
    fputcsv($output, ['Category', 'Amount', 'Date', 'Description']);

    // Write the database records to the CSV file
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['category_name'],
            number_format($row['amount'], 2), // Format the amount
            $row['income_date'],
            $row['description']
        ]);
    }

    fclose($output);
    exit();
} else {
    die('No income records found to export.');
}
?>

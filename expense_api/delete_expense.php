<?php
session_start();
header('Content-Type: application/json'); // Set response type to JSON
include '../db.php'; // Connect to the database

$response = [
    "status" => "error",
    "message" => "Invalid request"
];

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    $response["message"] = "You must be logged in to perform this action.";
    echo json_encode($response);
    exit();
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $expense_id = intval($_GET['id']); // Ensure it's an integer
    $user_id = $_SESSION['user_id'];

    // Prepare the delete statement
    $delete_sql = "DELETE FROM expenses_transaction WHERE expense_transaction_id = ? AND user_id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("ii", $expense_id, $user_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Successfully deleted
            $response["status"] = "success";
            $response["message"] = "Expense deleted successfully.";
            header("Location: ../expensepage.php");
        } else {
            // No matching transaction found to delete
            http_response_code(404); // Not Found
            $response["message"] = "Expense not found or you do not have permission to delete it.";
        }
    } else {
        // Error executing the SQL statement
        http_response_code(500); // Internal Server Error
        $response["message"] = "Failed to delete expense. Please try again later.";
    }
    $stmt->close();
} else {
    // If no ID or ID is invalid
    http_response_code(400); // Bad Request
    $response["message"] = "Invalid expense ID.";
}

$conn->close();
echo json_encode($response);
?>
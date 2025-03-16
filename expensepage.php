<?php
session_start();
include 'db.php'; // Kết nối cơ sở dữ liệu

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch categories from the database
$sql = "SELECT category_id, category_name FROM categories";
$result = $conn->query($sql);

// Fetch remaining budgets and corresponding category names for the user
$remaining_budget_sql = "SELECT rb.remaining_budget, c.category_name
                         FROM remaining_budget rb
                         JOIN budgets b ON rb.budget_id = b.budget_id
                         JOIN categories c ON b.category_id = c.category_id
                         WHERE b.user_id = ?";
$remaining_budget_stmt = $conn->prepare($remaining_budget_sql);
$remaining_budget_stmt->bind_param("i", $user_id);
$remaining_budget_stmt->execute();
$remaining_budget_result = $remaining_budget_stmt->get_result();

// Fetch expense transactions for the user with category and sub-category details
$expense_sql = "SELECT et.expense_transaction_id, et.amount, et.expense_date, et.description, c.category_name, sc.sub_category_name
                FROM `expenses_transaction` et
                JOIN categories c ON et.category_id = c.category_id
                LEFT JOIN sub_category sc ON et.sub_category_id = sc.sub_category_id
                WHERE et.user_id = ?
                ORDER BY et.expense_date DESC";
$expense_stmt = $conn->prepare($expense_sql);
$expense_stmt->bind_param("i", $user_id);
$expense_stmt->execute();
$expense_result = $expense_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finbud - Expenses</title>
    <script src="https://unpkg.com/vue@3"></script>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }

        .container {
            margin-top: 50px;
        }

        /* Navigation Bar Styling */
        nav.navbar {
            background-color: #ffffff;
            border-bottom: 1px solid #ddd;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .navbar-nav .nav-link {
            color: #007bff;
            margin-right: 20px;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            color: #0056b3;
        }

        .navbar-brand {
            color: #007bff;
            font-weight: 600;
            font-size: 1.4em;
        }

        /* Card Styling */
        .card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: #007bff;
            color: white;
            font-weight: 600;
            border-radius: 10px 10px 0 0;
            padding: 12px 20px;
        }

        .card-body {
            padding: 20px;
        }

        .income-section {
            background-color: #e6f0ff;
            font-size: 1.3em;
            color: #0056b3;
            text-align: center;
            border-radius: 10px;
            margin-bottom: 20px;
            padding: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Table Styling */
        .table th,
        .table td {
            vertical-align: middle;
            text-align: center;
            padding: 12px;
        }

        .table-striped tbody tr:nth-child(odd) {
            background-color: #f2f2f2;
        }

        .table-bordered {
            border: 1px solid #dee2e6;
        }

        .table-bordered td,
        .table-bordered th {
            border: 1px solid #dee2e6;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                margin-top: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top">
        <a class="navbar-brand ml-3" href="#"><i class="fas fa-chart-bar"></i> FinBud Dashboard</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php"><i class="fas fa-home"></i> Home</a>
                </li>
                <li class="nav-item active">
                    <a class="nav-link" href="expensepage.php"><i class="fas fa-wallet"></i> Expenses</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="budget.php"><i class="fas fa-chart-line"></i> Budgets</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="finance_goalpage.php"><i class="fas fa-bullseye"></i> Goals</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="income_page.php"><i class="fas fa-coins"></i> Income</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reportpage.php"><i class="fas fa-file-alt"></i> Reports</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="filter.php"><i class="fas fa-file-alt"></i> Filter</a>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a id="nav-link" class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i>
                        Logout</a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="container">
        <!-- Add Expense Transaction Form Section -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-money-bill-wave"></i> Add Expense Transaction
            </div>
            <div class="card-body">
                <form action="expense_api/process_expense.php" method="post">
                    <div class="form-group">
                        <label for="amount"><i class="fas fa-dollar-sign"></i> Amount:</label>
                        <input type="number" min="0" step="0.01" name="amount" id="amount" class="form-control"
                            placeholder="Enter the expense amount eg,.$1000.00" required>
                    </div>
                    <div class="form-group">
                        <label for="expense_date"><i class="fas fa-calendar-day"></i> Expense Date:</label>
                        <input type="date" name="expense_date" id="expense_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="category_id"><i class="fas fa-list-alt"></i> Category:</label>
                        <select name="category_id" id="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='" . $row['category_id'] . "'>" . htmlspecialchars($row['category_name']) . "</option>";
                                }
                            } else {
                                echo "<option value=''>No categories available</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sub-category-id">Sub-Category:</label>
                        <select name="sub_category_id" id="sub-category-id" class="form-control">
                            <option value="">Select Sub-Category</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="description"><i class="fas fa-pencil-alt"></i> Description:</label>
                        <textarea name="description" id="description" rows="3" class="form-control"
                            placeholder="Enter a brief description"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Add Expense
                    </button>
                </form>
            </div>
        </div>

        <!-- Expense Transactions Section -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-list"></i> Expense Transactions
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th><i class="fas fa-dollar-sign"></i> Amount</th>
                            <th><i class="fas fa-calendar-day"></i> Date</th>
                            <th><i class="fas fa-list-alt"></i> Category</th>
                            <th><i class="fas fa-cogs"></i> Sub-Category</th>
                            <th><i class="fas fa-pencil-alt"></i> Description</th>
                            <th><i class="fas fa-cogs"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($expense_result->num_rows > 0) {
                            while ($row = $expense_result->fetch_assoc()) {
                                echo "<tr>
                                    <td>" . number_format($row['amount'], 2) . "</td>
                                    <td>" . htmlspecialchars($row['expense_date']) . "</td>
                                    <td>" . htmlspecialchars($row['category_name']) . "</td>
                                    <td>" . htmlspecialchars($row['sub_category_name'] ?? '') . "</td>
                                    <td>" . htmlspecialchars($row['description']) . "</td>
                                    <td>
                                        <button class='btn btn-warning btn-sm btn-edit-expense' data-id='" . $row['expense_transaction_id'] . "'>
                                            <i class='fas fa-edit'></i> Edit
                                        </button>
                                        <a href='expense_api/delete_expense.php?id=" . $row['expense_transaction_id'] . "' class='btn btn-danger btn-sm delete-expense'>
                                            <i class='fas fa-trash'></i> Delete
                                        </a>
                                    </td>
                                  </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center'>No expense transactions found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <a href="expense_api/export_expenses.php" class="btn btn-primary">
                    <i class="fas fa-file-csv"></i> Export to CSV
                </a>
            </div>
        </div>

        <!-- Edit Expense Modal -->
        <div class="modal fade" id="editExpenseModal" tabindex="-1" aria-labelledby="editExpenseModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editExpenseModalLabel">Edit Expense</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="editExpenseForm">
                            <input type="hidden" id="edit-expense-id">
                            <div class="form-group">
                                <label for="edit-amount">Amount:</label>
                                <input type="number" min="0" step="0.01" id="edit-amount" class="form-control"
                                    placeholder="e.g., $1000.00" required>
                            </div>
                            <div class="form-group">
                                <label for="edit-expense-date">Expense Date:</label>
                                <input type="date" id="edit-expense-date" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="edit-category">Category:</label>
                                <select id="edit-category" class="form-control" required>
                                    <!-- Categories will be loaded here -->
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit-sub-category">Sub-Category:</label>
                                <select id="edit-sub-category" class="form-control">
                                    <option value="">Select Sub-Category</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit-description">Description:</label>
                                <input type="text" id="edit-description" class="form-control"
                                    placeholder="Enter a brief description">
                            </div>
                            <button type="button" id="saveChanges" class="btn btn-primary">Save changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Bot -->
        <div id="chatbot-container"
            style="position: fixed; bottom: 20px; right: 20px; width: 350px; height: 500px; background-color: #f8f9fa; box-shadow: 0 4px 8px rgba(0,0,0,0.1); display: none; border-radius: 10px;">
            <div
                style="background-color: #007bff; color: white; padding: 10px; text-align: center; font-weight: bold; border-radius: 10px 10px 0 0;">
                FinBud Chatbot
                <span style="float: right; cursor: pointer;"
                    onclick="document.getElementById('chatbot-container').style.display='none'">X</span>
            </div>

            <div id="chatbot-messages" style="height: 400px; overflow-y: auto; padding: 10px;"></div>

            <div style="display: flex; align-items: center; padding: 10px;">
                <button id="voice-button"
                    style="margin-right: 10px; background-color: #007bff; color: white; border: none; border-radius: 50%; width: 40px; height: 40px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                    🎤
                </button>
                <input type="text" id="chatbot-input" placeholder="Enter expense (e.g., 200 food today)"
                    style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 5px;" />
            </div>
        </div>

        <!-- Toggle Chatbot -->
        <button
            style="position: fixed; bottom: 20px; right: 20px; background-color: #007bff; color: white; border: none; border-radius: 50%; width: 50px; height: 50px;"
            onclick="document.getElementById('chatbot-container').style.display='block'">
            💬
        </button>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="expense_api/filter_expense.js"></script>
    <script src="expense_api/chatbot.js"></script>
    <script>
        $(document).ready(function () {
            // Load sub-categories based on selected category
            $('#category_id').change(function () {
                const categoryId = $(this).val();
                $.ajax({
                    url: 'fetch_sub_categories.php',
                    method: 'GET',
                    data: { category_id: categoryId },
                    dataType: 'json', 
                    success: function (subCategories) {
                        let subCategoryOptions = '<option value="">Select Sub-Category</option>';
                        subCategories.forEach(subCategory => {
                            subCategoryOptions += `<option value="${subCategory.sub_category_id}">${subCategory.sub_category_name}</option>`;
                        });
                        $('#sub-category-id').html(subCategoryOptions);
                    },
                    error: function () {
                        alert("Failed to load sub-categories.");
                    }
                });
            });

            // Confirm delete action
            $(document).on('click', '.delete-expense', function (e) {
                if (!confirm("Are you sure you want to delete this expense?")) {
                    e.preventDefault();
                }
            });
            // Open modal
            $('.btn-edit-expense').click(function () {
                const expenseId = $(this).data('id');
                $.ajax({
                    url: 'expense_api/get_expense.php',
                    method: 'GET',
                    data: { id: expenseId },
                    dataType: 'json',
                    success: function (data) {
                        $('#edit-expense-id').val(data.expense_transaction_id);
                        $('#edit-amount').val(data.amount);
                        $('#edit-expense-date').val(data.expense_date);
                        $('#edit-description').val(data.description);
                        $.ajax({
                            url: 'fetch_main_categories.php',
                            method: 'GET',
                            dataType: 'json',
                            success: function (categories) {
                                let categoryOptions = '';
                                categories.forEach(category => {
                                    categoryOptions += `<option value="${category.category_id}" ${category.category_id == data.category_id ? 'selected' : ''}>${category.category_name}</option>`;
                                });
                                $('#edit-category').html(categoryOptions);
                                loadSubCategories(data.category_id, data.sub_category_id);
                            }
                        });
                        $('#editExpenseModal').modal('show');
                    },
                    error: function () {
                        alert("Failed to load expense data.");
                    }
                });
            });
            $('#edit-category').change(function () {
                loadSubCategories($(this).val());
            });
            function loadSubCategories(categoryId, selectedSubCategoryId = null) {
                $.ajax({
                    url: 'fetch_sub_categories.php',
                    method: 'GET',
                    data: { category_id: categoryId },
                    dataType: 'json',
                    success: function (subCategories) {
                        let subCategoryOptions = '<option value="">Select Sub-Category</option>';
                        subCategories.forEach(subCategory => {
                            subCategoryOptions += `<option value="${subCategory.sub_category_id}" ${subCategory.sub_category_id == selectedSubCategoryId ? 'selected' : ''}>${subCategory.sub_category_name}</option>`;
                        });
                        $('#edit-sub-category').html(subCategoryOptions);
                    }
                });
            }
            // Save changes for edit modal
            $('#saveChanges').click(function () {
                const expenseId = $('#edit-expense-id').val();
                const amount = $('#edit-amount').val();
                const expenseDate = $('#edit-expense-date').val();
                const categoryId = $('#edit-category').val();
                const subCategoryId = $('#edit-sub-category').val();
                const description = $('#edit-description').val();
                $.ajax({
                    url: 'expense_api/update_expense.php',
                    method: 'POST',
                    data: {
                        expense_id: expenseId,
                        amount: amount,
                        expense_date: expenseDate,
                        category_id: categoryId,
                        sub_category_id: subCategoryId,
                        description: description
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            alert("Expense updated successfully!");
                            location.reload();
                        } else {
                            alert("Failed to update expense: " + response.message);
                        }
                    },
                    error: function () {                   alert("An error occurred while updating the expense.");
                    }
                });
            });
        });
    </script>
</body>
</html>
<?php
// Close database connections
$remaining_budget_stmt->close();
$expense_stmt->close();
$conn->close();
?>
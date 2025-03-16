<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filter Expense Transactions</title>

    <script src="https://unpkg.com/vue@3"></script>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CSS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .table th,
        .table td {
            vertical-align: middle;
        }

        .btn {
            border-radius: 5px;
        }

        .modal-header {
            background-color: #343a40;
            color: #ffffff;
        } 

        .modal-footer .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
    </style>
</head>

<body>
    <div id="app">
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
                    <li class="nav-item ">
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
                    <li class="nav-item active">
                        <a class="nav-link " href="filter.php"><i class="fas fa-file-alt"></i> Filter</a>
                    </li>

                </ul>
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a id="nav-link" class="nav-link text-danger" href="logout.php"><i
                                class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </nav>
        <div class="container mt-4">
            <h1 class="text-center  mb-4">Filter Expense Transactions</h1>
            <div class="row mb-3">
                <div class="col-md-4 col-12 mb-3">
                    <label>Minimum Amount:</label>
                    <input type="number" v-model="filters.minAmount" class="form-control"
                        placeholder="Enter Minimum Amount">
                </div>
                <div class="col-md-4 col-12 mb-3">
                    <label>Maximum Amount:</label>
                    <input type="number" v-model="filters.maxAmount" class="form-control"
                        placeholder="Enter Maximum Amount">
                </div>
                <div class="col-md-4 col-12 mb-3">
                    <label>Category:</label>
                    <select v-model="filters.category" @change="fetchSubCategories(filters.category)"
                        class="form-control">
                        <option value="">All Categories</option>
                        <option v-for="category in categories || []" :key="category.category_name"
                            :value="category.category_name">
                            {{ category.category_name }}
                        </option>
                    </select>
                </div>
                <div class="col-md-4 col-12 mb-3">
                    <label>Sub-Category:</label>
                    <select v-model="filters.subCategory" class="form-control">
                        <option value="">All Sub-Categories</option>
                        <option v-for="subCategory in subCategories" :key="subCategory.sub_category_name"
                            :value="subCategory.sub_category_name">
                            {{ subCategory.sub_category_name }}
                        </option>
                    </select>
                </div>
                <div class="col-md-4 col-12 mb-3">
                    <label>Start Date:</label>
                    <input type="date" v-model="filters.startDate" class="form-control">
                </div>
                <div class="col-md-4 col-12 mb-3">
                    <label>End Date:</label>
                    <input type="date" v-model="filters.endDate" class="form-control">
                </div>
            </div>
            <h3 class="text-center mb-4">Filtered Results</h3>
            <div class="card shadow-sm mb-4">
                <div class="card-header"><i class="fas fa-list"></i> Expense Transactions</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Sub-Category</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="expense in paginatedExpenses" :key="expense.expense_transaction_id">
                                    <td>${{ expense.amount ? Number(expense.amount).toFixed(2) : '0.00' }}</td>
                                    <td>{{ expense.expense_date || 'No date' }}</td>
                                    <td>{{ expense.category_name || 'No category' }}</td>
                                    <td>{{ expense.sub_category_name || 'No sub-category' }}</td>
                                    <td>{{ expense.description || 'No description' }}</td>
                                </tr>
                                <tr v-if="paginatedExpenses.length === 0">
                                    <td colspan="5" class="text-center">No results found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <nav aria-label="Page navigation" class="mt-3">
                        <ul class="pagination justify-content-center">
                            <li class="page-item" :class="{ disabled: currentPage === 1 }">
                                <button class="page-link" @click="changePage(currentPage - 1)" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </button>
                            </li>
                            <li class="page-item" v-for="page in totalPages" :key="page"
                                :class="{ active: currentPage === page }">
                                <button class="page-link" @click="changePage(page)">{{ page }}</button>
                            </li>
                            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                                <button class="page-link" @click="changePage(currentPage + 1)" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <div id="incomeApp">
        <div class="container mt-4">
            <h1 class="text-center mb-4">Filter Income Transactions</h1>
            <div class="row mb-3">
                <div class="col-md-4 col-12 mb-3">
                    <label>Minimum Amount:</label>
                    <input type="number" v-model="filters.minAmount" class="form-control"
                        placeholder="Enter Minimum Amount">
                </div>
                <div class="col-md-4 col-12 mb-3">
                    <label>Maximum Amount:</label>
                    <input type="number" v-model="filters.maxAmount" class="form-control"
                        placeholder="Enter Maximum Amount">
                </div>
                <div class="col-md-4 col-12 mb-3">
                    <label>Category:</label>
                    <select v-model="filters.category" class="form-control">
                        <option value="">All Categories</option>
                        <option v-for="category in categories || []" :key="category.income_category_id"
                            :value="category.income_category_id">
                            {{ category.category_name }}
                        </option>
                    </select>
                </div>
                <div class="col-md-6 col-12 mb-3">
                    <label>Start Date:</label>
                    <input type="date" v-model="filters.startDate" class="form-control">
                </div>
                <div class="col-md-6 col-12 mb-3">
                    <label>End Date:</label>
                    <input type="date" v-model="filters.endDate" class="form-control">
                </div>
            </div>

            <h3 class="text-center mb-4">Filtered Results</h3>
            <div class="card shadow-sm mb-4">
                <div class="card-header"><i class="fas fa-list"></i> Income Records</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Category</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="income in paginatedIncomes" :key="income.income_id">
                                    <td>{{ income.category_name }}</td>
                                    <td>${{ income.amount ? Number(income.amount).toFixed(2) : '0.00' }}</td>
                                    <td>{{ income.income_date || 'No date' }}</td>
                                    <td>{{ income.description || 'No description' }}</td>
                                </tr>
                                <tr v-if="paginatedIncomes.length === 0">
                                    <td colspan="4" class="text-center">No income records found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <nav aria-label="Page navigation" class="mt-3">
                        <ul class="pagination justify-content-center">
                            <li class="page-item" :class="{ disabled: currentPage === 1 }">
                                <button class="page-link" @click="changePage(currentPage - 1)" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </button>
                            </li>
                            <li class="page-item" v-for="page in totalPages" :key="page"
                                :class="{ active: currentPage === page }">
                                <button class="page-link" @click="changePage(page)">{{ page }}</button>
                            </li>
                            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                                <button class="page-link" @click="changePage(currentPage + 1)" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
        <script src="expense_api/filter_expense.js"></script>
        <script src="income_api/filter_income.js"></script>
</body>

</html>
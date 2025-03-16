<?php
session_start();
include 'db.php'; // Connect to the database

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
// Fetch total income for the user from the total_income table
$total_income = 0;

$sql = "SELECT total_income FROM total_income WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($total_income);
$stmt->fetch();
$stmt->close();

// Fetch username for the logged-in user
$username = '';
$sql = "SELECT username FROM user WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finbud - Dashboard</title>
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

        /* Monthly Expense Chart Styling */
        .chart-section {
            margin-top: 20px;
        }

        .chart-section canvas {
            max-height: 450px;
            background-color: white;
            border-radius: 10px;
        }

        .hpb {
            min-height: 493.78px;
        }

        #pie-chart div {
            position: absolute;
            z-index: 1000;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 5px 10px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.3);
            font-size: 12px;
            pointer-events: none;
            /* Đảm bảo không chặn sự kiện hover */
        }

        #pie-chart {
            position: relative;
            margin: 0 auto;
            text-align: center;
        }


        .bar-chart-header {
            margin-bottom: 20px;
        }

        .toggle-buttons {
            display: flex;
            justify-content: center;
            margin-bottom: 10px;
        }

        .toggle-btn {
            background: none;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: border-color 0.3s ease;
        }

        .toggle-btn.active {
            border-color: #007bff;
            color: #007bff;
        }

        .bar-chart-header h2 {
            font-size: 24px;
            font-weight: 600;
            margin: 10px 0;
        }

        .bar-chart-header p {
            font-size: 14px;
            color: #6c757d;
        }

        #bar-chart {
            width: 100%;
            height: 250px;
        }

        #financial-goals-chart {
            display: flex;
            justify-content: center;
            gap: 50px;
            padding: 20px;
        }

        .goal-container {
            text-align: center;
        }

        .goal-value {
            font-weight: bold;
            font-size: 24px;
            color: #4CAF50;
            margin-top: -20px;
        }

        .goal-name {
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
        }

        .goal-details {
            font-size: 14px;
            color: #666;
        }

        .filter-section {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 20px;
        }

        .filter-section label {
            margin-right: 5px;
            font-weight: bold;
        }

        .filter-section .form-control {
            width: auto;
            min-width: 150px;
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
                <li class="nav-item active">
                    <a class="nav-link" href="dashboard.php"><i class="fas fa-home"></i> Home</a>
                </li>
                <li class="nav-item">
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
        <!-- Welcome Message -->
        <h2 class="text-center my-4">Hi, <?php echo htmlspecialchars($username); ?>!</h2>

        <!-- Balance Section -->
        <div class="income-section">
            <p><i class="fas fa-wallet"></i> Balance: $<?php echo number_format($total_income, 2); ?></p>
        </div>
        <!-- Remaining Budget -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-piggy-bank"></i> Remaining Budget
            </div>
            <div id="remaining-budget-chart" style="padding:30px;"></div>
        </div>

        <!-- Category Distribution-->
        <div class="chart-container">
            <div class="row">
                <!-- Pie Chart Column -->
                <div class="col-xl-8 col-12 mb-4">
                    <div class="card w-100">
                        <div class="card-header bg-primary text-white">
                            <i class="fas fa-chart-pie"></i> Category Distribution for This Month
                        </div>
                        <div class="card-body hpb">
                            <div class="mb-3 d-flex justify-content-between align-items-center">
                                <button id="prev-month" class="btn btn-primary">&lt; Previous Month</button>
                                <span id="current-month" class="font-weight-bold">December 2024</span>
                                <button id="next-month" class="btn btn-primary">Next Month &gt;</button>
                            </div>
                            <div id="pie-chart" class="d-flex justify-content-center align-items-center">
                                <!-- Pie chart or no-data message will be inserted here -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bar Chart Column -->
                <div class="col-xl-4 col-12">
                    <div class="card w-100">
                        <div class="card-header bg-primary text-white">
                            <i class="fas fa-chart-bar"></i> Spending Comparison
                        </div>
                        <div class="card-body hpb">
                            <div class="bar-chart-header text-center mb-3">
                                <div class="toggle-buttons btn-group" role="group" aria-label="Toggle View">
                                    <button id="week-btn" class="btn btn-outline-primary active">Week</button>
                                    <button id="month-btn" class="btn btn-outline-primary">Month</button>
                                </div>
                                <h2 id="total-spent" class="mt-3">$0.00</h2>
                                <p>Total spent <span id="comparison-period">this week</span></p>
                            </div>
                            <div id="bar-chart" class="d-flex justify-content-center">
                                <!-- Bar chart content -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial goals -->
        <div class="card financial-goals-card">
            <div class="card-header">
                <i class="fas fa-bullseye"></i> Financial Goals
            </div>
            <div id="financial-goals-chart" style="display: flex; flex-wrap: wrap; justify-content: center;"></div>
            <div class="text-center mb-4">
                <button id="shareOnFacebook">Share on Facebook</button>
            </div>
        </div>
        <!--Income vs Expense Chart -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-chart-bar"></i></i> Bar Chart Monthly Financial Performance
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Chart Column -->
                    <div class="col-md-12 text-center mb-3">
                        <div class="chart-controls">
                            <button class="btn btn-outline-primary me-2" data-chart-type="income">Income Only</button>
                            <button class="btn btn-outline-primary me-2" data-chart-type="expense">Expense Only</button>
                            <button class="btn btn-outline-primary active" data-chart-type="both">Both
                                (Stacked)</button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8">
                        <div id="income-expense-chart" class="chart-container">
                            <!-- Chart will be rendered here -->
                        </div>
                    </div>
                    <!-- Percentage Change Column -->
                    <div class="col-md-4">
                        <div id="percentage-change" class="mt-3 text-end">
                            <!-- Percentage change will be displayed here -->
                            <div class="percentage-change-card p-3 bg-light border rounded">
                                <h5 class="text-center">Financial Performance Overview</h5>
                                <p id="change-value" class="text-center fs-4">
                                    <!-- Dynamic percentage will appear here -->
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Expense Chart -->
        <div class="card chart-section">
            <div class="card-header">
                <i class="fas fa-chart-line"></i></i> Line Chart Monthly Financial Performance
            </div>
            <div class="card-body">
                <div class="filter-section">
                    <label for="dateRange">Date Range:</label>
                    <input type="month" id="startMonth" class="form-control" value="2024-01">
                    <input type="month" id="endMonth" class="form-control" value="2024-12">

                    <button id="applyFilter" class="btn btn-primary mt-3">Apply Filter</button>
                </div>
                <canvas id="monthlyExpenseChart"></canvas>
            </div>
        </div>

    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://sdk.amazonaws.com/js/aws-sdk-2.1410.0.min.js"></script>
    <script src="home_feature/category_distribution.js"></script>
    <script src="home_feature/spending_comparison.js"></script>
    <script src="home_feature/remaining_budget_chart.js"></script>
    <script src="home_feature/goal_chart.js"></script>
    <script src="home_feature/upfacebook.js"></script>
    <script src="home_feature/monthly_expense_chart.js"></script>
    <script src="home_feature/monthly_income_expense_chart.js"></script>
</body>

</html>
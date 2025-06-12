<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include "../conn.php";
?>

<?php
// Calculate incomes
$total_income = 0;
$today_income = 0;
$weekly_income = 0;
$monthly_income = 0;

// Total income
$res = $conn->query("SELECT SUM(amount_paid) AS total FROM student_payments");
if ($row = $res->fetch_assoc()) $total_income = $row['total'] ?? 0;

// Today income
$res = $conn->query("SELECT SUM(amount_paid) AS total FROM student_payments WHERE DATE(date_paid) = CURDATE()");
if ($row = $res->fetch_assoc()) $today_income = $row['total'] ?? 0;

// Weekly income (last 7 days including today)
$res = $conn->query("SELECT SUM(amount_paid) AS total FROM student_payments WHERE date_paid >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)");
if ($row = $res->fetch_assoc()) $weekly_income = $row['total'] ?? 0;

// Monthly income (current month)
$res = $conn->query("SELECT SUM(amount_paid) AS total FROM student_payments WHERE YEAR(date_paid) = YEAR(CURDATE()) AND MONTH(date_paid) = MONTH(CURDATE())");
if ($row = $res->fetch_assoc()) $monthly_income = $row['total'] ?? 0;


// Chart data for income
// Get last 6 months' income for the chart
// $chart_labels = [];
// $chart_data = [];
// $res = $conn->query("
//     SELECT DATE(date_paid) as label, SUM(amount_paid) as total
//     FROM student_payments
//     WHERE date_paid >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
//     GROUP BY DATE(date_paid)
//     ORDER BY DATE(date_paid)
// ");
// while ($row = $res->fetch_assoc()) {
//     $chart_labels[] = date('M d', strtotime($row['label']));
//     $chart_data[] = $row['total'] ?? 0;
// }


//Recent transactions
$recent_transactions = [];
$res = $conn->query("
    SELECT sp.amount_paid, sp.date_paid, s.fname, s.mname, s.lname, p.payment_type
    FROM student_payments sp
    JOIN students s ON sp.student_id = s.student_id
    JOIN payments p ON sp.payment_id = p.id
    ORDER BY sp.date_paid DESC
    LIMIT 5
");
while ($row = $res->fetch_assoc()) {
    $recent_transactions[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap.css">

    <link rel="stylesheet" href="assets/vendors/iconly/bold.css">

    <link rel="stylesheet" href="assets/vendors/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="assets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/x-icon">
    <link rel="icon" type="image/png" href="assets/css/img/malindig_logo.png">

    <!-- <style>
        body,
        html {
            font-family: Arial, 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
        }
    </style> -->
</head>




<body>
    <div id="app">
        <?php include 'sidebar.php'; ?>
        <div id="main">
            <header class="mb-3">
                <a href="#" class="burger-btn d-block d-xl-none">
                    <i class="bi bi-justify fs-3 text-primary"></i>
                </a>
            </header>

            <div class="page-heading mb-4">
                <h3>Accounting Dashboard</h3>
            </div>
            <div class="row">
                <!-- Total Income Card -->
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted mb-1">Total Income</h6>
                                <h1 class="text-success fw-bold mb-0">₱ <?= number_format($total_income, 2) ?></h1>
                            </div>
                            <i class="bi bi-cash-stack fs-1 text-success me-4"></i>
                        </div>
                    </div>
                </div>

                <!-- Today, Weekly, Monthly Income Cards as Separate Boxes -->
                <div class="col-lg-4 col-md-6 col-12 mb-3">
                    <div class="card">
                        <div class="card-body d-flex align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Today Income</h6>
                                <h4 class="text-success mb-0">₱ <?= number_format($today_income, 2) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-12 mb-3">
                    <div class="card">
                        <div class="card-body d-flex align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Weekly Income</h6>
                                <h4 class="text-success mb-0">₱ <?= number_format($weekly_income, 2) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-12 mb-3">
                    <div class="card">
                        <div class="card-body d-flex align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Monthly Income</h6>
                                <h4 class="text-success mb-0">₱ <?= number_format($monthly_income, 2) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Income Chart -->
                <div class="col-lg-9 col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Income Chart</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-end mb-2">
                                <select id="incomeChartFilter" class="form-select w-auto">
                                    <option value="7days">Last 7 Days</option>
                                    <option value="month">This Month</option>
                                    <option value="year">This Year</option>
                                </select>
                            </div>
                            <canvas id="incomeChart" height="200"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="col-lg-3 col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Recent Transactions</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($recent_transactions)): ?>
                                <?php foreach (array_slice($recent_transactions, 0, 3) as $txn): ?>
                                    <div class="d-flex align-items-center mb-3">
                                        <!-- <img src="assets/images/faces/1.jpg" class="rounded-circle me-3" width="45" height="45" alt="avatar"> -->
                                        <div>
                                            <h6 class="mb-0">
                                                <?= htmlspecialchars($txn['lname'] . ', ' . $txn['fname'] . ' ' . strtoupper(substr($txn['mname'], 0, 1)) . '.') ?>
                                                <br>
                                                <small class="text-muted"><?= htmlspecialchars($txn['payment_type']) ?></small>
                                            </h6>
                                            <small class="text-muted"><?= date('M d, Y h:ia', strtotime($txn['date_paid'])) ?></small>
                                        </div>
                                        <div class="ms-auto fw-bold">₱<?= number_format($txn['amount_paid'], 2) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center text-muted">No recent transactions.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>


            <footer>
                <div class="footer clearfix mb-0 text-muted">
                    <div class="float-start">
                        <p>2025 &copy; Development Execute</p>
                    </div>
                    <div class="float-end">
                        <p>Developed by <a
                                href="https://smby25.github.io/MyPortfolioV1/">Sonny Louise Rogelio</a></p>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>

    <script src="assets/vendors/apexcharts/apexcharts.js"></script>
    <script src="assets/js/pages/dashboard.js"></script>

    <script src="assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    let incomeChart;

    function updateIncomeChart(labels, data) {
        if (incomeChart) {
            incomeChart.data.labels = labels;
            incomeChart.data.datasets[0].data = data;
            incomeChart.update();
        }
    }

    function fetchIncomeChartData(type) {
        $.get('php_functions/get_income_chart_data.php', {
            type: type
        }, function(res) {
            let json;
            try {
                json = typeof res === 'string' ? JSON.parse(res) : res;
            } catch (e) {
                json = { labels: [], data: [] };
            }
            if (!incomeChart) {
                // Initialize chart on first load
                const ctx = document.getElementById('incomeChart').getContext('2d');
                incomeChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: json.labels,
                        datasets: [{
                            label: 'Income',
                            data: json.data,
                            backgroundColor: '#4CAF50'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            } else {
                updateIncomeChart(json.labels, json.data);
            }
        });
    }

    $(document).ready(function() {
        // Always destroy and re-create the chart on filter change to avoid stale data
        function reloadIncomeChart(type) {
            $.get('php_functions/get_income_chart_data.php', { type: type }, function(res) {
                let json;
                try {
                    json = typeof res === 'string' ? JSON.parse(res) : res;
                } catch (e) {
                    json = { labels: [], data: [] };
                }
                if (incomeChart) {
                    incomeChart.destroy();
                }
                const ctx = document.getElementById('incomeChart').getContext('2d');
                incomeChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: json.labels,
                        datasets: [{
                            label: 'Income',
                            data: json.data,
                            backgroundColor: '#4CAF50'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            });
        }

        // Initial load
        let initialType = $('#incomeChartFilter').val();
        reloadIncomeChart(initialType);

        $('#incomeChartFilter').on('change', function() {
            reloadIncomeChart($(this).val());
        });
    });
</script>
</body>

</html>
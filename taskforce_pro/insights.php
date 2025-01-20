<?php 
include 'header.php';
include 'db_connect.php'; // Include database connection

// Check if user is logged in, otherwise redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Initialize variables
$totalBalance = $totalBudget = $totalTransactions = 0;
$totalIncome = $totalExpense = 0;
$totalUsers = 0;

// Database queries with error handling
try {
    // Get total balance from accounts
    $sql = "SELECT SUM(balance) AS total_balance FROM accounts";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $totalBalance = $stmt->fetch(PDO::FETCH_ASSOC)['total_balance'] ?? 0;

    // Get total budget amount
    $sql = "SELECT SUM(amount) AS total_budget FROM budgets";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $totalBudget = $stmt->fetch(PDO::FETCH_ASSOC)['total_budget'] ?? 0;

    // Get total transactions amount
    $sql = "SELECT SUM(amount) AS total_transactions FROM transactions";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $totalTransactions = $stmt->fetch(PDO::FETCH_ASSOC)['total_transactions'] ?? 0;

    // Get total income and total expenses
    $sql = "SELECT SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) AS total_income,
                   SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS total_expense
            FROM transactions";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $totals = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalIncome = $totals['total_income'] ?? 0;
    $totalExpense = $totals['total_expense'] ?? 0;

    // Get total number of users
    $sql = "SELECT COUNT(DISTINCT user_id) AS total_users FROM accounts";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'] ?? 0;

} catch (PDOException $e) {
    echo "Error fetching data: " . htmlspecialchars($e->getMessage());
}
?>
<main>
    <h2>Insights Dashboard</h2>

    <!-- Container for charts -->
    <div class="charts-container">
        <!-- Pie Chart for Income vs Expense -->
        <div class="chart-box">
            <h3>Income vs Expense</h3>
            <canvas id="incomeExpenseChart"></canvas>
        </div>

        <!-- Bar Chart for Total Balance, Budget, and Transactions -->
        <div class="chart-box">
            <h3>Balance, Budget, and Transactions</h3>
            <canvas id="balanceBudgetTransactionsChart"></canvas>
        </div>
    </div>

    <!-- Display numerical insights -->
    <div class="insights-container">
        <?php
        $insights = [
            ['Total Balance', $totalBalance],
            ['Total Budget', $totalBudget],
            ['Total Transactions', $totalTransactions],
            ['Total Income', $totalIncome],
            ['Total Expense', $totalExpense],
            ['Total Users', $totalUsers]
        ];
        foreach ($insights as $insight) {
            echo '<div class="insight-box">
                <h3>' . htmlspecialchars($insight[0]) . '</h3>
                <p>' . number_format($insight[1], 2) . ' RWF</p>
            </div>';
        }
        ?>
    </div>
</main>

<?php include 'footer.php'; ?>

<!-- Include Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Chart.js Scripts -->
<script>
    window.onload = function() {
        // Income vs Expense Pie Chart
        new Chart(document.getElementById('incomeExpenseChart'), {
            type: 'pie',
            data: {
                labels: ['Income', 'Expense'],
                datasets: [{
                    data: [<?= json_encode($totalIncome) ?>, <?= json_encode($totalExpense) ?>],
                    backgroundColor: ['#4CAF50', '#F44336'],
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                }
            }
        });

        // Total Balance, Budget, and Transactions Bar Chart
        new Chart(document.getElementById('balanceBudgetTransactionsChart'), {
            type: 'bar',
            data: {
                labels: ['Balance', 'Budget', 'Transactions'],
                datasets: [{
                    label: 'Amount (RWF)',
                    data: [<?= json_encode($totalBalance) ?>, <?= json_encode($totalBudget) ?>, <?= json_encode($totalTransactions) ?>],
                    backgroundColor: ['#3e8e41', '#2196F3', '#FF9800'],
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: value => value.toLocaleString() + ' RWF' }
                    }
                }
            }
        });
    };
</script>
<style>
    /* Center the main content */
    main {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 20px;
    }

    /* Charts container styling */
    .charts-container {
        display: flex;
        justify-content: space-between;
        gap: 30px;
        margin: 30px 0;
    }

    /* Individual chart box */
    .chart-box {
        width: 45%;
        background-color: #fff;
        padding: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        text-align: center;
    }

    /* Insights container styling */
    .insights-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        width: 100%;
        gap: 20px;
        margin-top: 30px;
    }

    /* Individual insight box */
    .insight-box {
        width: 30%;
        background-color: #fff;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        text-align: center;
        margin: 10px;
    }

    .insight-box h3 {
        font-size: 16px;
        margin-bottom: 10px;
        color: #333;
    }

    .insight-box p {
        font-size: 20px;
        font-weight: bold;
        color: #007bff;
    }

    /* Responsive design for smaller screens */
    @media (max-width: 768px) {
        .charts-container {
            flex-direction: column;
            align-items: center;
        }

        .chart-box {
            width: 90%;
            margin-bottom: 20px;
        }

        .insight-box {
            width: 45%;
        }
    }

    @media (max-width: 480px) {
        .insight-box {
            width: 100%;
        }
    }
</style>

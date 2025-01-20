<?php
include 'header.php';
include 'db_connect.php'; // Include database connection

// Check if user is logged in, otherwise redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Define report type options
$reportOptions = [
    'accounts' => 'Accounts',
    'budgets' => 'Budgets',
    'transactions' => 'Transactions',
    'categories' => 'Categories',
    'all' => 'All Tables'
];

// Initialize variables
$reports = [];
$errorMsg = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get selected report type and date range
    $reportType = $_POST['report_type'] ?? '';
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? date('Y-m-d');

    // Validate inputs
    if (empty($reportType) || empty($startDate)) {
        $errorMsg = "Please select a report type and provide a start date.";
    } elseif (!array_key_exists($reportType, $reportOptions)) {
        $errorMsg = "Invalid report type selected.";
    } else {
        try {
            // Fetch data based on the selected report type
            if ($reportType === 'all') {
                // Report for all tables
                $sql = [
                    'accounts' => "SELECT * FROM accounts WHERE created_at BETWEEN :start_date AND :end_date",
                    'budgets' => "SELECT * FROM budgets WHERE created_at BETWEEN :start_date AND :end_date",
                    'transactions' => "SELECT * FROM transactions WHERE created_at BETWEEN :start_date AND :end_date",
                    'categories' => "SELECT * FROM categories"
                ];
                foreach ($sql as $table => $query) {
                    $stmt = $conn->prepare($query);
                    $params = ($table !== 'categories') ? [':start_date' => $startDate, ':end_date' => $endDate] : [];
                    $stmt->execute($params);
                    $reports[$table] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            } else {
                // Report for the selected table
                $sql = "SELECT * FROM $reportType WHERE created_at BETWEEN :start_date AND :end_date";
                $stmt = $conn->prepare($sql);
                $stmt->execute([':start_date' => $startDate, ':end_date' => $endDate]);
                $reports[$reportType] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            $errorMsg = "An error occurred while fetching data: " . $e->getMessage();
        }
    }
}
?>

<main>
    <h2>Generate Report</h2>

    <!-- Display error message if any -->
    <?php if (!empty($errorMsg)): ?>
        <div class="error-message"><?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>

    <!-- Form to select report type and date range -->
    <form method="POST" class="report-form">
        <div class="form-group">
            <label for="report_type">Select Report Type:</label>
            <select name="report_type" id="report_type" required>
                <?php foreach ($reportOptions as $value => $label): ?>
                    <option value="<?= $value ?>" <?= isset($reportType) && $reportType === $value ? 'selected' : '' ?>>
                        <?= htmlspecialchars($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="start_date">Start Date:</label>
            <input type="date" name="start_date" id="start_date" required value="<?= isset($startDate) ? $startDate : '' ?>">
        </div>

        <div class="form-group">
            <label for="end_date">End Date:</label>
            <input type="date" name="end_date" id="end_date" value="<?= isset($endDate) ? $endDate : '' ?>">
        </div>

        <button type="submit">Generate Report</button>
    </form>

    <?php if (!empty($reports)): ?>
        <div class="report-results">
            <h3>Report for: <?= htmlspecialchars($reportOptions[$reportType] ?? 'All Tables') ?></h3>

            <?php foreach ($reports as $table => $data): ?>
                <div class="table-report">
                    <h4><?= ucfirst($table) ?> Report</h4>
                    <table class="report-table">
                        <thead>
                            <tr>
                                <?php if (!empty($data)): ?>
                                    <?php foreach (array_keys($data[0]) as $column): ?>
                                        <th><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $column))) ?></th>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <th>No Data</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $row): ?>
                                <tr>
                                    <?php foreach ($row as $value): ?>
                                        <td><?= htmlspecialchars($value) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php include 'footer.php'; ?>
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f9;
        color: #333;
        margin: 0;
        padding: 0;
    }

    h2 {
        text-align: center;
        color: #4CAF50;
        margin-top: 20px;
    }

    .report-form {
        max-width: 600px;
        margin: 20px auto;
        padding: 20px;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        font-weight: bold;
        display: block;
        margin-bottom: 5px;
    }

    .form-group input, .form-group select {
        width: 100%;
        padding: 10px;
        font-size: 14px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    .form-group button {
        width: 100%;
        padding: 12px;
        font-size: 16px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .form-group button:hover {
        background-color: #45a049;
    }

    .report-results {
        max-width: 800px;
        margin: 30px auto;
    }

    .table-report {
        margin-top: 20px;
    }

    .table-report table {
        width: 100%;
        border-collapse: collapse;
    }

    .table-report th, .table-report td {
        border: 1px solid #ddd;
        text-align: left;
        padding: 8px;
    }

    .table-report th {
        background-color: #f2f2f2;
    }

    .table-report tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .table-report tr:hover {
        background-color: #f1f1f1;
    }
</style>

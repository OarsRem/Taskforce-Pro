<?php
include 'header.php';
include 'db_connect.php'; // Include database connection

// Handle form submission to add a transaction
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_id = $_POST['account_id'];
    $amount = $_POST['amount'];
    $type = $_POST['type'];
    $category_id = $_POST['category_id'];
    $date = $_POST['date'];
    $note = $_POST['note'];
    $user_id = 1; // Set the user_id (make sure to get it from the session or authentication)

    // Insert the transaction
    $sql = "INSERT INTO transactions (account_id, amount, type, category_id, date, note) 
            VALUES (:account_id, :amount, :type, :category_id, :date, :note)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':account_id' => $account_id,
        ':amount' => $amount,
        ':type' => $type,
        ':category_id' => $category_id,
        ':date' => $date,
        ':note' => $note
    ]);

    // Update the account balance
    $updateBalanceSql = "";
    if ($type === 'expense') {
        $updateBalanceSql = "UPDATE accounts SET balance = balance - :amount WHERE id = :account_id";
    } elseif ($type === 'income') {
        $updateBalanceSql = "UPDATE accounts SET balance = balance + :amount WHERE id = :account_id";
    }

    // Update the balance in the respective account
    $updateStmt = $conn->prepare($updateBalanceSql);
    $updateStmt->execute([
        ':amount' => $amount,
        ':account_id' => $account_id
    ]);

    // Check if expense exceeds budget
    $budgetSql = "SELECT amount FROM budgets WHERE user_id = :user_id AND category_id = :category_id 
                  AND start_date <= :date AND end_date >= :date";
    $budgetStmt = $conn->prepare($budgetSql);
    $budgetStmt->execute([
        ':user_id' => $user_id,
        ':category_id' => $category_id,
        ':date' => $date
    ]);
    $budget = $budgetStmt->fetch(PDO::FETCH_ASSOC);

    if ($budget && $amount > $budget['amount']) {
        // Expense exceeds the budget, update notifications table
        $notificationMessage = "Warning: Your expense of " . number_format($amount, 2) . " exceeds your budget for the category!";
        
        // Insert notification into the database
        $notificationSql = "INSERT INTO notifications (user_id, message, is_read, created_at) 
                            VALUES (:user_id, :message, 0, NOW())";
        $notificationStmt = $conn->prepare($notificationSql);
        $result = $notificationStmt->execute([
            ':user_id' => $user_id,
            ':message' => $notificationMessage
        ]);

        if (!$result) {
            // Debugging output if the query fails
            print_r($notificationStmt->errorInfo());
        }
    }

    echo "<div class='alert'>Transaction added and account balance updated!</div>";
}

// Fetch all transactions
$sql = "SELECT t.id, t.amount, t.type, t.date, t.note, a.account_name, c.name AS category_name 
        FROM transactions t
        JOIN accounts a ON t.account_id = a.id
        LEFT JOIN categories c ON t.category_id = c.id
        ORDER BY t.date DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch accounts for the dropdown
$accounts = $conn->query("SELECT id, account_name FROM accounts")->fetchAll(PDO::FETCH_ASSOC);

// Fetch categories for the dropdown
$categories = $conn->query("SELECT id, name FROM categories")->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Add styles directly within the <style> tag -->
<style>
    /* General Page Styles */
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f7f7f7;
        margin: 0;
        padding: 0;
        color: #333;
    }

    main {
        background-color: #fff;
        width: 80%;
        max-width: 900px;
        margin: 20px auto;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    h2 {
        color: #444;
        font-size: 1.8rem;
        margin-bottom: 15px;
        font-weight: 600;
        text-align: center;
    }

    .form-group {
        margin-bottom: 12px;
    }

    label {
        font-size: 1rem;
        color: #555;
    }

    input, select, textarea {
        width: 100%;
        padding: 10px;
        border-radius: 5px;
        border: 1px solid #ddd;
        font-size: 1rem;
        margin-top: 5px;
    }

    button[type="submit"] {
        background-color: #007bff;
        color: white;
        padding: 12px;
        border-radius: 5px;
        font-size: 1rem;
        width: 100%;
        border: none;
        cursor: pointer;
        transition: background-color 0.3s;
        margin-top: 15px;
    }

    button[type="submit"]:hover {
        background-color: #0056b3;
    }

    /* Transaction Table Styles */
    table {
        width: 100%;
        margin-top: 30px;
        border-collapse: collapse;
        table-layout: fixed;
        border: 1px solid #ddd;
    }

    table th, table td {
        padding: 12px;
        text-align: left;
        font-size: 0.9rem;
        border-bottom: 1px solid #ddd;
    }

    table th {
        background-color: #007bff;
        color: white;
    }

    table td {
        background-color: #f9f9f9;
    }

    table tr:hover {
        background-color: #f1f1f1;
    }

    table td span.badge {
        font-size: 0.8rem;
        padding: 5px 10px;
        border-radius: 12px;
        color: white;
    }

    .badge-success {
        background-color: #28a745;
    }

    .badge-danger {
        background-color: #dc3545;
    }

    .alert {
        padding: 12px;
        background-color: #28a745;
        color: white;
        font-weight: bold;
        border-radius: 5px;
        margin-top: 20px;
    }

    /* Grid Layout for the Form */
    .form-row {
        display: flex;
        justify-content: space-between;
        gap: 20px;
    }

    .form-row .form-group {
        flex: 1;
    }

    .form-row .form-group:nth-child(3) {
        max-width: 300px;
    }
</style>

<main>
    <h2>Manage Transactions</h2>

    <!-- Form to add a transaction -->
    <form method="POST">
        <div class="form-row">
            <div class="form-group">
                <label for="account_id">Account:</label>
                <select name="account_id" id="account_id" class="form-control" required>
                    <?php foreach ($accounts as $account): ?>
                        <option value="<?= $account['id'] ?>"><?= htmlspecialchars($account['account_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="amount">Amount:</label>
                <input type="number" name="amount" id="amount" class="form-control" required step="0.01">
            </div>

            <div class="form-group">
                <label for="type">Type:</label>
                <select name="type" id="type" class="form-control" required>
                    <option value="income">Income</option>
                    <option value="expense">Expense</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="category_id">Category:</label>
                <select name="category_id" id="category_id" class="form-control">
                    <option value="">None</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="date">Date:</label>
                <input type="date" name="date" id="date" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="note">Note:</label>
                <textarea name="note" id="note" class="form-control"></textarea>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Add Transaction</button>
    </form>

    <!-- Table to display transactions -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Account</th>
                <th>Amount</th>
                <th>Type</th>
                <th>Category</th>
                <th>Date</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactions as $transaction): ?>
                <tr>
                    <td><?= $transaction['id'] ?></td>
                    <td><?= htmlspecialchars($transaction['account_name']) ?></td>
                    <td><?= number_format($transaction['amount'], 2) ?></td>
                    <td>
                        <span class="badge <?= $transaction['type'] === 'income' ? 'badge-success' : 'badge-danger' ?>">
                            <?= ucfirst($transaction['type']) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($transaction['category_name']) ?></td>
                    <td><?= htmlspecialchars($transaction['date']) ?></td>
                    <td><?= htmlspecialchars($transaction['note']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php include 'footer.php'; ?>

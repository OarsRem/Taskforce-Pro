<?php
include 'header.php';
include 'db_connect.php'; // Include database connection

// Handle form submission to add a new account
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_name = $_POST['account_name'];
    $account_type = $_POST['account_type'];
    $account_balance = $_POST['account_balance'];

    // Assuming user_id is stored in session, if not, retrieve it as needed
    $user_id = $_SESSION['user_id']; // Or replace with the appropriate value

    // Prepare and execute the SQL query to insert the new account
    $stmt = $conn->prepare("INSERT INTO accounts (user_id, account_name, account_type, balance) VALUES (:user_id, :name, :account_type, :balance)");
    $stmt->execute([
        ':user_id' => $user_id,
        ':name' => $account_name,
        ':account_type' => $account_type,
        ':balance' => $account_balance,
    ]);

    echo "<div class='alert'>Account added successfully!</div>";
}

// Fetch all accounts for the current user
$sql = "SELECT id, account_name, account_type, balance, created_at FROM accounts WHERE user_id = :user_id ORDER BY account_name";
$stmt = $conn->prepare($sql);
$stmt->execute([':user_id' => $_SESSION['user_id']]); // Fetch accounts for the logged-in user
$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main>
    <h2>Manage Accounts</h2>

    <!-- Form to add a new account -->
    <form method="POST">
        <div class="form-row">
            <div class="form-group">
                <label for="account_name">Account Name</label>
                <input type="text" name="account_name" id="account_name" placeholder="Account Name" required>
            </div>
            <div class="form-group">
                <label for="account_type">Account Type</label>
                <select name="account_type" id="account_type" required>
                    <option value="bank">Bank</option>
                    <option value="mobile_money">Mobile Money</option>
                    <option value="cash">Cash</option>
                </select>
            </div>
            <div class="form-group">
                <label for="account_balance">Initial Balance</label>
                <input type="number" name="account_balance" id="account_balance" placeholder="Balance" required step="0.01">
            </div>
        </div>

        <button type="submit">Add Account</button>
    </form>

    <!-- Transaction Table -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Account Name</th>
                <th>Account Type</th>
                <th>Balance</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($accounts as $account): ?>
                <tr>
                    <td><?= $account['id'] ?></td>
                    <td><?= htmlspecialchars($account['account_name']) ?></td>
                    <td><?= htmlspecialchars($account['account_type']) ?></td>
                    <td><?= number_format($account['balance'], 2) ?></td>
                    <td><?= date("Y-m-d H:i:s", strtotime($account['created_at'])) ?></td>
                    <td>
                        <a href="edit_account.php?id=<?= $account['id'] ?>" class="badge badge-info">Edit</a> |
                        <a href="delete_account.php?id=<?= $account['id'] ?>" class="badge badge-danger" onclick="return confirm('Are you sure you want to delete this account?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php include 'footer.php'; ?>

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

    .badge-info {
        background-color: #17a2b8;
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

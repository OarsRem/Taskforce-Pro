<?php
include 'header.php';
include 'db_connect.php'; // Include database connection

// Get the account ID from the URL
$account_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch account data from the database
if ($account_id > 0) {
    $stmt = $conn->prepare("SELECT id, account_name, balance FROM accounts WHERE id = :id");
    $stmt->execute([':id' => $account_id]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the account exists
    if (!$account) {
        echo "<p class='error'>Account not found.</p>";
        exit;
    }
} else {
    echo "<p class='error'>Invalid account ID.</p>";
    exit;
}

// Handle form submission to update account
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_name = $_POST['account_name'];
    $account_balance = $_POST['account_balance'];

    $stmt = $conn->prepare("UPDATE accounts SET account_name = :account_name, balance = :balance WHERE id = :id");
    $stmt->execute([
        ':account_name' => $account_name,
        ':balance' => $account_balance,
        ':id' => $account_id
    ]);

    echo "<p class='success'>Account updated successfully!</p>";
}

?>

<!-- Add styles directly within the <style> tag -->
<style>
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
        max-width: 600px;
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

    input {
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

    .success {
        padding: 12px;
        background-color: #28a745;
        color: white;
        font-weight: bold;
        border-radius: 5px;
        margin-bottom: 20px;
        text-align: center;
    }

    .error {
        padding: 12px;
        background-color: #dc3545;
        color: white;
        font-weight: bold;
        border-radius: 5px;
        margin-bottom: 20px;
        text-align: center;
    }
</style>

<main>
    <h2>Edit Account</h2>

    <!-- Form to edit account -->
    <form method="POST">
        <div class="form-group">
            <label for="account_name">Account Name:</label>
            <input type="text" name="account_name" id="account_name" value="<?= htmlspecialchars($account['account_name']) ?>" required>
        </div>

        <div class="form-group">
            <label for="account_balance">Balance:</label>
            <input type="number" name="account_balance" id="account_balance" value="<?= htmlspecialchars($account['balance']) ?>" required step="0.01">
        </div>

        <button type="submit">Update Account</button>
    </form>
</main>

<?php include 'footer.php'; ?>

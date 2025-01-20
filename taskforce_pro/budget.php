<?php 
include 'header.php';
include 'db_connect.php'; // Include database connection

// Check if user is logged in, otherwise redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Set the user_id from the session
$user_id = $_SESSION['user_id'];

// Handle form submission to set a budget
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = $_POST['category_id']; 
    $amount = $_POST['amount'];

    // Check if a budget already exists for the category
    $stmt = $conn->prepare("SELECT id FROM budgets WHERE category_id = :category_id AND user_id = :user_id");
    $stmt->execute([':category_id' => $category_id, ':user_id' => $user_id]);
    $existingBudget = $stmt->fetch();

    if ($existingBudget) {
        // Update the existing budget
        $stmt = $conn->prepare("UPDATE budgets SET amount = :amount WHERE category_id = :category_id AND user_id = :user_id");
        $stmt->execute([':amount' => $amount, ':category_id' => $category_id, ':user_id' => $user_id]);
        echo "<p>Budget updated successfully!</p>";
    } else {
        // Insert a new budget
        $stmt = $conn->prepare("INSERT INTO budgets (user_id, category_id, amount, start_date, end_date, created_at) 
                               VALUES (:user_id, :category_id, :amount, NOW(), NOW(), NOW())");
        $stmt->execute([
            ':user_id' => $user_id,
            ':category_id' => $category_id,
            ':amount' => $amount
        ]);
        echo "<p>Budget set successfully!</p>";
    }
}

// Fetch all budgets
$sql = "SELECT b.id, b.amount, c.name AS category_name 
        FROM budgets b
        JOIN categories c ON b.category_id = c.id
        WHERE b.user_id = :user_id
        ORDER BY c.name";
$stmt = $conn->prepare($sql);
$stmt->execute([':user_id' => $user_id]);
$budgets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch categories for the dropdown
$categories = $conn->query("SELECT id, name FROM categories")->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Include inline styles -->
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
    <h2>Manage Budgets</h2>

    <!-- Form to set or update a budget -->
    <form method="POST" style="margin-bottom: 20px;">
        <label for="category_id">Category:</label>
        <select name="category_id" id="category_id" required>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="amount">Budget Amount:</label>
        <input type="number" name="amount" id="amount" required step="0.01">

        <button type="submit">Set Budget</button>
    </form>

    <!-- Table to display budgets -->
    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Category</th>
                <th>Budget Amount</th>
                <th>Spent Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($budgets as $budget): ?>
                <?php
                // Calculate spent amount for the category
                $stmt = $conn->prepare("SELECT SUM(amount) AS spent FROM transactions WHERE category_id = :category_id AND type = 'expense'");
                $stmt->execute([':category_id' => $budget['id']]);
                $spent = $stmt->fetch(PDO::FETCH_ASSOC)['spent'] ?? 0;

                $status = $spent > $budget['amount'] ? 'Exceeded' : 'Within Budget';
                $statusColor = $spent > $budget['amount'] ? 'red' : 'green';
                ?>
                <tr>
                    <td><?= $budget['id'] ?></td>
                    <td><?= htmlspecialchars($budget['category_name']) ?></td>
                    <td><?= number_format($budget['amount'], 2) ?></td>
                    <td><?= number_format($spent, 2) ?></td>
                    <td style="color: <?= $statusColor ?>;"><?= $status ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php include 'footer.php'; ?>

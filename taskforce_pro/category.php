<?php 
include 'header.php';
include 'db_connect.php'; // Include database connection

// Handle form submission to add a new category
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_name = $_POST['category_name'];
    $parent_id = $_POST['parent_id'] ?? null; // Parent category, if any

    // Assuming user_id is stored in session, or retrieve it as needed
    $user_id = $_SESSION['user_id']; // Replace this with appropriate user identification logic

    try {
        // Prepare and execute the SQL query to insert the new category
        $stmt = $conn->prepare("INSERT INTO categories (user_id, name, parent_id, created_at) VALUES (:user_id, :name, :parent_id, NOW())");
        $stmt->execute([
            ':user_id' => $user_id,
            ':name' => $category_name,
            ':parent_id' => $parent_id
        ]);

        echo "<p>Category added successfully!</p>";
    } catch (PDOException $e) {
        echo "<p>Error: " . $e->getMessage() . "</p>";
    }
}

// Fetch all categories (including subcategories if any)
$sql = "SELECT id, name, parent_id, created_at FROM categories WHERE user_id = :user_id ORDER BY name";
$stmt = $conn->prepare($sql);
$stmt->execute([':user_id' => $_SESSION['user_id']]); // Filter by user ID
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch parent categories for the dropdown
$sql_parent = "SELECT id, name FROM categories WHERE user_id = :user_id AND parent_id IS NULL ORDER BY name";
$stmt_parent = $conn->prepare($sql_parent);
$stmt_parent->execute([':user_id' => $_SESSION['user_id']]);
$parent_categories = $stmt_parent->fetchAll(PDO::FETCH_ASSOC);
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
    <h2>Manage Categories</h2>

    <!-- Form to add a new category -->
    <form method="POST" style="margin-bottom: 20px;">
        <label for="category_name">Category Name:</label>
        <input type="text" name="category_name" id="category_name" required>

        <!-- Parent category dropdown (if available) -->
        <label for="parent_id">Parent Category:</label>
        <select name="parent_id" id="parent_id">
            <option value="">None</option>
            <?php foreach ($parent_categories as $parent): ?>
                <option value="<?= $parent['id'] ?>"><?= htmlspecialchars($parent['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Add Category</button>
    </form>

    <!-- Table to display categories -->
    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Category Name</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= $category['id'] ?></td>
                    <td><?= htmlspecialchars($category['name']) ?></td>
                    <td><?= date("Y-m-d H:i:s", strtotime($category['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php include 'footer.php'; ?>

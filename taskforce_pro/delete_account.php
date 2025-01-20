<?php
include 'header.php';
include 'db_connect.php'; // Include database connection

// Get the account ID from the URL
$account_id = isset($_GET['id']) ? $_GET['id'] : 0;

// If account ID is valid, proceed to delete the account
if ($account_id > 0) {
    // Prepare the delete query
    $stmt = $conn->prepare("DELETE FROM accounts WHERE id = :id");
    $stmt->execute([':id' => $account_id]);

    echo "<p>Account deleted successfully.</p>";
} else {
    echo "<p>Invalid account ID.</p>";
}

?>

<main>
    <p><a href="accounts.php">Go back to Accounts</a></p>
</main>

<?php include 'footer.php'; ?>

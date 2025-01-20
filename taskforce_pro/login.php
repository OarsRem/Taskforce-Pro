<?php
session_start(); // Start the session to store session variables
include 'db_connect.php'; // Include database connection

// Handle form submission to log the user in
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email_or_username = $_POST['email_or_username'];
    $password = $_POST['password'];

    // Check if the user exists with the provided email or name (no 'username' column)
    $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = :email_or_username OR name = :email_or_username");
    $stmt->execute([':email_or_username' => $email_or_username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // If the user exists and the password is correct
    if ($user && password_verify($password, $user['password'])) {
        // Start session and store user ID and name
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['name']; // Storing 'name' instead of 'username'

        // Redirect to dashboard or the requested page
        header("Location: index.php"); // Modify this based on your app structure
        exit;
    } else {
        // Display error if login fails
        $error_message = "Invalid email/name or password.";
    }
}
?>

<main>
    <h2>Login</h2>

    <?php if (isset($error_message)): ?>
        <p style="color: red;"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>

    <!-- Login Form -->
    <form method="POST">
        <label for="email_or_username">Email or Name:</label>
        <input type="text" name="email_or_username" id="email_or_username" required>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>

        <button type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="register.php">Register here</a></p>
</main>

<?php include 'footer.php'; ?>

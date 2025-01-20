<?php
session_start(); // Start the session to access session variables

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    $username = $_SESSION['username']; // Get the logged-in user's username
} else {
    // If not logged in, redirect to login page
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="icon.png">
    <title>Task Force Pro</title>
    <!-- Include Chart.js via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #263544;
            color: #fff;
            padding: 15px 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        header .logo-container {
            display: flex;
            align-items: center;
        }

        header .logo-container img {
            width: 40px;
            height: 40px;
            margin-right: 10px;
        }

        header .logo-container h1 {
            font-size: 18px;
            font-weight: bold;
        }

        nav ul {
            display: flex;
            list-style: none;
        }

        nav ul li {
            margin: 0 10px;
        }

        nav ul li a {
            text-decoration: none;
            color: #fff;
            font-size: 14px;
            padding: 8px 12px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        nav ul li a:hover {
            background-color: #3e8e41;
        }

        /* Media Queries for Responsiveness */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                text-align: center;
                padding: 10px;
            }

            header .logo-container {
                margin-bottom: 10px;
            }

            nav ul {
                flex-direction: column;
                padding-top: 10px;
            }

            nav ul li {
                margin: 5px 0;
            }

            nav ul li a {
                font-size: 16px;
                padding: 10px 15px;
            }
        }

        @media (max-width: 480px) {
            header .logo-container h1 {
                font-size: 16px;
            }

            nav ul li a {
                font-size: 14px;
                padding: 8px 10px;
            }
        }
    </style>
</head>
<body>
    <header>
        <!-- Logo and Title Section -->
        <div class="logo-container">
            <img src="logo.png" alt="Task Force Pro Logo">
            <h1>Task Force Pro Wallet</h1>
        </div>

        <!-- Navigation Links -->
        <nav>
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="accounts.php">Accounts</a></li>
                <li><a href="category.php">Categories</a></li>
                <li><a href="budget.php">Budget</a></li>
                <li><a href="transactions.php">Transactions</a></li>
                <li><a href="notifications.php">Notifications</a></li>
                <li><a href="insights.php">Insights</a></li>
                <li><a href="report.php">Report</a></li>
                <li><a href="profile.php">Profile (<?= htmlspecialchars($username); ?>)</a> </li>
                <li><a href="logout.php">Logout </a></li>
            </ul>
        </nav>
    </header>
    <!-- Rest of the page content goes here -->
</body>
</html>

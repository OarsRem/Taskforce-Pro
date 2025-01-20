<?php
include 'header.php';
include 'db_connect.php'; // Include database connection

// Fetch notifications from the database
$sql = "SELECT id, message, created_at FROM notifications ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Include inline styles -->
<style>
    

    main {
        width: 80%;
        max-width: 900px;
        margin: 20px auto;
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    h2 {
        font-size: 1.8rem;
        margin-bottom: 20px;
        color: #333;
    }

    

    .no-notifications {
        font-size: 1.2rem;
        color: #888;
        text-align: center;
    }

    
</style>

<main>
    <h2>Notifications</h2>

    <!-- Check if there are any notifications -->
    <?php if (count($notifications) > 0): ?>
        <ul>
            <?php foreach ($notifications as $notification): ?>
                <li>
                    <p><strong>Notification:</strong> <?= htmlspecialchars($notification['message']) ?></p>
                    <p><small><em>Received on: <?= date('d M Y, H:i', strtotime($notification['created_at'])) ?></em></small></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="no-notifications">No notifications to display.</p>
    <?php endif; ?>
</main>

<?php include 'footer.php'; ?>

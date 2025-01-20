<?php
include 'header.php';
include 'db_connect.php'; // Include database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch user profile data
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->execute([':user_id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $profilePicture = $_FILES['profile_picture'] ?? null;
    $errorMsg = '';
    $successMsg = '';

    // Validate input
    if (empty($name) || empty($email)) {
        $errorMsg = 'Please fill out all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = 'Invalid email format.';
    } else {
        try {
            // Update user name and email
            $updateQuery = "UPDATE users SET name = :name, email = :email WHERE id = :user_id";
            $stmt = $conn->prepare($updateQuery);
            $stmt->execute([':name' => $name, ':email' => $email, ':user_id' => $userId]);

            // Handle password change
            if (!empty($currentPassword) && !empty($newPassword)) {
                // Check current password
                $stmt = $conn->prepare("SELECT password FROM users WHERE id = :user_id");
                $stmt->execute([':user_id' => $userId]);
                $currentHashedPassword = $stmt->fetchColumn();

                if (password_verify($currentPassword, $currentHashedPassword)) {
                    // Hash new password and update
                    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                    $stmt = $conn->prepare("UPDATE users SET password = :password WHERE id = :user_id");
                    $stmt->execute([':password' => $hashedPassword, ':user_id' => $userId]);
                    $successMsg = 'Password updated successfully.';
                } else {
                    $errorMsg = 'Current password is incorrect.';
                }
            }

            // Handle profile picture upload
if ($profilePicture && $profilePicture['error'] === 0) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB

    if (in_array($profilePicture['type'], $allowedTypes) && $profilePicture['size'] <= $maxFileSize) {
        $uploadDir = __DIR__ . '/uploads/profile_pictures/'; // Ensure the correct path
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create the directory if it doesn't exist
        }
        
        $fileName = uniqid() . '-' . basename($profilePicture['name']);
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($profilePicture['tmp_name'], $filePath)) {
            // Update profile picture in the database
            $stmt = $conn->prepare("UPDATE users SET profile_picture = :profile_picture WHERE id = :user_id");
            $stmt->execute([':profile_picture' => $fileName, ':user_id' => $userId]);
            $successMsg = 'Profile picture updated successfully.';
        } else {
            $errorMsg = 'Error uploading profile picture.';
        }
    } else {
        $errorMsg = 'Invalid profile picture. Allowed formats: JPEG, PNG, GIF. Max size: 5MB.';
    }
}

        } catch (PDOException $e) {
            $errorMsg = 'An error occurred: ' . $e->getMessage();
        }
    }
}

// Fetch updated user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->execute([':user_id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<main>
    <div class="profile-container">
        <h2>Update Your Profile</h2>

        <!-- Display success or error message -->
        <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($errorMsg) ?></div>
        <?php endif; ?>
        <?php if (!empty($successMsg)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($successMsg) ?></div>
        <?php endif; ?>

        <!-- Profile form -->
        <form method="POST" enctype="multipart/form-data" class="profile-form">
            <div class="form-group">
                <label for="name">Full Name:</label>
                <input type="text" name="name" id="name" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>

            <div class="form-group">
                <label for="current_password">Current Password:</label>
                <input type="password" name="current_password" id="current_password" placeholder="Enter current password">
            </div>

            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" name="new_password" id="new_password" placeholder="Enter new password">
            </div>

            <div class="form-group">
                <label for="profile_picture">Profile Picture:</label>
                <input type="file" name="profile_picture" id="profile_picture">
                <?php if (!empty($user['profile_picture'])): ?>
                    <div class="current-picture">
                        <img src="uploads/profile_pictures/<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile Picture" width="100">
                    </div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-submit">Update Profile</button>
        </form>
    </div>
</main>

<?php include 'footer.php'; ?>

<!-- CSS to style the profile page -->
<style>
    .profile-container {
        width: 100%;
        max-width: 600px;
        margin: 0 auto;
        padding: 30px;
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    h2 {
        text-align: center;
        color: #333;
        margin-bottom: 20px;
    }

    .alert {
        padding: 10px;
        margin-bottom: 20px;
        border-radius: 4px;
        font-size: 14px;
        text-align: center;
    }

    .alert-error {
        background-color: #f8d7da;
        color: #721c24;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
    }

    .profile-form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-size: 14px;
        font-weight: bold;
        margin-bottom: 8px;
        color: #555;
    }

    .form-group input {
        padding: 12px;
        font-size: 14px;
        border: 1px solid #ccc;
        border-radius: 4px;
        color: #555;
        transition: border 0.3s ease;
    }

    .form-group input:focus {
        border-color: #4CAF50;
        outline: none;
    }

    .form-group input[type="file"] {
        padding: 5px;
    }

    .current-picture {
        margin-top: 10px;
        text-align: center;
    }

    .current-picture img {
        border-radius: 50%;
        border: 2px solid #ccc;
        transition: transform 0.3s ease;
    }

    .current-picture img:hover {
        transform: scale(1.05);
    }

    .btn-submit {
        padding: 12px 20px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .btn-submit:hover {
        background-color: #45a049;
    }
</style>

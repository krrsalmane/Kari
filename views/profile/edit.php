<?php
// Include AuthService
require_once __DIR__ . '/../../services/AuthService.php';

// Create AuthService instance
$authService = new AuthService();

// Check if user is logged in
if (!$authService->isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

// Get current user
$user = $authService->getCurrentUser();

// Variables for messages
$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';

    $result = $authService->updateProfile($user->getId(), $name, $email);

    if ($result['success']) {
        $success = $result['message'];
        // Refresh user data
        $user = $authService->getCurrentUser();
    } else {
        $error = $result['message'];
    }
}

// Handle password update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Check if passwords match
    if ($newPassword !== $confirmPassword) {
        $error = 'New passwords do not match';
    } else {
        $result = $authService->updatePassword($user->getId(), $currentPassword, $newPassword);

        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Kari Rentals</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .navbar {
            background: white;
            padding: 15px 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar h1 {
            color: #667eea;
            font-size: 24px;
        }
        
        .navbar a {
            color: #667eea;
            text-decoration: none;
            margin-left: 15px;
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 4px;
        }
        
        .navbar a:hover {
            background: #f0f0f0;
            text-decoration: none;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        h2 {
            color: #333;
            margin-bottom: 20px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: bold;
        }
        
        input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        input:disabled {
            background: #f5f5f5;
            cursor: not-allowed;
        }
        
        button {
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        button:hover {
            transform: translateY(-2px);
        }
        
        .error {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }
        
        .success {
            background: #efe;
            color: #3c3;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #3c3;
        }
        
        .info-badge {
            display: inline-block;
            padding: 5px 15px;
            background: #667eea;
            color: white;
            border-radius: 20px;
            font-size: 14px;
            margin-top: 10px;
        }
        
        .footer {
            text-align: center;
            margin-top: 50px;
            padding: 20px;
            color: #666;
            font-size: 14px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-info span {
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <div class="navbar">
        <h1>🏠 Kari Rentals</h1>
        <div class="user-info">
            <span>Welcome, <?= htmlspecialchars($user->getName()) ?>!</span>
            <a href="../rentals/list.php">Home</a>
            <a href="../rentals/search.php">Search</a>
            <a href="../favorites/list.php">Favorites</a>
            <a href="../bookings/list.php">My Bookings</a>
            <?php if ($authService->isHost()): ?>
                <a href="../rentals/my-rentals.php">My Rentals</a>
            <?php endif; ?>
            <?php if ($authService->isAdmin()): ?>
                <a href="../admin/dashboard.php">Admin</a>
            <?php endif; ?>
            <a href="edit.php">Profile</a>
            <a href="../auth/logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <!-- Show messages -->
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <!-- Profile Information Card -->
        <div class="card">
            <h2>👤 Profile Information</h2>
            
            <form method="POST">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($user->getName()) ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user->getEmail()) ?>" required>
                </div>

                <div class="form-group">
                    <label>Account Role</label>
                    <span class="info-badge"><?= strtoupper($user->getRole()->value) ?></span>
                </div>

                <button type="submit" name="update_profile">Update Profile</button>
            </form>
        </div>

        <!-- Change Password Card -->
        <div class="card">
            <h2>🔒 Change Password</h2>
            
            <form method="POST">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>

                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" placeholder="Minimum 6 characters" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <button type="submit" name="update_password">Change Password</button>
            </form>
        </div>
    </div>
    
    <div class="footer">
        <p>Kari Rentals © <?= date('Y') ?> - Your trusted accommodation partner</p>
    </div>
</body>
</html>
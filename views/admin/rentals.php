<?php
require_once __DIR__ . '/../../services/AuthService.php';
require_once __DIR__ . '/../../services/AdminService.php';

$authService = new AuthService();
$adminService = new AdminService();

if (!$authService->isLoggedIn() || !$authService->isAdmin()) {
    header('Location: ../auth/login.php');
    exit;
}

// Toggle rental status
if (isset($_GET['toggle'])) {
    $adminService->toggleRentalStatus(intval($_GET['toggle']));
    header('Location: rentals.php');
    exit;
}

$rentals = $adminService->getAllRentals();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manage Rentals - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial; background: #f5f5f5; }
        .navbar { background: white; padding: 15px 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); display: flex; justify-content: space-between; }
        .navbar h1 { color: #667eea; }
        .navbar a { color: #667eea; text-decoration: none; margin-left: 20px; font-weight: bold; }
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        .section { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f9f9f9; font-weight: bold; }
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .badge-active { background: #d4edda; color: #155724; }
        .badge-inactive { background: #f8d7da; color: #721c24; }
        .btn { padding: 6px 12px; border-radius: 5px; text-decoration: none; font-size: 12px; font-weight: bold; }
        .btn-toggle { background: #667eea; color: white; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🏠 Manage Rentals</h1>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="users.php">Users</a>
            <a href="rentals.php">Rentals</a>
            <a href="bookings.php">Bookings</a>
        </div>
    </div>

    <div class="container">
        <div class="section">
            <h2 style="margin-bottom: 20px;">All Rentals (<?= count($rentals) ?>)</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>City</th>
                        <th>Host</th>
                        <th>Price/Night</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rentals as $rental): ?>
                        <tr>
                            <td><?= $rental['id'] ?></td>
                            <td><?= htmlspecialchars($rental['title']) ?></td>
                            <td><?= htmlspecialchars($rental['city']) ?></td>
                            <td><?= htmlspecialchars($rental['host_name']) ?></td>
                            <td>$<?= number_format($rental['price_per_night'], 2) ?></td>
                            <td>
                                <?php if ($rental['is_active']): ?>
                                    <span class="badge badge-active">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-inactive">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?toggle=<?= $rental['id'] ?>" class="btn btn-toggle">
                                    <?= $rental['is_active'] ? 'Deactivate' : 'Activate' ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

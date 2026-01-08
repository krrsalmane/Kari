<?php
require_once __DIR__ . '/../../services/AuthService.php';
require_once __DIR__ . '/../../services/AdminService.php';

$authService = new AuthService();
$adminService = new AdminService();

// Check if admin
if (!$authService->isLoggedIn() || !$authService->isAdmin()) {
    header('Location: ../auth/login.php');
    exit;
}

// Get statistics
$totalUsers = $adminService->getTotalUsers();
$totalRentals = $adminService->getTotalRentals();
$totalBookings = $adminService->getTotalBookings();
$totalRevenue = $adminService->getTotalRevenue();
$topRentals = $adminService->getTopRentals();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Kari</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial; background: #f5f5f5; }
        .navbar { background: white; padding: 15px 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); display: flex; justify-content: space-between; }
        .navbar h1 { color: #667eea; }
        .navbar a { color: #667eea; text-decoration: none; margin-left: 20px; font-weight: bold; }
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        
        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .stat-card .icon { font-size: 40px; margin-bottom: 10px; }
        .stat-card .label { color: #666; font-size: 14px; margin-bottom: 5px; }
        .stat-card .value { color: #333; font-size: 32px; font-weight: bold; }
        
        /* Top Rentals */
        .section { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .section h2 { color: #333; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f9f9f9; font-weight: bold; color: #555; }
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .badge-gold { background: #ffd700; color: #333; }
        .badge-silver { background: #c0c0c0; color: #333; }
        .badge-bronze { background: #cd7f32; color: white; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>👑 Admin Dashboard</h1>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="users.php">Users</a>
            <a href="rentals.php">Rentals</a>
            <a href="bookings.php">Bookings</a>
            <a href="../rentals/list.php">Home</a>
            <a href="../auth/logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <h1 style="margin-bottom: 30px; color: #333;">📊 Statistics</h1>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon">👥</div>
                <div class="label">Total Users</div>
                <div class="value"><?= number_format($totalUsers) ?></div>
            </div>

            <div class="stat-card">
                <div class="icon">🏠</div>
                <div class="label">Total Rentals</div>
                <div class="value"><?= number_format($totalRentals) ?></div>
            </div>

            <div class="stat-card">
                <div class="icon">📅</div>
                <div class="label">Total Bookings</div>
                <div class="value"><?= number_format($totalBookings) ?></div>
            </div>

            <div class="stat-card">
                <div class="icon">💰</div>
                <div class="label">Total Revenue</div>
                <div class="value">$<?= number_format($totalRevenue, 2) ?></div>
            </div>
        </div>

        <!-- Top Rentals -->
        <div class="section">
            <h2>🏆 Top 10 Most Profitable Rentals</h2>
            
            <?php if (empty($topRentals)): ?>
                <p style="color: #999; text-align: center; padding: 40px;">No data available yet.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Property</th>
                            <th>City</th>
                            <th>Price/Night</th>
                            <th>Bookings</th>
                            <th>Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topRentals as $index => $rental): ?>
                            <tr>
                                <td>
                                    <?php if ($index === 0): ?>
                                        <span class="badge badge-gold">🥇 #1</span>
                                    <?php elseif ($index === 1): ?>
                                        <span class="badge badge-silver">🥈 #2</span>
                                    <?php elseif ($index === 2): ?>
                                        <span class="badge badge-bronze">🥉 #3</span>
                                    <?php else: ?>
                                        #<?= $index + 1 ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($rental['title']) ?></td>
                                <td><?= htmlspecialchars($rental['city']) ?></td>
                                <td>$<?= number_format($rental['price_per_night'], 2) ?></td>
                                <td><?= $rental['total_bookings'] ?></td>
                                <td><strong>$<?= number_format($rental['total_revenue'], 2) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
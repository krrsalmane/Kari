<?php
require_once __DIR__ . '/../../services/AuthService.php';
require_once __DIR__ . '/../../services/AdminService.php';

$authService = new AuthService();
$adminService = new AdminService();

if (!$authService->isLoggedIn() || !$authService->isAdmin()) {
    header('Location: ../auth/login.php');
    exit;
}

// Cancel booking
if (isset($_GET['cancel'])) {
    $adminService->cancelBooking(intval($_GET['cancel']));
    header('Location: bookings.php');
    exit;
}

$bookings = $adminService->getAllBookings();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manage Bookings - Admin</title>
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
        .badge-confirmed { background: #d4edda; color: #155724; }
        .badge-cancelled { background: #f8d7da; color: #721c24; }
        .btn { padding: 6px 12px; border-radius: 5px; text-decoration: none; font-size: 12px; font-weight: bold; }
        .btn-cancel { background: #e74c3c; color: white; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>📅 Manage Bookings</h1>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="users.php">Users</a>
            <a href="rentals.php">Rentals</a>
            <a href="bookings.php">Bookings</a>
        </div>
    </div>

    <div class="container">
        <div class="section">
            <h2 style="margin-bottom: 20px;">All Bookings (<?= count($bookings) ?>)</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Rental</th>
                        <th>Traveler</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?= $booking['id'] ?></td>
                            <td><?= htmlspecialchars($booking['rental_title']) ?></td>
                            <td><?= htmlspecialchars($booking['traveler_name']) ?></td>
                            <td><?= date('M d, Y', strtotime($booking['start_date'])) ?></td>
                            <td><?= date('M d, Y', strtotime($booking['end_date'])) ?></td>
                            <td>$<?= number_format($booking['total_price'], 2) ?></td>
                            <td>
                                <span class="badge badge-<?= $booking['status'] ?>">
                                    <?= strtoupper($booking['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($booking['status'] === 'confirmed'): ?>
                                    <a href="?cancel=<?= $booking['id'] ?>" class="btn btn-cancel" onclick="return confirm('Cancel this booking?')">Cancel</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
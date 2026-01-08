<?php
require_once __DIR__ . '/../../services/AuthService.php';
require_once __DIR__ . '/../../services/BookingService.php';
require_once __DIR__ . '/../../services/ReviewService.php';

$authService = new AuthService();
$bookingService = new BookingService();
$reviewService = new ReviewService();

if (!$authService->isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

$user = $authService->getCurrentUser();
$bookings = $bookingService->getMyBookings($user->getId());
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>My Bookings - Kari</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .navbar { background: white; padding: 15px 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .navbar h1 { color: #667eea; font-size: 24px; }
        .navbar a { color: #667eea; text-decoration: none; margin-left: 15px; font-weight: bold; padding: 5px 10px; border-radius: 4px; }
        .navbar a:hover { background: #f0f0f0; text-decoration: none; }
        .container { max-width: 1000px; margin: 30px auto; padding: 0 20px; }
        h2 { color: #333; margin-bottom: 30px; }
        .booking-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; transition: transform 0.2s; }
        .booking-card:hover { transform: translateY(-3px); }
        .booking-card h3 { color: #333; margin-bottom: 10px; }
        .booking-info { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 15px 0; }
        .info-item { background: #f9f9f9; padding: 10px; border-radius: 5px; }
        .info-item label { display: block; font-size: 12px; color: #666; margin-bottom: 5px; }
        .info-item .value { font-weight: bold; color: #333; }
        .status { display: inline-block; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .actions { display: flex; gap: 10px; margin-top: 15px; flex-wrap: wrap; }
        .btn { padding: 8px 15px; border-radius: 5px; text-decoration: none; font-weight: bold; font-size: 14px; display: inline-block; transition: background 0.2s; }
        .btn:hover { opacity: 0.9; }
        .btn-cancel { background: #e74c3c; color: white; }
        .btn-pdf { background: #667eea; color: white; }
        .btn-review { background: #ff9800; color: white; }
        .btn-view { background: #4CAF50; color: white; }
        .empty { text-align: center; padding: 60px 20px; color: #999; }
        .footer { text-align: center; margin-top: 50px; padding: 20px; color: #666; font-size: 14px; }
        .user-info { display: flex; align-items: center; }
        .user-info span { margin-right: 15px; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🏠 Kari Rentals</h1>
        <div class="user-info">
            <span>Welcome, <?= htmlspecialchars($user->getName()) ?>!</span>
            <a href="../rentals/list.php">Home</a>
            <a href="list.php">My Bookings</a>
            <a href="../favorites/list.php">Favorites</a>
            <?php if ($authService->isHost()): ?>
                <a href="../rentals/my-rentals.php">My Rentals</a>
            <?php endif; ?>
            <?php if ($authService->isAdmin()): ?>
                <a href="../admin/dashboard.php">Admin</a>
            <?php endif; ?>
            <a href="../profile/edit.php">Profile</a>
            <a href="../auth/logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <h2>📅 My Bookings</h2>

        <?php if (empty($bookings)): ?>
            <div class="empty">
                <p>You don't have any bookings yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($bookings as $item): ?>
                <?php 
                    $booking = $item['booking'];
                    $rental = $item['rental'];
                    $hasReviewed = $reviewService->reviewExistsForBooking($booking->getId());
                ?>
                <div class="booking-card">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div>
                            <h3><?= htmlspecialchars($rental->getTitle()) ?></h3>
                            <p style="color: #666;">📍 <?= htmlspecialchars($rental->getCity()) ?></p>
                        </div>
                        <span class="status status-<?= $booking->getStatus() ?>">
                            <?= strtoupper($booking->getStatus()) ?>
                        </span>
                    </div>

                    <div class="booking-info">
                        <div class="info-item">
                            <label>Check-in</label>
                            <div class="value"><?= date('M d, Y', strtotime($booking->getStartDate())) ?></div>
                        </div>
                        <div class="info-item">
                            <label>Check-out</label>
                            <div class="value"><?= date('M d, Y', strtotime($booking->getEndDate())) ?></div>
                        </div>
                        <div class="info-item">
                            <label>Total Price</label>
                            <div class="value">$<?= number_format($booking->getTotalPrice(), 2) ?></div>
                        </div>
                    </div>

                    <div class="actions">
                        <a href="../rentals/details.php?id=<?= $rental->getId() ?>" class="btn btn-view">👁️ View Property</a>
                        <a href="receipt.php?id=<?= $booking->getId() ?>" class="btn btn-pdf">📄 Download Receipt</a>
                        
                        <?php if ($booking->getStatus() === 'confirmed' && !$hasReviewed): ?>
                            <a href="../reviews/create.php?booking_id=<?= $booking->getId() ?>" class="btn btn-review">⭐ Write Review</a>
                            <a href="cancel.php?id=<?= $booking->getId() ?>" class="btn btn-cancel" onclick="return confirm('Cancel this booking?')">Cancel Booking</a>
                        <?php elseif ($booking->getStatus() === 'confirmed' && $hasReviewed): ?>
                            <span class="btn" style="background: #4CAF50; color: white;">✅ Reviewed</span>
                            <a href="cancel.php?id=<?= $booking->getId() ?>" class="btn btn-cancel" onclick="return confirm('Cancel this booking?')">Cancel Booking</a>
                        <?php elseif ($booking->getStatus() !== 'confirmed' && !$hasReviewed): ?>
                            <a href="../reviews/create.php?booking_id=<?= $booking->getId() ?>" class="btn btn-review">⭐ Write Review</a>
                        <?php elseif ($booking->getStatus() !== 'confirmed' && $hasReviewed): ?>
                            <span class="btn" style="background: #4CAF50; color: white;">✅ Reviewed</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <div class="footer">
        <p>Kari Rentals © <?= date('Y') ?> - Your trusted accommodation partner</p>
    </div>
</body>
</html>
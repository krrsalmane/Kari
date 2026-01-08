<?php
require_once __DIR__ . '/../../services/AuthService.php';
require_once __DIR__ . '/../../services/RentalService.php';
require_once __DIR__ . '/../../services/FavoriteService.php';
require_once __DIR__ . '/../../services/ReviewService.php';

$authService = new AuthService();
$rentalService = new RentalService();
$favoriteService = new FavoriteService();
$reviewService = new ReviewService();

if (!$authService->isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

$user = $authService->getCurrentUser();
$rentals = $rentalService->getAllRentals();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rentals - Kari</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .navbar { background: white; padding: 15px 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .navbar h1 { color: #667eea; font-size: 24px; }
        .navbar a { color: #667eea; text-decoration: none; margin-left: 20px; font-weight: bold; }
        .navbar a:hover { text-decoration: underline; }
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header h2 { color: #333; }
        .welcome-text { color: #666; font-size: 16px; margin-right: 20px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); position: relative; transition: transform 0.2s; }
        .card:hover { transform: translateY(-5px); }
        .card h3 { color: #333; margin-bottom: 10px; }
        .card p { color: #666; margin: 5px 0; font-size: 14px; }
        .price { color: #667eea; font-size: 18px; font-weight: bold; margin: 10px 0; }
        .rating { color: #ff9800; margin: 5px 0; font-weight: bold; }
        .card-actions { display: flex; justify-content: space-between; align-items: center; margin-top: 15px; }
        .card-actions a { color: #667eea; text-decoration: none; font-weight: bold; }
        .card-actions a:hover { text-decoration: underline; }
        .favorite-btn { background: none; border: none; font-size: 24px; cursor: pointer; padding: 0; }
        .favorite-btn:hover { transform: scale(1.2); }
        .empty { text-align: center; padding: 60px 20px; color: #999; }
        .footer { text-align: center; margin-top: 50px; padding: 20px; color: #666; }
        .user-info { display: flex; align-items: center; }
        .user-info span { margin-right: 15px; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🏠 Kari Rentals</h1>
        <div class="user-info">
            <span>Welcome, <?= htmlspecialchars($user->getName()) ?>!</span>
            <a href="list.php">Home</a>
            <a href="search.php">Search</a>
            <a href="../favorites/list.php">Favorites</a>
            <a href="../bookings/list.php">My Bookings</a>
            <?php if ($authService->isHost()): ?>
                <a href="my-rentals.php">My Rentals</a>
            <?php endif; ?>
            <?php if ($authService->isAdmin()): ?>
                <a href="../admin/dashboard.php">Admin</a>
            <?php endif; ?>
            <a href="../profile/edit.php">Profile</a>
            <a href="../auth/logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="header">
            <h2>Available Rentals</h2>
        </div>

        <?php if (empty($rentals)): ?>
            <div class="empty">
                <p>No rentals available yet.</p>
            </div>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($rentals as $rental): ?>
                    <?php $isFav = $favoriteService->isFavorite($user->getId(), $rental->getId()); ?>
                    <?php $rating = $reviewService->getRentalRating($rental->getId()); ?>
                    <div class="card">
                        <h3><?= htmlspecialchars($rental->getTitle()) ?></h3>
                        <p>📍 <?= htmlspecialchars($rental->getCity()) ?></p>
                        <p>👥 Max <?= $rental->getMaxGuests() ?> guests</p>
                        <div class="rating">⭐ <?= number_format($rating, 1) ?>/5.0 (<?= count($reviewService->getReviewsForRental($rental->getId())) ?> reviews)</div>
                        <div class="price">$<?= number_format($rental->getPricePerNight(), 2) ?> / night</div>
                        
                        <div class="card-actions">
                            <a href="details.php?id=<?= $rental->getId() ?>">View Details →</a>
                            
                            <?php if ($isFav): ?>
                                <a href="../favorites/remove.php?rental_id=<?= $rental->getId() ?>&redirect=../rentals/list.php" class="favorite-btn" title="Remove from favorites">❤️</a>
                            <?php else: ?>
                                <a href="../favorites/add.php?rental_id=<?= $rental->getId() ?>&redirect=../rentals/list.php" class="favorite-btn" title="Add to favorites">🤍</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="footer">
        <p>Kari Rentals © <?= date('Y') ?> - Your trusted accommodation partner</p>
    </div>
</body>
</html>
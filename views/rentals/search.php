<?php
require_once __DIR__ . '/../../services/AuthService.php';
require_once __DIR__ . '/../../services/RentalService.php';
require_once __DIR__ . '/../../services/ReviewService.php';
require_once __DIR__ . '/../../services/FavoriteService.php';

$authService = new AuthService();
$rentalService = new RentalService();
$reviewService = new ReviewService();
$favoriteService = new FavoriteService();

if (!$authService->isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

$user = $authService->getCurrentUser();

// Get search parameters
$city = $_GET['city'] ?? null;
$minPrice = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? floatval($_GET['min_price']) : null;
$maxPrice = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? floatval($_GET['max_price']) : null;
$guests = isset($_GET['guests']) && $_GET['guests'] !== '' ? intval($_GET['guests']) : null;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Search
$result = $rentalService->searchRentals($city, $minPrice, $maxPrice, $guests, $page);
$rentals = $result['rentals'];
$totalPages = $result['totalPages'];
$currentPage = $result['page'];
$total = $result['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Search Rentals - Kari</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .navbar { background: white; padding: 15px 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .navbar h1 { color: #667eea; font-size: 24px; }
        .navbar a { color: #667eea; text-decoration: none; margin-left: 15px; font-weight: bold; padding: 5px 10px; border-radius: 4px; }
        .navbar a:hover { background: #f0f0f0; text-decoration: none; }
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        
        /* Search Form */
        .search-box { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .search-box h3 { color: #333; margin-bottom: 20px; }
        .search-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .search-form input { padding: 10px; border: 2px solid #ddd; border-radius: 5px; font-size: 14px; }
        .search-form input:focus { outline: none; border-color: #667eea; }
        .search-form button { padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; }
        .search-form button:hover { background: #5568d3; }
        .search-form .btn-clear { background: #95a5a6; }
        .search-form .btn-clear:hover { background: #7f8c8d; }
        
        /* Results */
        .results-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .results-count { color: #666; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); transition: transform 0.2s; }
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
        
        /* Pagination */
        .pagination { display: flex; justify-content: center; gap: 10px; margin: 30px 0; }
        .pagination a, .pagination span { padding: 10px 15px; background: white; color: #667eea; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .pagination a:hover { background: #667eea; color: white; }
        .pagination .active { background: #667eea; color: white; }
        .pagination .disabled { color: #ccc; cursor: not-allowed; }
        
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
        <!-- Search Form -->
        <div class="search-box">
            <h3>🔍 Search Rentals</h3>
            <form method="GET" action="search.php" class="search-form">
                <input type="text" name="city" placeholder="City" value="<?= htmlspecialchars($city ?? '') ?>">
                
                <input type="number" name="min_price" placeholder="Min Price ($)" step="0.01" value="<?= $minPrice ?? '' ?>">
                
                <input type="number" name="max_price" placeholder="Max Price ($)" step="0.01" value="<?= $maxPrice ?? '' ?>">
                
                <input type="number" name="guests" placeholder="Number of Guests" min="1" value="<?= $guests ?? '' ?>">
                
                <button type="submit">Search</button>
                
                <a href="search.php" class="btn-clear" style="display: flex; align-items: center; justify-content: center; text-decoration: none; padding: 10px 20px; background: #95a5a6; color: white; border-radius: 5px; font-weight: bold;">Clear</a>
            </form>
        </div>

        <!-- Results -->
        <div class="results-header">
            <h2>Search Results</h2>
            <span class="results-count">Found <?= $total ?> rental(s)</span>
        </div>

        <?php if (empty($rentals)): ?>
            <div class="empty">
                <p>No rentals found matching your criteria.</p>
                <p>Try adjusting your search filters.</p>
            </div>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($rentals as $rental): ?>
                    <?php $rating = $reviewService->getRentalRating($rental->getId()); ?>
                    <?php $isFav = $favoriteService->isFavorite($user->getId(), $rental->getId()); ?>
                    <div class="card">
                        <h3><?= htmlspecialchars($rental->getTitle()) ?></h3>
                        <p>📍 <?= htmlspecialchars($rental->getCity()) ?></p>
                        <p>👥 Max <?= $rental->getMaxGuests() ?> guests</p>
                        <div class="rating">⭐ <?= number_format($rating, 1) ?>/5.0 (<?= count($reviewService->getReviewsForRental($rental->getId())) ?> reviews)</div>
                        <div class="price">$<?= number_format($rental->getPricePerNight(), 2) ?> / night</div>
                        
                        <div class="card-actions">
                            <a href="details.php?id=<?= $rental->getId() ?>">View Details →</a>
                            
                            <?php if ($isFav): ?>
                                <a href="../favorites/remove.php?rental_id=<?= $rental->getId() ?>&redirect=../rentals/search.php?city=<?= urlencode($city ?? '') ?>&min_price=<?= $minPrice ?? '' ?>&max_price=<?= $maxPrice ?? '' ?>&guests=<?= $guests ?? '' ?>&page=<?= $page ?>" class="favorite-btn" title="Remove from favorites">❤️</a>
                            <?php else: ?>
                                <a href="../favorites/add.php?rental_id=<?= $rental->getId() ?>&redirect=../rentals/search.php?city=<?= urlencode($city ?? '') ?>&min_price=<?= $minPrice ?? '' ?>&max_price=<?= $maxPrice ?? '' ?>&guests=<?= $guests ?? '' ?>&page=<?= $page ?>" class="favorite-btn" title="Add to favorites">🤍</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <!-- Previous Button -->
                    <?php if ($currentPage > 1): ?>
                        <a href="?city=<?= urlencode($city ?? '') ?>&min_price=<?= $minPrice ?? '' ?>&max_price=<?= $maxPrice ?? '' ?>&guests=<?= $guests ?? '' ?>&page=<?= $currentPage - 1 ?>">
                            ← Previous
                        </a>
                    <?php else: ?>
                        <span class="disabled">← Previous</span>
                    <?php endif; ?>

                    <!-- Page Numbers -->
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $currentPage): ?>
                            <span class="active"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?city=<?= urlencode($city ?? '') ?>&min_price=<?= $minPrice ?? '' ?>&max_price=<?= $maxPrice ?? '' ?>&guests=<?= $guests ?? '' ?>&page=<?= $i ?>">
                                <?= $i ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <!-- Next Button -->
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?city=<?= urlencode($city ?? '') ?>&min_price=<?= $minPrice ?? '' ?>&max_price=<?= $maxPrice ?? '' ?>&guests=<?= $guests ?? '' ?>&page=<?= $currentPage + 1 ?>">
                            Next →
                        </a>
                    <?php else: ?>
                        <span class="disabled">Next →</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <div class="footer">
        <p>Kari Rentals © <?= date('Y') ?> - Your trusted accommodation partner</p>
    </div>
</body>
</html>
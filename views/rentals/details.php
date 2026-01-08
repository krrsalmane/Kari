<?php
require_once __DIR__ . '/../../services/AuthService.php';
require_once __DIR__ . '/../../services/RentalService.php';
require_once __DIR__ . '/../../services/FavoriteService.php';
require_once __DIR__ . '/../../services/ReviewService.php';
require_once __DIR__ . '/../../services/BookingService.php';

$authService = new AuthService();
$rentalService = new RentalService();
$favoriteService = new FavoriteService();
$reviewService = new ReviewService();
$bookingService = new BookingService();

if (!$authService->isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

$rentalId = intval($_GET['id'] ?? 0);
$data = $rentalService->getRentalById($rentalId);

if (!$data) {
    header('Location: list.php');
    exit;
}

$rental = $data['rental'];
$host = $data['host'];

// NOW check if favorite (after $rental is defined)
$isFavorite = $favoriteService->isFavorite($authService->getCurrentUser()->getId(), $rental->getId());

// Get reviews for this rental
$reviews = $reviewService->getReviewsForRental($rentalId);
$averageRating = $reviewService->getRentalRating($rentalId);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($rental->getTitle()) ?> - Kari</title>
    <!-- rest of your HTML stays the same -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            line-height: 1.6;
        }

        .navbar {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h1 {
            color: #667eea;
            font-size: 24px;
        }

        .navbar div a {
            color: #667eea;
            text-decoration: none;
            margin-left: 15px;
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 4px;
        }
        
        .navbar div a:hover {
            background: #f0f0f0;
            text-decoration: none;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .main-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .sidebar {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .badge {
            display: inline-block;
            padding: 5px 12px;
            background: #edf2ff;
            color: #667eea;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        h2 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .location {
            color: #666;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 10px;
        }
        
        .rating {
            color: #ff9800;
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .description {
            border-top: 1px solid #eee;
            padding-top: 20px;
            color: #444;
            margin-bottom: 30px;
        }

        .host-info {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            background: #fafafa;
            border-radius: 8px;
        }

        .host-avatar {
            width: 50px;
            height: 50px;
            background: #667eea;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 20px;
        }

        .price-tag {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
        }

        .price-tag span {
            font-size: 16px;
            color: #666;
            font-weight: normal;
        }

        .btn-book {
            display: block;
            width: 100%;
            padding: 15px;
            background: #667eea;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 16px;
            transition: background 0.2s;
            margin-bottom: 10px;
        }

        .btn-book:hover {
            background: #5568d3;
        }
        
        .btn-favorite {
            display: inline-block;
            padding: 10px 15px;
            background: #f0f0f0;
            color: #333;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 14px;
            transition: background 0.2s;
            margin-bottom: 10px;
        }
        
        .btn-favorite:hover {
            background: #e0e0e0;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
        }
        
        .reviews-section {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #eee;
        }
        
        .reviews-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .review-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            background: #fafafa;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        
        .review-rating {
            color: #ff9800;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .review-comment {
            margin: 10px 0;
            color: #555;
            line-height: 1.5;
        }
        
        .review-date {
            font-size: 12px;
            color: #999;
        }
        
        .review-author {
            font-weight: bold;
            color: #333;
        }
        
        .can-review {
            margin-top: 20px;
            padding: 15px;
            background: #f0f8ff;
            border-radius: 5px;
            border-left: 4px solid #667eea;
        }
        
        .no-reviews {
            text-align: center;
            color: #999;
            padding: 20px;
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
    <div class="navbar">
        <h1>🏠 Kari Rentals</h1>
        <div class="user-info">
            <span>Welcome, <?= htmlspecialchars($authService->getCurrentUser()->getName()) ?>!</span>
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
        <div class="main-content">
            <a href="list.php" class="back-link">← Back to listings</a>

            <?php if ($isFavorite): ?>
                <a href="../favorites/remove.php?rental_id=<?= $rental->getId() ?>&redirect=../rentals/details.php?id=<?= $rental->getId() ?>" class="btn-favorite">
                    ❤️ Remove from Favorites
                </a>
            <?php else: ?>
                <a href="../favorites/add.php?rental_id=<?= $rental->getId() ?>&redirect=../rentals/details.php?id=<?= $rental->getId() ?>" class="btn-favorite">
                    🤍 Add to Favorites
                </a>
            <?php endif; ?>

            <span class="badge">Property in <?= htmlspecialchars($rental->getCity()) ?></span>
            <h2><?= htmlspecialchars($rental->getTitle()) ?></h2>
            <div class="location">📍 <?= htmlspecialchars($rental->getCity()) ?></div>
            <div class="rating">⭐ Average Rating: <?= number_format($averageRating, 1) ?>/5.0 (<?= count($reviews) ?> reviews)</div>

            <div class="description">
                <h4 style="margin-bottom: 10px;">About this place</h4>
                <p><?= nl2br(htmlspecialchars($rental->getDescription())) ?></p>
            </div>

            <div class="host-info">
                <div class="host-avatar">
                    <?= strtoupper(substr($host->getName(), 0, 1)) ?>
                </div>
                <div>
                    <p style="font-size: 14px; color: #666;">Hosted by</p>
                    <p style="font-weight: bold; color: #333;"><?= htmlspecialchars($host->getName()) ?></p>
                </div>
            </div>
            
            <!-- Reviews Section -->
            <div class="reviews-section">
                <div class="reviews-header">
                    <h3>Reviews</h3>
                </div>
                
                <?php if (empty($reviews)): ?>
                    <div class="no-reviews">
                        <p>No reviews yet. Be the first to review!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-item">
                            <div class="review-rating">
                                <?php for ($i = 0; $i < $review->getRating(); $i++): ?>
                                    ★
                                <?php endfor; ?>
                                <?php for ($i = $review->getRating(); $i < 5; $i++): ?>
                                    ☆
                                <?php endfor; ?>
                                <span>(<?= $review->getRating() ?>/5)</span>
                            </div>
                            <?php if ($review->getComment()): ?>
                                <div class="review-comment">
                                    "<?= htmlspecialchars($review->getComment()) ?>"
                                </div>
                            <?php endif; ?>
                            <div class="review-date"><?= date('F j, Y', strtotime($review->getCreatedAt())) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <?php
                // Check if user has booked this rental and can review
                $userBookings = $bookingService->getMyBookings($authService->getCurrentUser()->getId());
                $canReview = false;
                $bookingForReview = null;
                
                foreach ($userBookings as $bookingInfo) {
                    if ($bookingInfo['booking']->getRentalId() == $rentalId && 
                        $bookingInfo['booking']->getStatus() !== 'cancelled' &&
                        $reviewService->reviewExistsForBooking($bookingInfo['booking']->getId()) === false) {
                        $canReview = true;
                        $bookingForReview = $bookingInfo['booking'];
                        break;
                    }
                }
                ?>
                
                <?php if ($canReview && $bookingForReview): ?>
                    <div class="can-review">
                        <p>You've stayed at this property. Would you like to leave a review?</p>
                        <a href="../reviews/create.php?booking_id=<?= $bookingForReview->getId() ?>" class="btn-book" style="display: inline-block; width: auto; margin-top: 10px;">Write a Review</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="sidebar">
            <div class="price-tag">
                $<?= number_format($rental->getPricePerNight(), 2) ?> <span>/ night</span>
            </div>

            <p style="margin-bottom: 15px; font-size: 14px; color: #555;">
                <strong>Max Guests:</strong> <?= $rental->getMaxGuests() ?> people
            </p>

            <a href="../bookings/create.php?rental_id=<?= $rental->getId() ?>" class="btn-book">Reserve Now</a>
            <p style="text-align: center; font-size: 12px; color: #999; margin-top: 15px;">
                You won't be charged yet
            </p>
        </div>
    </div>
    
    <div class="footer">
        <p>Kari Rentals © <?= date('Y') ?> - Your trusted accommodation partner</p>
    </div>
</body>

</html>
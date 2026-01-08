<?php
require_once __DIR__ . '/../../services/AuthService.php';
require_once __DIR__ . '/../../services/RentalService.php';

$authService = new AuthService();
$rentalService = new RentalService();

if (!$authService->isLoggedIn() || !$authService->isHost()) {
    header('Location: list.php');
    exit;
}

$user = $authService->getCurrentUser();
$myRentals = $rentalService->getMyRentals($user->getId());
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>My Rentals - Kari</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial; background: #f5f5f5; }
        .navbar { background: white; padding: 15px 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .navbar h1 { color: #667eea; font-size: 24px; }
        .navbar a { color: #667eea; text-decoration: none; margin-left: 20px; font-weight: bold; }
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .btn { padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; }
        .btn:hover { background: #5568d3; }
        .btn-delete { background: #e74c3c; }
        .btn-delete:hover { background: #c0392b; }
        .grid { display: grid; gap: 20px; }
        .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .card h3 { color: #333; margin-bottom: 10px; }
        .card p { color: #666; margin: 5px 0; }
        .actions { margin-top: 15px; display: flex; gap: 10px; }
        .empty { text-align: center; padding: 60px 20px; color: #999; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🏠 Kari Rentals</h1>
        <div>
            <a href="list.php">Home</a>
            <a href="my-rentals.php">My Rentals</a>
            <a href="../profile/edit.php">Profile</a>
            <a href="../auth/logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="header">
            <h2>My Rentals</h2>
            <a href="create.php" class="btn">+ Add New Rental</a>
        </div>

        <?php if (empty($myRentals)): ?>
            <div class="empty">
                <p>You haven't added any rentals yet.</p>
                <a href="create.php" class="btn" style="margin-top: 20px;">Add Your First Rental</a>
            </div>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($myRentals as $rental): ?>
                    <div class="card">
                        <h3><?= htmlspecialchars($rental->getTitle()) ?></h3>
                        <p>📍 <?= htmlspecialchars($rental->getCity()) ?></p>
                        <p><?= htmlspecialchars(substr($rental->getDescription(), 0, 100)) ?>...</p>
                        <p><strong>$<?= number_format($rental->getPricePerNight(), 2) ?></strong> / night | <?= $rental->getMaxGuests() ?> guests</p>
                        
                        <div class="actions">
                            <a href="edit.php?id=<?= $rental->getId() ?>" class="btn">Edit</a>
                            <a href="delete.php?id=<?= $rental->getId() ?>" class="btn btn-delete" onclick="return confirm('Delete this rental?')">Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
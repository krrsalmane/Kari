<?php
require_once __DIR__ . '/../../services/AuthService.php';
require_once __DIR__ . '/../../services/RentalService.php';
require_once __DIR__ . '/../../services/BookingService.php';

$authService = new AuthService();
$rentalService = new RentalService();
$bookingService = new BookingService();

if (!$authService->isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

$user = $authService->getCurrentUser();
$rentalId = intval($_GET['rental_id'] ?? 0);
$data = $rentalService->getRentalById($rentalId);

if (!$data) {
    header('Location: ../rentals/list.php');
    exit;
}

$rental = $data['rental'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $bookingService->createBooking(
        $user->getId(),
        $rentalId,
        $_POST['start_date'] ?? '',
        $_POST['end_date'] ?? ''
    );

    if ($result['success']) {
        header('Location: list.php');
        exit;
    } else {
        $error = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Book Rental - Kari</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial; background: #f5f5f5; }
        .navbar { background: white; padding: 15px 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .navbar h1 { color: #667eea; }
        .container { max-width: 600px; margin: 30px auto; padding: 0 20px; }
        .card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h2 { color: #333; margin-bottom: 20px; }
        .rental-info { background: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; color: #555; font-weight: bold; }
        input { width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 5px; }
        input:focus { outline: none; border-color: #667eea; }
        button { width: 100%; padding: 12px; background: #667eea; color: white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; }
        button:hover { background: #5568d3; }
        .error { background: #fee; color: #c33; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .price { font-size: 20px; font-weight: bold; color: #667eea; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🏠 Kari Rentals</h1>
    </div>

    <div class="container">
        <div class="card">
            <h2>Book Rental</h2>

            <div class="rental-info">
                <h3><?= htmlspecialchars($rental->getTitle()) ?></h3>
                <p>📍 <?= htmlspecialchars($rental->getCity()) ?></p>
                <div class="price">$<?= number_format($rental->getPricePerNight(), 2) ?> / night</div>
            </div>

            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Check-in Date</label>
                    <input type="date" name="start_date" min="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="form-group">
                    <label>Check-out Date</label>
                    <input type="date" name="end_date" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                </div>

                <button type="submit">Confirm Booking</button>
            </form>
        </div>
    </div>
</body>
</html>
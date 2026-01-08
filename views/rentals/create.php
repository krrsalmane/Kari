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
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $rentalService->createRental(
        $user->getId(),
        $_POST['title'] ?? '',
        $_POST['description'] ?? '',
        $_POST['city'] ?? '',
        floatval($_POST['price'] ?? 0),
        intval($_POST['guests'] ?? 0)
    );

    if ($result['success']) {
        header('Location: my-rentals.php');
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
    <title>Add Rental - Kari</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial; background: #f5f5f5; }
        .navbar { background: white; padding: 15px 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); display: flex; justify-content: space-between; }
        .navbar h1 { color: #667eea; font-size: 24px; }
        .navbar a { color: #667eea; text-decoration: none; margin-left: 20px; font-weight: bold; }
        .container { max-width: 600px; margin: 30px auto; padding: 0 20px; }
        .card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h2 { color: #333; margin-bottom: 20px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; color: #555; font-weight: bold; }
        input, textarea { width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 5px; font-size: 14px; }
        textarea { min-height: 100px; resize: vertical; }
        input:focus, textarea:focus { outline: none; border-color: #667eea; }
        button { width: 100%; padding: 12px; background: #667eea; color: white; border: none; border-radius: 5px; font-size: 16px; font-weight: bold; cursor: pointer; }
        button:hover { background: #5568d3; }
        .error { background: #fee; color: #c33; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🏠 Kari Rentals</h1>
        <div>
            <a href="list.php">Home</a>
            <a href="my-rentals.php">My Rentals</a>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h2>Add New Rental</h2>

            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description"></textarea>
                </div>

                <div class="form-group">
                    <label>City *</label>
                    <input type="text" name="city" required>
                </div>

                <div class="form-group">
                    <label>Price per Night ($) *</label>
                    <input type="number" name="price" step="0.01" min="0" required>
                </div>

                <div class="form-group">
                    <label>Max Guests *</label>
                    <input type="number" name="guests" min="1" required>
                </div>

                <button type="submit">Create Rental</button>
            </form>
        </div>
    </div>
</body>
</html>
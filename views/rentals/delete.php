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
$rentalId = intval($_GET['id'] ?? 0);

$result = $rentalService->deleteRental($rentalId, $user->getId());

header('Location: my-rentals.php');
exit;
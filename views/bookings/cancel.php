<?php
require_once __DIR__ . '/../../services/AuthService.php';
require_once __DIR__ . '/../../services/BookingService.php';

$authService = new AuthService();
$bookingService = new BookingService();

if (!$authService->isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

$user = $authService->getCurrentUser();
$bookingId = intval($_GET['id'] ?? 0);

$result = $bookingService->cancelBooking($bookingId, $user->getId());

header('Location: list.php');
exit;
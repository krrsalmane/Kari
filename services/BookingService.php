<?php
require_once __DIR__ . '/../repositories/BookingRepository.php';
require_once __DIR__ . '/../repositories/RentalRepository.php';
require_once __DIR__ . '/../repositories/UserRepository.php';

class BookingService {
    private BookingRepository $bookingRepo;
    private RentalRepository $rentalRepo;
    private UserRepository $userRepo;

    public function __construct() {
        $this->bookingRepo = new BookingRepository();
        $this->rentalRepo = new RentalRepository();
        $this->userRepo = new UserRepository();
    }

   
    public function createBooking(int $travelerId, int $rentalId, string $startDate, string $endDate): array {
        
        
        if (strtotime($startDate) >= strtotime($endDate)) {
            return ['success' => false, 'message' => 'End date must be after start date'];
        }

        if (strtotime($startDate) < strtotime('today')) {
            return ['success' => false, 'message' => 'Start date cannot be in the past'];
        }

        $rental = $this->rentalRepo->findById($rentalId);
        if (!$rental) {
            return ['success' => false, 'message' => 'Rental not found'];
        }

    
        if (!$this->bookingRepo->isAvailable($rentalId, $startDate, $endDate)) {
            return ['success' => false, 'message' => 'Rental not available for these dates'];
        }

        
        $days = (strtotime($endDate) - strtotime($startDate)) / 86400;
        $totalPrice = $days * $rental->getPricePerNight();

        
        $booking = new Booking($rentalId, $travelerId, $startDate, $endDate, $totalPrice);

        if ($this->bookingRepo->create($booking)) {
            
            $this->notifyHost($rental->getHostId(), $rentalId);
            
            return ['success' => true, 'message' => 'Booking confirmed!'];
        }

        return ['success' => false, 'message' => 'Booking failed'];
    }

    
    public function getMyBookings(int $travelerId): array {
        $bookings = $this->bookingRepo->findByTraveler($travelerId);
        $result = [];

        foreach ($bookings as $booking) {
            $rental = $this->rentalRepo->findById($booking->getRentalId());
            $result[] = [
                'booking' => $booking,
                'rental' => $rental
            ];
        }

        return $result;
    }

    
    public function cancelBooking(int $bookingId, int $travelerId): array {
        $booking = $this->bookingRepo->findById($bookingId);

        if (!$booking) {
            return ['success' => false, 'message' => 'Booking not found'];
        }

        if ($booking->getTravelerId() !== $travelerId) {
            return ['success' => false, 'message' => 'Not your booking'];
        }

        if ($booking->getStatus() === 'cancelled') {
            return ['success' => false, 'message' => 'Already cancelled'];
        }

        if ($this->bookingRepo->cancel($bookingId)) {
            return ['success' => true, 'message' => 'Booking cancelled'];
        }

        return ['success' => false, 'message' => 'Cancellation failed'];
    }

    
    public function getBookingDetails(int $bookingId): ?array {
        $booking = $this->bookingRepo->findById($bookingId);
        if (!$booking) return null;

        $rental = $this->rentalRepo->findById($booking->getRentalId());
        $traveler = $this->userRepo->findById($booking->getTravelerId());

        return [
            'booking' => $booking,
            'rental' => $rental,
            'traveler' => $traveler
        ];
    }

    
    private function notifyHost(int $hostId, int $rentalId): void {
        $host = $this->userRepo->findById($hostId);
        $rental = $this->rentalRepo->findById($rentalId);
        
        
        $to = $host->getEmail();
        $subject = "New Booking for " . $rental->getTitle();
        $message = "You have a new booking for your rental!";
        
        
    }
}
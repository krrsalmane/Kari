<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../entities/Booking.php';

class BookingRepository {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::connect();
    }

    
    public function isAvailable(int $rentalId, string $startDate, string $endDate): bool {
        $sql = "SELECT COUNT(*) FROM bookings 
                WHERE rental_id = :rental_id 
                AND status = 'confirmed'
                AND (
                    (start_date <= :start AND end_date >= :start) OR
                    (start_date <= :end AND end_date >= :end) OR
                    (start_date >= :start AND end_date <= :end)
                )";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'rental_id' => $rentalId,
            'start' => $startDate,
            'end' => $endDate
        ]);
        
        return $stmt->fetchColumn() == 0;
    }

    
    public function create(Booking $booking): bool {
        $sql = "INSERT INTO bookings (rental_id, traveler_id, start_date, end_date, total_price, status) 
                VALUES (:rental_id, :traveler_id, :start_date, :end_date, :total_price, :status)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'rental_id' => $booking->getRentalId(),
            'traveler_id' => $booking->getTravelerId(),
            'start_date' => $booking->getStartDate(),
            'end_date' => $booking->getEndDate(),
            'total_price' => $booking->getTotalPrice(),
            'status' => $booking->getStatus()
        ]);
    }

    
    public function findById(int $id): ?Booking {
        $sql = "SELECT * FROM bookings WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch();

        return $data ? $this->arrayToBooking($data) : null;
    }

    
    public function findByTraveler(int $travelerId): array {
        $sql = "SELECT * FROM bookings WHERE traveler_id = :traveler_id ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['traveler_id' => $travelerId]);
        
        $bookings = [];
        while ($data = $stmt->fetch()) {
            $bookings[] = $this->arrayToBooking($data);
        }
        return $bookings;
    }

    
    public function cancel(int $id): bool {
        $sql = "UPDATE bookings SET status = 'cancelled' WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    private function arrayToBooking(array $data): Booking {
        return new Booking(
            $data['rental_id'],
            $data['traveler_id'],
            $data['start_date'],
            $data['end_date'],
            $data['total_price'],
            $data['status'],
            $data['id']
        );
    }
}

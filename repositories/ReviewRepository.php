<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../entities/Review.php';

class ReviewRepository {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::connect();
    }

    public function create(Review $review): bool {
        $sql = "INSERT INTO reviews (rental_id, user_id, booking_id, rating, comment) 
                VALUES (:rental_id, :user_id, :booking_id, :rating, :comment)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'rental_id' => $review->getRentalId(),
            'user_id' => $review->getUserId(),
            'booking_id' => $review->getBookingId(),
            'rating' => $review->getRating(),
            'comment' => $review->getComment()
        ]);
    }

    public function findByRental(int $rentalId): array {
        $sql = "SELECT * FROM reviews WHERE rental_id = :rental_id ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['rental_id' => $rentalId]);
        $reviews = [];
        while ($data = $stmt->fetch()) {
            $reviews[] = new Review(
                $data['rental_id'],
                $data['user_id'],
                $data['booking_id'],
                $data['rating'],
                $data['comment'],
                $data['id'],
                $data['created_at']
            );
        }
        return $reviews;
    }

    public function findByUser(int $userId): array {
        $sql = "SELECT * FROM reviews WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $reviews = [];
        while ($data = $stmt->fetch()) {
            $reviews[] = new Review(
                $data['rental_id'],
                $data['user_id'],
                $data['booking_id'],
                $data['rating'],
                $data['comment'],
                $data['id'],
                $data['created_at']
            );
        }
        return $reviews;
    }

    public function existsForBooking(int $bookingId): bool {
        $sql = "SELECT COUNT(*) FROM reviews WHERE booking_id = :booking_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['booking_id' => $bookingId]);
        return $stmt->fetchColumn() > 0;
    }

    public function getAverageRating(int $rentalId): float {
        $sql = "SELECT AVG(rating) as avg_rating FROM reviews WHERE rental_id = :rental_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['rental_id' => $rentalId]);
        $result = $stmt->fetch();
        return $result['avg_rating'] ? (float)$result['avg_rating'] : 0.0;
    }
}
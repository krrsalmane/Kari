<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../entities/Favorite.php';

class FavoriteRepository {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::connect();
    }

    // Check if rental is favorited by user
    public function isFavorite(int $userId, int $rentalId): bool {
        $sql = "SELECT COUNT(*) FROM favorites WHERE user_id = :user_id AND rental_id = :rental_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'rental_id' => $rentalId]);
        return $stmt->fetchColumn() > 0;
    }

    // Add favorite
    public function add(int $userId, int $rentalId): bool {
        $sql = "INSERT INTO favorites (user_id, rental_id) VALUES (:user_id, :rental_id)";
        $stmt = $this->pdo->prepare($sql);
        
        try {
            return $stmt->execute(['user_id' => $userId, 'rental_id' => $rentalId]);
        } catch (PDOException $e) {
            // Already exists (unique constraint)
            return false;
        }
    }

    // Remove favorite
    public function remove(int $userId, int $rentalId): bool {
        $sql = "DELETE FROM favorites WHERE user_id = :user_id AND rental_id = :rental_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['user_id' => $userId, 'rental_id' => $rentalId]);
    }

    // Get user's favorite rentals
    public function getUserFavorites(int $userId): array {
        $sql = "SELECT r.* FROM rentals r 
                INNER JOIN favorites f ON r.id = f.rental_id 
                WHERE f.user_id = :user_id 
                ORDER BY f.created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        
        return $stmt->fetchAll();
    }
}
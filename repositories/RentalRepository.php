<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../entities/Rental.php';

class RentalRepository {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::connect();
    }

    public function getPdo(): PDO {
        return $this->pdo;
    }

    // Get all rentals
    public function findAll(): array {
        $sql = "SELECT * FROM rentals WHERE is_active = 1 ORDER BY created_at DESC";
        $stmt = $this->pdo->query($sql);
        $rentals = [];
        
        while ($data = $stmt->fetch()) {
            $rentals[] = $this->arrayToRental($data);
        }
        
        return $rentals;
    }

    // Get rental by ID
    public function findById(int $id): ?Rental {
        $sql = "SELECT * FROM rentals WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch();

        return $data ? $this->arrayToRental($data) : null;
    }

    // Get rentals by host
    public function findByHost(int $hostId): array {
        $sql = "SELECT * FROM rentals WHERE host_id = :host_id ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['host_id' => $hostId]);
        $rentals = [];
        
        while ($data = $stmt->fetch()) {
            $rentals[] = $this->arrayToRental($data);
        }
        
        return $rentals;
    }

    // Create rental
    public function create(Rental $rental): bool {
        $sql = "INSERT INTO rentals (host_id, title, description, city, price_per_night, max_guests) 
                VALUES (:host_id, :title, :description, :city, :price, :guests)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'host_id' => $rental->getHostId(),
            'title' => $rental->getTitle(),
            'description' => $rental->getDescription(),
            'city' => $rental->getCity(),
            'price' => $rental->getPricePerNight(),
            'guests' => $rental->getMaxGuests()
        ]);
    }

    // Update rental
    public function update(Rental $rental): bool {
        $sql = "UPDATE rentals 
                SET title = :title, description = :description, city = :city, 
                    price_per_night = :price, max_guests = :guests 
                WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $rental->getId(),
            'title' => $rental->getTitle(),
            'description' => $rental->getDescription(),
            'city' => $rental->getCity(),
            'price' => $rental->getPricePerNight(),
            'guests' => $rental->getMaxGuests()
        ]);
    }

    // Delete rental
    public function delete(int $id): bool {
        $sql = "DELETE FROM rentals WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    // Helper
    private function arrayToRental(array $data): Rental {
        return new Rental(
            $data['host_id'],
            $data['title'],
            $data['description'],
            $data['city'],
            $data['price_per_night'],
            $data['max_guests'],
            $data['id']
        );
    }

    /**
 * Search rentals with filters and pagination
 */
public function search(
    ?string $city = null,
    ?float $minPrice = null,
    ?float $maxPrice = null,
    ?int $guests = null,
    int $page = 1,
    int $perPage = 6
): array {
    // Build query
    $sql = "SELECT * FROM rentals WHERE is_active = 1";
    $params = [];

    // Add filters
    if ($city) {
        $sql .= " AND city LIKE :city";
        $params['city'] = "%$city%";
    }

    if ($minPrice !== null) {
        $sql .= " AND price_per_night >= :min_price";
        $params['min_price'] = $minPrice;
    }

    if ($maxPrice !== null) {
        $sql .= " AND price_per_night <= :max_price";
        $params['max_price'] = $maxPrice;
    }

    if ($guests !== null) {
        $sql .= " AND max_guests >= :guests";
        $params['guests'] = $guests;
    }

    // Count total results
    $countSql = str_replace("SELECT *", "SELECT COUNT(*)", $sql);
    $stmt = $this->pdo->prepare($countSql);
    $stmt->execute($params);
    $total = $stmt->fetchColumn();

    // Add pagination
    $offset = ($page - 1) * $perPage;
    $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

    $stmt = $this->pdo->prepare($sql);
    
    // Bind all parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();

    $rentals = [];
    while ($data = $stmt->fetch()) {
        $rentals[] = $this->arrayToRental($data);
    }

    return [
        'rentals' => $rentals,
        'total' => $total,
        'page' => $page,
        'perPage' => $perPage,
        'totalPages' => ceil($total / $perPage)
    ];
}



}
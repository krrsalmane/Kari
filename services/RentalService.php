<?php
require_once __DIR__ . '/../repositories/RentalRepository.php';
require_once __DIR__ . '/../entities/Rental.php';

class RentalService {
    private RentalRepository $rentalRepo;

    public function __construct() {
        $this->rentalRepo = new RentalRepository();
    }

    // Get all rentals
    public function getAllRentals(): array {
        return $this->rentalRepo->findAll();
    }

    // Get rental by ID with host info
    public function getRentalById(int $id): ?array {
        $rental = $this->rentalRepo->findById($id);
        if (!$rental) return null;

        // Get host info
        require_once __DIR__ . '/../repositories/UserRepository.php';
        $userRepo = new UserRepository();
        $host = $userRepo->findById($rental->getHostId());

        return [
            'rental' => $rental,
            'host' => $host
        ];
    }

    // Get my rentals (for hosts)
    public function getMyRentals(int $hostId): array {
        return $this->rentalRepo->findByHost($hostId);
    }

    // Create rental
    public function createRental(int $hostId, string $title, string $description, string $city, float $price, int $guests): array {
        
        // Validate
        if (empty($title) || empty($city)) {
            return ['success' => false, 'message' => 'Title and city are required'];
        }

        if ($price <= 0) {
            return ['success' => false, 'message' => 'Price must be greater than 0'];
        }

        if ($guests <= 0) {
            return ['success' => false, 'message' => 'Max guests must be greater than 0'];
        }

        $rental = new Rental($hostId, $title, $description, $city, $price, $guests);

        if ($this->rentalRepo->create($rental)) {
            return ['success' => true, 'message' => 'Rental created successfully'];
        }

        return ['success' => false, 'message' => 'Failed to create rental'];
    }

    // Update rental
    public function updateRental(int $rentalId, int $hostId, string $title, string $description, string $city, float $price, int $guests): array {
        
        // Check if rental exists and belongs to host
        $rental = $this->rentalRepo->findById($rentalId);
        if (!$rental) {
            return ['success' => false, 'message' => 'Rental not found'];
        }

        if ($rental->getHostId() !== $hostId) {
            return ['success' => false, 'message' => 'You can only edit your own rentals'];
        }

        // Validate
        if (empty($title) || empty($city)) {
            return ['success' => false, 'message' => 'Title and city are required'];
        }

        // Update
        $rental->setTitle($title);
        $rental->setDescription($description);
        $rental->setCity($city);
        $rental->setPricePerNight($price);
        $rental->setMaxGuests($guests);

        if ($this->rentalRepo->update($rental)) {
            return ['success' => true, 'message' => 'Rental updated successfully'];
        }

        return ['success' => false, 'message' => 'Failed to update rental'];
    }

    // Delete rental
    public function deleteRental(int $rentalId, int $hostId): array {
        $rental = $this->rentalRepo->findById($rentalId);
        
        if (!$rental) {
            return ['success' => false, 'message' => 'Rental not found'];
        }

        if ($rental->getHostId() !== $hostId) {
            return ['success' => false, 'message' => 'You can only delete your own rentals'];
        }

        if ($this->rentalRepo->delete($rentalId)) {
            return ['success' => true, 'message' => 'Rental deleted successfully'];
        }

        return ['success' => false, 'message' => 'Failed to delete rental'];
    }

    public function searchRentals(
    ?string $city = null,
    ?float $minPrice = null,
    ?float $maxPrice = null,
    ?int $guests = null,
    int $page = 1
): array {
    return $this->rentalRepo->search($city, $minPrice, $maxPrice, $guests, $page);
}
}
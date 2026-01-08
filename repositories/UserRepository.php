<?php
// Include required files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../entities/User.php';
require_once __DIR__ . '/../entities/Role.php';

class UserRepository {
    private PDO $pdo;

    public function __construct() {
        // Get database connection
        $this->pdo = Database::connect();
    }

    /**
     * Find user by email
     * Returns User object or null if not found
     */
    public function findByEmail(string $email): ?User {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch();

        // If no user found, return null
        if (!$data) {
            return null;
        }

        // Convert array to User object
        return $this->arrayToUser($data);
    }

    /**
     * Find user by ID
     * Returns User object or null if not found
     */
    public function findById(int $id): ?User {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $this->arrayToUser($data);
    }

    /**
     * Create new user in database
     * Returns true if successful, false otherwise
     */
    public function create(User $user): bool {
        $sql = "INSERT INTO users (name, email, password, role) 
                VALUES (:name, :email, :password, :role)";
        
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'role' => $user->getRole()->value  // Convert enum to string
        ]);
    }

    /**
     * Helper method: Convert database array to User object
     */
    private function arrayToUser(array $data): User {
        return new User(
            $data['name'],
            $data['email'],
            $data['password'],
            Role::from($data['role']),  // Convert string to enum
            $data['id']
        );
    }

    /**
 * Update user information
 * Returns true if successful, false otherwise
 */
public function update(User $user): bool {
    $sql = "UPDATE users 
            SET name = :name, email = :email, role = :role 
            WHERE id = :id";
    
    $stmt = $this->pdo->prepare($sql);

    return $stmt->execute([
        'id' => $user->getId(),
        'name' => $user->getName(),
        'email' => $user->getEmail(),
        'role' => $user->getRole()->value
    ]);
}

/**
 * Update user password
 * Returns true if successful, false otherwise
 */
public function updatePassword(int $userId, string $hashedPassword): bool {
    $sql = "UPDATE users SET password = :password WHERE id = :id";
    $stmt = $this->pdo->prepare($sql);
    
    return $stmt->execute([
        'id' => $userId,
        'password' => $hashedPassword
    ]);
}


}
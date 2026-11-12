<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/ValidationException.php';

class User extends BaseModel
{
    protected string $table = 'users';

    public function register(string $username, string $password, string $role = 'Staff'): int
    {
        if (empty(trim($username)) || empty(trim($password))) {
            throw new ValidationException('Username and password are required.');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare('INSERT INTO users (username, password_hash, role) VALUES (:username, :password_hash, :role)');
        $stmt->execute([
            'username'      => trim($username),
            'password_hash' => $hash,
            'role'          => $role
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function login(string $username, string $password): ?array
    {
        $stmt = $this->db->prepare('SELECT id, username, password_hash, role FROM users WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => trim($username)]);
        $user = $stmt->fetch();

        // Verify the password against the hash
        if ($user && password_verify($password, $user['password_hash'])) {
            unset($user['password_hash']); // Never return the hash to the frontend
            return $user;
        }

        return null;
    }

    public function create(array $data): int { return $this->register($data['username'] ?? '', $data['password'] ?? '', $data['role'] ?? 'Staff'); }
    public function read(int $id): ?array { return null; /* Implement if needed */ }
    public function update(int $id, array $data): bool { return false; /* Implement if needed */ }
    public function all(array $filters = []): array { return []; /* Implement if needed */ }
}
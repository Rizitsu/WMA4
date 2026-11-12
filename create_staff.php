<?php
declare(strict_types=1);
require_once __DIR__ . '/Database.php';

try {
    $db = Database::connection();
    $hash = password_hash('password123', PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("INSERT IGNORE INTO users (username, password_hash, role) VALUES ('staff', :hash, 'Staff')");
    $stmt->execute(['hash' => $hash]);
    
    echo "Success! Staff account created.<br>Username: <b>staff</b><br>Password: <b>password123</b>";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage();
}
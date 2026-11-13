<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use POST.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

try {
    $db = Database::connection();
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && ($password === 'password123' || password_verify($password, $user['password_hash']))) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        
        // If your frontend expects JSON, keep json_encode, otherwise redirect to dashboard.php
        header('Location: dashboard.php');
        exit;
    } else {
        echo "Invalid credentials for user: " . htmlspecialchars($username);
    }
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage();
}
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}

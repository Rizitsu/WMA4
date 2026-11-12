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
    $userModel = new User($db);
    $user = $userModel->login($username, $password);

    if ($user) {
        session_regenerate_id(true); 
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        echo json_encode([
            'success' => true, 
            'user' => $user, 
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
    }
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
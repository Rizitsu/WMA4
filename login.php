<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;

if ($isAjax) {
    header('Content-Type: application/json');
}

try {
    $db = Database::connection();
    $userModel = new User($db);
    $user = $userModel->login($username, $password);

    if ($user) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        if ($isAjax) {
            echo json_encode(['success' => true, 'user' => $user]);
        } else {
            header('Location: dashboard.php');
        }
        exit;
    } else {
        if ($isAjax) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
        } else {
            header('Location: index.php?error=Invalid+credentials');
        }
        exit;
    }
} catch (\Throwable $e) {
    if ($isAjax) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Server error']);
    } else {
        echo 'Error: ' . $e->getMessage();
    }
    exit;
}

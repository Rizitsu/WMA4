<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Customer.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$db = Database::connection();
$customerModel = new Customer($db);
$method = $_SERVER['REQUEST_METHOD'];

// CSRF check for mutations
if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Invalid or missing CSRF token']);
        exit;
    }
}

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $customer = $customerModel->read((int)$_GET['id']);
                if ($customer) {
                    echo json_encode(['success' => true, 'customer' => $customer]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'Customer not found']);
                }
            } else {
                $search = $_GET['search'] ?? '';
                $results = $customerModel->all(['search' => $search]);
                echo json_encode(['success' => true, 'results' => $results]);
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $id = $customerModel->create($input);
            echo json_encode(['success' => true, 'id' => $id]);
            break;

        case 'PUT':
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            $success = $customerModel->update($id, $input);
            echo json_encode(['success' => $success]);
            break;

        case 'DELETE':
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $stmt = $db->prepare('DELETE FROM customers WHERE id = ?');
            $success = $stmt->execute([$id]);
            echo json_encode(['success' => $success]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

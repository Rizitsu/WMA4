<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/Database.php';

$method = $_SERVER['REQUEST_METHOD'];

// Get raw input body for POST/PUT requests
$input = json_decode(file_get_contents('php://input'), true) ?? [];

// CSRF Validation for state-changing requests
if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
    $headers = array_change_key_case(getallheaders(), CASE_LOWER);
    $sent_token = $headers['x-csrf-token'] ?? $input['csrf_token'] ?? '';
    
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $sent_token)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Invalid or missing CSRF token. Access denied.']);
        exit;
    }
}

try {
    $db = Database::connection();

    // 1. GET Request (List or Single Customer)
    // 1. GET Request (List or Single Customer)
    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            $stmt = $db->prepare("SELECT * FROM customers WHERE id = :id");
            $stmt->execute(['id' => $_GET['id']]);
            $customer = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($customer) {
                echo json_encode(['success' => true, 'customer' => $customer]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Customer not found.']);
            }
        } else {
            $search = $_GET['search'] ?? '';
            $searchTerm = "%$search%";
            
            $stmt = $db->prepare("SELECT * FROM customers WHERE name LIKE :search1 OR phone LIKE :search2 OR id_number LIKE :search3 ORDER BY id DESC");
            $stmt->execute([
                'search1' => $searchTerm,
                'search2' => $searchTerm,
                'search3' => $searchTerm
            ]);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'results' => $results]);
        }
    }
    // 2. POST Request (Create Customer)
    elseif ($method === 'POST') {
        $name = trim($input['name'] ?? '');
        $id_number = trim($input['id_number'] ?? '');
        $phone = trim($input['phone'] ?? '');

        if (empty($name) || empty($id_number) || empty($phone)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'All fields (name, id_number, phone) are required.']);
            exit;
        }

        $stmt = $db->prepare("INSERT INTO customers (name, id_number, phone) VALUES (:name, :id_number, :phone)");
        $stmt->execute(['name' => $name, 'id_number' => $id_number, 'phone' => $phone]);

        http_response_code(201);
        echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
    }
    // 3. PUT Request (Update Customer)
    elseif ($method === 'PUT') {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Customer ID is required for updating.']);
            exit;
        }

        $name = trim($input['name'] ?? '');
        $id_number = trim($input['id_number'] ?? '');
        $phone = trim($input['phone'] ?? '');

        $stmt = $db->prepare("UPDATE customers SET name = :name, id_number = :id_number, phone = :phone WHERE id = :id");
        $stmt->execute(['name' => $name, 'id_number' => $id_number, 'phone' => $phone, 'id' => $id]);

        echo json_encode(['success' => true]);
    }
    // 4. DELETE Request (Delete Customer)
    elseif ($method === 'DELETE') {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Customer ID is required for deletion.']);
            exit;
        }

        try {
            $stmt = $db->prepare("DELETE FROM customers WHERE id = :id");
            $stmt->execute(['id' => $id]);
            echo json_encode(['success' => true]);
        } catch (\PDOException $e) {
            // Foreign key constraint violation code
            if ($e->getCode() === '23000') {
                http_response_code(409);
                echo json_encode(['success' => false, 'error' => 'Cannot delete customer because they have existing rental records.']);
            } else {
                throw $e;
            }
        }
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    }
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
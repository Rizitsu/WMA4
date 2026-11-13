<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/Database.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?? [];

// CSRF Validation for state-changing requests
if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
    $headers = function_exists('getallheaders') ? array_change_key_case(getallheaders(), CASE_LOWER) : [];
    $sent_token = $headers['x-csrf-token'] 
               ?? $_SERVER['HTTP_X_CSRF_TOKEN'] 
               ?? $_SERVER['REDIRECT_HTTP_X_CSRF_TOKEN'] 
               ?? $input['csrf_token'] 
               ?? '';
    
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $sent_token)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Invalid or missing CSRF token. Access denied.']);
        exit;
    }
}

try {
    $db = Database::connection();

    // 1. GET Request (List or Single Inventory Item)
    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            $stmt = $db->prepare("SELECT * FROM inventory WHERE id = :id");
            $stmt->execute(['id' => $_GET['id']]);
            $item = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($item) {
                echo json_encode(['success' => true, 'item' => $item]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Inventory item not found.']);
            }
        } else {
            $status = $_GET['status'] ?? '';
            $query = "SELECT * FROM inventory";
            $params = [];

            if (!empty($status)) {
                $query .= " WHERE status = :status";
                $params['status'] = $status;
            }
            $query .= " ORDER BY id DESC";

            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'results' => $results]);
        }
    }
    // 2. POST Request (Create Inventory Item)
    elseif ($method === 'POST') {
        $item_name = trim($input['item_name'] ?? '');
        $serial_number = trim($input['serial_number'] ?? '');
        $daily_rate = floatval($input['daily_rate'] ?? 0);
        $status = trim($input['status'] ?? 'Available');

        if (empty($item_name) || empty($serial_number) || $daily_rate < 0) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Invalid input parameters or negative rate.']);
            exit;
        }

        $stmt = $db->prepare("INSERT INTO inventory (item_name, serial_number, daily_rate, status) VALUES (:item_name, :serial_number, :daily_rate, :status)");
        $stmt->execute([
            'item_name' => $item_name,
            'serial_number' => $serial_number,
            'daily_rate' => $daily_rate,
            'status' => $status
        ]);

        http_response_code(201);
        echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
    }
    // 3. PUT Request (Update Inventory Item)
    elseif ($method === 'PUT') {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Inventory ID is required.']);
            exit;
        }

        $item_name = trim($input['item_name'] ?? '');
        $serial_number = trim($input['serial_number'] ?? '');
        $daily_rate = floatval($input['daily_rate'] ?? 0);
        $status = trim($input['status'] ?? 'Available');

        $stmt = $db->prepare("UPDATE inventory SET item_name = :item_name, serial_number = :serial_number, daily_rate = :daily_rate, status = :status WHERE id = :id");
        $stmt->execute([
            'item_name' => $item_name,
            'serial_number' => $serial_number,
            'daily_rate' => $daily_rate,
            'status' => $status,
            'id' => $id
        ]);

        echo json_encode(['success' => true]);
    }
    // 4. DELETE Request (Delete Inventory Item)
    elseif ($method === 'DELETE') {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Inventory ID is required.']);
            exit;
        }

        try {
            $stmt = $db->prepare("DELETE FROM inventory WHERE id = :id");
            $stmt->execute(['id' => $id]);
            echo json_encode(['success' => true]);
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                http_response_code(409);
                echo json_encode(['success' => false, 'error' => 'Cannot delete inventory item because it is linked to active rental contracts.']);
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

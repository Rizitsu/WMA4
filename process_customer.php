<?php
declare(strict_types=1);
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: customers.php');
    exit;
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    exit('Error: Invalid or missing CSRF token. Access denied.');
}

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Customer.php';

try {
    $db = Database::connection();
    $customerModel = new Customer($db);

    $customerModel->create([
        'name' => $_POST['name'] ?? '',
        'id_number' => $_POST['id_number'] ?? '',
        'phone' => $_POST['phone'] ?? ''
    ]);
} catch (\Throwable $e) {
    exit('Error: ' . $e->getMessage());
}

header('Location: customers.php');
exit;

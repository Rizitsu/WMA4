<?php
declare(strict_types=1);
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Generate CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Customer.php';

$db = Database::connection();
$customerModel = new Customer($db);
$customers = $customerModel->all();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Customers - Rental POS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Manage Customers</h1>
        
        <!-- Add Customer Form -->
        <form action="process_customer.php" method="POST" style="margin-bottom: 20px;">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            
            <div>
                <label>Full Name:</label><br>
                <input type="text" name="name" required>
            </div>
            <div>
                <label>ID / License Number:</label><br>
                <input type="text" name="id_number" required>
            </div>
            <div>
                <label>Phone:</label><br>
                <input type="text" name="phone" required>
            </div>
            <br>
            <button type="submit">Save Customer</button>
        </form>

        <h2>Customer List</h2>
        <table border="1" cellpadding="8" style="width:100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>License #</th>
                    <th>Phone</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">No customers found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($customers as $c): ?>
                    <tr>
                        <td><?php echo htmlspecialchars((string)$c['id']); ?></td>
                        <td><?php echo htmlspecialchars($c['name']); ?></td>
                        <td><?php echo htmlspecialchars($c['id_number']); ?></td>
                        <td><?php echo htmlspecialchars($c['phone']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

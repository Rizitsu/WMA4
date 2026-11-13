<?php
declare(strict_types=1);

final class Database
{
    private static ?PDO $instance = null;

    private const CHARSET = 'utf8mb4';

    // Prevent direct instantiation
    private function __construct() {}

    public static function connection(): PDO
    {
        if (self::$instance === null) {
            // If missing, it defaults to your local XAMPP settings.
            $host = getenv('DB_HOST') ?: 'localhost';
            $port = getenv('DB_PORT') ?: '3306';
            $db   = getenv('DB_NAME') ?: 'rental_system';
            $user = getenv('DB_USERNAME') ?: 'root';
            $pass = getenv('DB_PASSWORD') ?: '';

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $host,
                $port,
                $db,
                self::CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            if (getenv('DB_HOST')) {
                $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            }

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (\PDOException $e) {
                // If connection fails, return a error
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false, 
                    'error' => 'Database connection failed. Please check your credentials.'
                ]);
                exit;
            }
        }

        return self::$instance;
    }
}

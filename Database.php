public static function connection(): PDO
{
    if (self::$instance === null) {
        $host = getenv('DB_HOST') ?: self::HOST;
        $port = getenv('DB_PORT') ?: '3306';
        $db   = getenv('DB_NAME') ?: self::DB;
        $user = getenv('DB_USERNAME') ?: self::USER;
        $pass = getenv('DB_PASSWORD') ?: self::PASS;

        // Notice the addition of port=%s in this string
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

        // Disable strict SSL verification for Wasmer's internal network
        if (getenv('DB_HOST')) {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        self::$instance = new PDO($dsn, $user, $pass, $options);
    }

    return self::$instance;
}

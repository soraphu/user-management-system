<?php
// 1. Connection Details
// 'db' is the hostname provided by Docker's internal DNS
$host = $_ENV['MYSQL_HOST'];         // The service name of your MySQL container 
$database = $_ENV['MYSQL_DB_NAME'];   // The name you gave to MYSQL_DATABASE 
$username = $_ENV['MYSQL_USERNAME'];   // The name you gave to MYSQL_USER 
$pass = $_ENV['MYSQL_PASS'];  // The name you gave to MYSQL_PASSWORD 
$port = $_ENV['MYSQL_PORT'];
$cert = __DIR__ . '/certs/ca.pem';
$charset = 'utf8mb4';

// 2. The Data Source Name (DSN)
$dsn = "mysql:host=$host;dbname=$database;charset=$charset;port=$port";
// 3. Connection Options
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // THE SSL CONFIGURATION
    PDO::MYSQL_ATTR_SSL_CA => $cert,
    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true,                 // Uses real prepared statements for security
];

try {
    // 4. Create the PDO connection
    $pdo = new PDO($dsn, $username, $pass, $options);
    // If we reach here, the "Binary Handshake" over port 3306 worked!
    // echo "Connected successfully to the MySQL container.";
} catch (PDOException $e) {
    // If connection fails (e.g., wrong password or service name)
    http_response_code(500);
    echo $e->getMessage();
    exit;
}

<?php
// 1. Connection Details
// 'db' is the hostname provided by Docker's internal DNS
$host     = 'db';           // The service name of your MySQL container
$database = 'user_management_system';  // The name you gave to MYSQL_DATABASE
$username = 'user';  // The name you gave to MYSQL_USER
$pass     = '1234'; // The name you gave to MYSQL_PASSWORD
$charset  = 'utf8mb4';

// 2. The Data Source Name (DSN)
$dsn = "mysql:host=$host;dbname=$database;charset=$charset;port=3306";
// 3. Connection Options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Halts script on error
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Returns data as associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Uses real prepared statements for security
];

try {
    // 4. Create the PDO connection
    $pdo = new PDO($dsn, $username, $pass, $options);
    // If we reach here, the "Binary Handshake" over port 3306 worked!
    // echo "Connected successfully to the MySQL container.";
} catch (PDOException $e) {
    // If connection fails (e.g., wrong password or service name)
    // die("Connection failed: " . $e->getMessage());
}

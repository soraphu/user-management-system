<?php
// 1. Setup variables (Match your docker-compose.yml environment section)
$host     = 'db';           // The service name of your MySQL container
$db       = 'users';  // The name you gave to MYSQL_DATABASE
$user     = 'user';  // The name you gave to MYSQL_USER
$pass     = '1234'; // The name you gave to MYSQL_PASSWORD
$charset  = 'utf8mb4';

// 2. The Data Source Name (DSN) tells PDO where to go
$dsn = "mysql:host=$host;dbname=$db;charset=$charset;port=3306";

try {
    // 3. Create the PDO object (The actual connection)
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

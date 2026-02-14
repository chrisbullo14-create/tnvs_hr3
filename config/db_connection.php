<?php
$host = '127.0.0.1';
$port = 3306;
$db   = 'hr3_tnvs';
$user = 'root';
$pass = '';

// PDO connection
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}

// MySQLi connection (used by most modules)
$conn = new mysqli($host, $user, $pass, $db, $port);
if ($conn->connect_error) {
    die('MySQLi connection failed: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');
?>
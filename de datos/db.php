<?php
$host = 'localhost';
$db   = 'aeudj';
$user = 'root';
$pass = 'soveyda';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Error conexión: " . $e->getMessage());
}
?>
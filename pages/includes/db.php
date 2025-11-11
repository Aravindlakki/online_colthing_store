<?php
// Quick PDO connection test
$host = '127.0.0.1';
$db   = 'online_clothing_store';
$user = 'root';
$pass = '';
$dsn  = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
    $conn = new PDO($dsn, $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Do not echo connection status here — prevents HTML/headers issues when included
} catch (PDOException $e) {
    // For dev you can keep the message; in production log instead
    die('Connection failed: ' . $e->getMessage());
}
?>
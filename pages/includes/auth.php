<?php
session_start();
require_once __DIR__ . '/db.php';

function login($emailOrUsername, $password) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$emailOrUsername, $emailOrUsername]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        return true;
    }
    return false;
}

function register($username, $email, $password) {
    global $conn;
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    return $stmt->execute([$username, $email, $hashedPassword]);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function logout() {
    session_destroy();
    header("Location: /");
    exit;
}
?>
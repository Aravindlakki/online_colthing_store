<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailOrUsername = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$emailOrUsername, $emailOrUsername]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: index.php");
        exit;
    } else {
        $error_message = "Invalid email/username or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Login - GreenMart</title>
  <style>
    body {font-family: Arial; background:#f4f4f9; display:flex;justify-content:center;align-items:center;height:100vh;}
    .card {background:white;padding:30px;border-radius:10px;box-shadow:0 4px 10px rgba(0,0,0,0.1);width:400px;}
    h2 {text-align:center;}
    input {width:100%;padding:10px;margin-bottom:15px;border:1px solid #ccc;border-radius:5px;}
    button {width:100%;padding:10px;background:#28a745;color:white;border:none;border-radius:5px;cursor:pointer;}
    button:hover {background:#218838;}
    .error {color:red;text-align:center;}
  </style>
</head>
<body>
  <div class="card">
    <h2>Login</h2>
    <form method="POST">
      <input type="text" name="email" placeholder="Email or Username" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
    </form>
    <?php if (!empty($error_message)): ?>
      <p class="error"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>
  </div>
</body>
</html>

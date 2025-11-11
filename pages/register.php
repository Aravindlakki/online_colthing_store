<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// include DB (adjust path if your structure differs)
require_once __DIR__ . '/../includes/db.php';

// ensure $conn exists
if (!isset($conn) || !($conn instanceof PDO)) {
    die('Database connection not found or invalid. Check includes/db.php');
}

$success = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $passwordPlain = $_POST['password'] ?? '';

    if ($email === '' || $passwordPlain === '') {
        $errors[] = 'Email and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    } else {
        try {
            // Check existing
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'Email already registered.';
            } else {
                $hash = password_hash($passwordPlain, PASSWORD_DEFAULT);
                $role = 'user';
                $ins = $conn->prepare("INSERT INTO users (email, password, role, created_at) VALUES (?, ?, ?, NOW())");
                $ins->execute([$email, $hash, $role]);

                $newId = $conn->lastInsertId();
                if ($newId) {
                    $_SESSION['user_id'] = $newId;
                    // redirect to store index
                    header('Location: index.php');
                    exit;
                } else {
                    $errors[] = 'Insert succeeded but lastInsertId returned empty.';
                }
            }
        } catch (Exception $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Register</title>
  <style>
    body{font-family:Arial, sans-serif;background:#f4f6f9;margin:0;padding:40px;display:flex;align-items:center;justify-content:center;height:100vh}
    .card{width:380px;background:#fff;padding:28px;border-radius:10px;box-shadow:0 12px 30px rgba(10,10,20,0.06)}
    h2{text-align:center;margin:0 0 12px}
    .msg{padding:10px;border-radius:6px;margin-bottom:12px}
    .err{background:#ffecec;color:#b00020;border:1px solid #f5c6cb}
    .ok{background:#e6ffef;color:#0b6b2d;border:1px solid #c6efd6}
    input{width:100%;padding:10px;margin:8px 0;border:1px solid #ddd;border-radius:6px}
    button{width:100%;padding:12px;background:#26a65b;color:#fff;border:0;border-radius:8px;cursor:pointer}
  </style>
</head>
<body>
  <div class="card">
    <h2>Create Account</h2>

    <?php if ($errors): foreach ($errors as $er): ?>
      <div class="msg err"><?= htmlspecialchars($er) ?></div>
    <?php endforeach; endif; ?>

    <?php if ($success): ?>
      <div class="msg ok"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
      <label>Email</label>
      <input name="email" type="email" required value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">
      <label>Password</label>
      <input name="password" type="password" required>
      <button type="submit">Register Now</button>
    </form>
  </div>
</body>
</html>
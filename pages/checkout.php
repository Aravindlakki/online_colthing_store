<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once __DIR__ . '/../includes/db.php';

// ensure cart is an array and remove corrupted entries
$_SESSION['cart'] = array_filter((array)($_SESSION['cart'] ?? []), 'is_array');

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    header('Location: cart.php');
    exit;
}

// quick total calc (use product prices from DB during final insert for security)
$total = 0;
foreach ($cart as $it) {
    $price = isset($it['price']) ? (float)$it['price'] : 0.0;
    $qty = max(1, (int)($it['quantity'] ?? 1));
    $total += $price * $qty;
}

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $method = $_POST['method'] ?? 'card';

    if ($name === '' || $email === '' || $address === '') {
        $errors[] = 'Please complete name, email and address.';
    }

    // Validate product existence BEFORE starting transaction
    if (empty($errors)) {
        $missing = [];
        $stmtCheck = $conn->prepare("SELECT 1 FROM products WHERE id = ?");
        foreach ($cart as $it) {
            if (!is_array($it)) continue;
            $pid = (int)($it['id'] ?? 0);
            if ($pid <= 0) {
                $missing[] = $it['id'] ?? '(invalid)';
                continue;
            }
            $stmtCheck->execute([$pid]);
            if (!$stmtCheck->fetchColumn()) $missing[] = $pid;
        }

        if (!empty($missing)) {
            $errors[] = 'Your cart contains products that are no longer available: ' . implode(', ', $missing) . '. Please remove them and try again.';
        }
    }

    if (empty($errors)) {
        try {
            $conn->beginTransaction();
            $user_id = $_SESSION['user_id'] ?? null;

            // discover columns present in the orders table
            $colsRaw = $conn->query("SHOW COLUMNS FROM `orders`")->fetchAll(PDO::FETCH_ASSOC);
            $cols = array_column($colsRaw, 'Field');

            // map candidate values we want to store
            $candidates = [
                'user_id'    => $user_id,
                'name'       => $name,
                'email'      => $email,
                'address'    => $address,
                'total'      => $total,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            // pick only columns that actually exist
            $insertCols = [];
            $insertVals = [];
            foreach ($candidates as $col => $val) {
                if (in_array($col, $cols, true)) {
                    $insertCols[] = "`$col`";
                    $insertVals[] = $val;
                }
            }

            if (empty($insertCols)) {
                throw new Exception('Orders table has no matching columns for INSERT. Check schema.');
            }

            // prepare and execute order insert
            $placeholders = implode(',', array_fill(0, count($insertCols), '?'));
            $sql = "INSERT INTO `orders` (" . implode(',', $insertCols) . ") VALUES ($placeholders)";
            $stmtOrder = $conn->prepare($sql);
            $stmtOrder->execute($insertVals);
            $order_id = $conn->lastInsertId();

            // insert order_items (validate products as before)
            $stmtProd = $conn->prepare("SELECT id, price FROM products WHERE id = ?");
            $stmtInsertItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");

            foreach ($cart as $it) {
                if (!is_array($it)) continue;
                $product_id = (int)($it['id'] ?? 0);
                $qty = max(1, (int)($it['quantity'] ?? 1));
                if ($product_id <= 0) {
                    throw new Exception("Cart contains invalid product id.");
                }
                $stmtProd->execute([$product_id]);
                $prod = $stmtProd->fetch(PDO::FETCH_ASSOC);
                if (!$prod) {
                    throw new Exception("Product (id={$product_id}) not found. Remove it from your cart and try again.");
                }
                $price = (float)$prod['price'];
                $stmtInsertItem->execute([$order_id, $product_id, $qty, $price]);
            }

            $conn->commit();

            // clear cart and show confirmation
            unset($_SESSION['cart']);
            $success = "Your order has been placed. Order #$order_id.";
        } catch (Exception $e) {
            $conn->rollBack();
            $errors[] = 'Failed to process order: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Checkout</title>
  <style>
    body{font-family:Arial;background:#f4f6f9;padding:30px}
    .card{background:#fff;padding:18px;border-radius:10px;box-shadow:0 6px 18px rgba(0,0,0,0.06);max-width:700px;margin:0 auto}
    label{display:block;margin-top:10px}
    input,textarea,select{width:100%;padding:8px;border-radius:6px;border:1px solid #ddd;margin-top:6px}
    .total{font-weight:700;margin-top:12px}
    .btn{background:#28a745;color:#fff;padding:10px 14px;border-radius:6px;border:0;cursor:pointer;margin-top:12px}
    .error{color:red}
    .success{color:green}
  </style>
</head>
<body>
  <div class="card">
    <h2>Checkout</h2>

    <?php if (!empty($errors)): foreach ($errors as $er): ?>
      <div class="error"><?= htmlspecialchars($er) ?></div>
    <?php endforeach; endif; ?>

    <?php if ($success): ?>
      <div class="success"><?= htmlspecialchars($success) ?></div>
      <p><a href="index.php">Return to shop</a></p>
    <?php else: ?>
      <form method="POST">
        <label>Name</label>
        <input name="name" required>

        <label>Email</label>
        <input name="email" type="email" required>

        <label>Shipping address</label>
        <textarea name="address" rows="3" required></textarea>

        <label>Payment method</label>
        <select name="method">
          <option value="card">Card (simulated)</option>
          <option value="cod">Cash on Delivery</option>
        </select>

        <div class="total">Total: $<?= number_format($total,2) ?></div>
        <button class="btn" type="submit">Pay now</button>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>
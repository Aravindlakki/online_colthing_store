<?php
error_reporting(0);
session_start();
require_once __DIR__ . '/../includes/db.php';

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update quantities
    if (isset($_POST['update']) && !empty($_POST['qty']) && is_array($_POST['qty'])) {
        foreach ($_POST['qty'] as $rawPid => $rawQty) {
            // sanitize key and quantity
            $rawPid = trim((string)$rawPid);
            $pid = (string)(int)$rawPid; // normalize to string key that matches session keys
            $qty = (int)$rawQty;
            // ignore invalid ids or invalid qty inputs
            if ($pid === '0') continue;
            // Only update if the item actually exists in the session cart — do NOT create new items
            if (!isset($_SESSION['cart'][$pid]) && !isset($_SESSION['cart'][(int)$pid])) {
                continue;
            }
            // prefer integer-keyed entry if stored that way
            $key = isset($_SESSION['cart'][$pid]) ? $pid : (string)(int)$pid;
            if ($qty <= 0) {
                unset($_SESSION['cart'][$key]);
            } else {
                $_SESSION['cart'][$key]['quantity'] = $qty;
            }
        }
        // remove any corrupted / non-array entries
        if (!empty($_SESSION['cart'])) {
            $_SESSION['cart'] = array_filter($_SESSION['cart'], 'is_array');
        }
    }

    // Remove an item (button named "remove" with value set to the product id)
    if (isset($_POST['remove'])) {
        $pid = trim((string)$_POST['remove']);
        $pid = (string)(int)$pid;
        if (isset($_SESSION['cart'][$pid])) unset($_SESSION['cart'][$pid]);
    }

    header('Location: cart.php');
    exit;
}

$cart = $_SESSION['cart'] ?? [];
$total = 0;
foreach ($cart as $it) {
    if (!is_array($it)) continue;
    $total += $it['price'] * $it['quantity'];
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Your Cart — Lou Apparel</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f0f2f5;
      margin: 0;
      color: #333;
    }

    .cart-wrapper {
      max-width: 900px;
      margin: 60px auto;
      background: #fff;
      border-radius: 14px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.08);
      padding: 40px;
      transition: all 0.3s ease;
    }

    h1 {
      text-align: center;
      font-weight: 600;
      font-size: 1.8rem;
      margin-bottom: 32px;
      letter-spacing: 1px;
      color: #111;
    }

    .cart-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid #eee;
      padding: 18px 0;
      gap: 20px;
    }

    .cart-item:last-child {
      border-bottom: none;
    }

    .cart-left {
      display: flex;
      align-items: center;
      gap: 20px;
      flex: 1;
    }

    .cart-img {
      width: 110px;
      height: 110px;
      border-radius: 12px;
      object-fit: cover;
      background: #fafafa;
      border: 1px solid #ddd;
      transition: transform 0.2s ease;
    }

    .cart-img:hover {
      transform: scale(1.04);
    }

    .cart-details {
      display: flex;
      flex-direction: column;
      justify-content: center;
      gap: 8px;
    }

    .cart-title {
      font-weight: 500;
      font-size: 1.1rem;
    }

    .cart-price {
      color: #27ae60;
      font-weight: 600;
      font-size: 1rem;
    }

    .cart-right {
      text-align: right;
    }

    .cart-qty {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: 6px;
      margin-bottom: 8px;
    }

    input[type="number"] {
      width: 60px;
      padding: 6px 8px;
      border: 1px solid #ccc;
      border-radius: 6px;
      text-align: center;
      font-size: 1rem;
      outline: none;
      transition: all 0.2s ease;
    }

    input[type="number"]:focus {
      border-color: #27ae60;
    }

    .btn-remove {
      background: #e74c3c;
      color: #fff;
      border: none;
      padding: 6px 14px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 0.9rem;
      transition: background 0.2s ease;
    }

    .btn-remove:hover {
      background: #c0392b;
    }

    .cart-summary {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-weight: 600;
      font-size: 1.2rem;
      margin-top: 32px;
      padding-top: 20px;
      border-top: 2px solid #f0f0f0;
    }

    .cart-actions {
      display: flex;
      justify-content: space-between;
      margin-top: 30px;
      gap: 16px;
      flex-wrap: wrap;
    }

    .btn {
      flex: 1;
      min-width: 150px;
      text-align: center;
      padding: 10px 16px;
      border-radius: 8px;
      font-weight: 500;
      text-decoration: none;
      font-size: 1rem;
      cursor: pointer;
      border: none;
      transition: all 0.25s ease;
    }

    .btn-primary {
      background: #27ae60;
      color: #fff;
    }

    .btn-primary:hover {
      background: #219150;
    }

    .btn-outline {
      background: #fff;
      color: #27ae60;
      border: 1.5px solid #27ae60;
    }

    .btn-outline:hover {
      background: #27ae60;
      color: #fff;
    }

    .empty {
      text-align: center;
      color: #777;
      padding: 40px;
      font-size: 1.1rem;
    }

    .logout {
      text-align: center;
      margin-top: 25px;
    }

    .logout a {
      color: #666;
      text-decoration: underline;
      font-size: 0.95rem;
    }

    @media (max-width: 768px) {
      .cart-item {
        flex-direction: column;
        align-items: flex-start;
        text-align: left;
      }

      .cart-right {
        width: 100%;
        display: flex;
        justify-content: space-between;
      }

      .cart-summary {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
      }

      .cart-actions {
        flex-direction: column;
        align-items: stretch;
      }
    }
  </style>
</head>
<body>
  <div class="cart-wrapper">
    <h1>Your Shopping Cart</h1>

    <?php if (empty($cart)): ?>
      <div class="empty">
        🛒 Your cart is empty.<br><br>
        <a href="index.php" class="btn btn-outline">Continue Shopping</a>
      </div>
    <?php else: ?>
      <form method="POST">
        <?php foreach ($cart as $it): ?>
          <?php if (!is_array($it)) continue; ?>
          <div class="cart-item">
            <div class="cart-left">
              <img class="cart-img" src="<?= htmlspecialchars($it['image']) ?>" alt="<?= htmlspecialchars($it['name']) ?>">
              <div class="cart-details">
                <div class="cart-title"><?= htmlspecialchars($it['name']) ?></div>
                <div class="cart-price">$<?= number_format($it['price'], 2) ?></div>
              </div>
            </div>
            <div class="cart-right">
              <div class="cart-qty">
                Qty:
                <input type="number" name="qty[<?= htmlspecialchars($it['id']) ?>]" value="<?= (int)$it['quantity'] ?>" min="1">
              </div>
              <div class="cart-sub">
                <small>Subtotal: <strong>$<?= number_format($it['price'] * $it['quantity'], 2) ?></strong></small>
              </div>
              <button type="submit" name="remove" value="<?= htmlspecialchars($it['id']) ?>" class="btn-remove">Remove</button>
            </div>
          </div>
        <?php endforeach; ?>

        <div class="cart-summary">
          <span>Total</span>
          <span>$<?= number_format($total, 2) ?></span>
        </div>

        <div class="cart-actions">
          <a href="index.php" class="btn btn-outline">Continue Shopping</a>
          <button type="submit" name="update" class="btn btn-primary">Update Cart</button>
          <a href="checkout.php" class="btn btn-primary">Checkout</a>
        </div>
      </form>
    <?php endif; ?>

    <div class="logout">
      <a href="logout.php">Logout</a>
    </div>
  </div>
</body>
</html>

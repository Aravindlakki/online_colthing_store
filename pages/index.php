<?php
session_start();

// Static product list with your uploaded clothing images
$products = [
  ['id'=>1, 'name'=>'RUN Hoodie', 'price'=>45.99, 'image'=>'../images/pexels-kowalievska-1040424.jpg', 'stock'=>12],
  ['id'=>2, 'name'=>'Knitted Vest', 'price'=>29.99, 'image'=>'../images/pexels-anj-namoro-1479642-2850487.jpg', 'stock'=>20],
  ['id'=>3, 'name'=>'Formal Suit', 'price'=>89.00, 'image'=>'../images/pexels-andrew-3178767.jpg', 'stock'=>10],
  ['id'=>4, 'name'=>'Casual Jacket', 'price'=>55.50, 'image'=>'../images/pexels-thomasronveaux-3705262.jpg', 'stock'=>18],
  ['id'=>5, 'name'=>'Maroon Shirt', 'price'=>39.99, 'image'=>'../images/pexels-yogendras31-1760900.jpg', 'stock'=>25],
  ['id'=>6, 'name'=>'Yellow Hoodie', 'price'=>42.00, 'image'=>'../images/pexels-marleneleppanen-1183266.jpg', 'stock'=>15],
  ['id'=>7, 'name'=>'Black T-Shirt', 'price'=>24.50, 'image'=>'../images/pexels-elii-3662357.jpg', 'stock'=>30],
];

$cart_count = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $it) $cart_count += (int)$it['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Shop Collections | Online Clothing Store</title>
  <style>
    :root {
      --accent: #111;
      --muted: #777;
      --border: #e5e5e5;
      --bg: #fff;
      --hover: #f9f9f9;
      --font: 'Inter', 'Helvetica Neue', Arial, sans-serif;
    }

    body {
      margin: 0;
      background: var(--bg);
      font-family: var(--font);
      color: var(--accent);
      text-align: center;
      padding: 0;
    }

    header {
      padding: 20px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid var(--border);
    }

    header h1 {
      font-size: 22px;
      letter-spacing: 1px;
      font-weight: 500;
      margin: 0;
    }

    nav a {
      margin: 0 12px;
      text-decoration: none;
      color: var(--accent);
      font-size: 14px;
      font-weight: 500;
    }

    nav a:hover {
      text-decoration: underline;
    }

    .cart {
      font-size: 14px;
    }

    .cart span {
      background: #000;
      color: #fff;
      border-radius: 12px;
      padding: 3px 8px;
      margin-left: 5px;
    }

    .hero {
      text-align: center;
      padding: 60px 20px 30px;
    }

    .hero h2 {
      font-weight: 400;
      font-size: 28px;
      letter-spacing: 1px;
      margin-bottom: 10px;
    }

    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 40px;
      padding: 0 60px 60px;
      justify-items: center;
    }

    .card {
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 4px;
      overflow: hidden;
      width: 260px;
      transition: all 0.3s ease;
      cursor: pointer;
      text-align: center;
    }

    .card:hover {
      background: var(--hover);
      transform: translateY(-4px);
    }

    .card img {
      width: 100%;
      height: 260px;
      object-fit: cover;
      border-bottom: 1px solid var(--border);
    }

    .card h3 {
      margin: 12px 0 6px;
      font-size: 16px;
      font-weight: 500;
    }

    .price {
      font-size: 14px;
      color: var(--muted);
      margin-bottom: 14px;
    }

    .view-btn {
      display: inline-block;
      background: transparent;
      border: 1px solid var(--accent);
      color: var(--accent);
      font-size: 13px;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      padding: 6px 10px;
      border-radius: 2px;
      text-decoration: none;
      margin-bottom: 15px;
      transition: all 0.2s ease;
    }

    .view-btn:hover {
      background: var(--accent);
      color: #fff;
    }

    footer {
      padding: 20px;
      border-top: 1px solid var(--border);
      color: var(--muted);
      font-size: 13px;
    }
  </style>
</head>

<body>
  <header>
    <h1>Lou Apparel</h1>
    <nav>
      <a href="#">Home</a>
      <a href="#">Shop</a>
      <a href="#">About</a>
      <a href="#">Contact</a>
    </nav>
    <div class="cart">Cart <span><?= (int)$cart_count ?></span></div>
  </header>

  <section class="hero">
    <h2>Shop Collections</h2>
  </section>

  <div class="grid">
    <?php foreach ($products as $p): ?>
      <div class="card">
        <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
        <h3><?= htmlspecialchars($p['name']) ?></h3>
        <div class="price">$<?= number_format($p['price'], 2) ?></div>
        <form method="POST" action="add_to_cart.php">
          <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
          <input type="hidden" name="quantity" value="1">
          <button type="submit" class="view-btn">Add to Cart</button>
        </form>
      </div>
    <?php endforeach; ?>
  </div>

  <footer>
    © <?= date('Y') ?> Lou Apparel — All rights reserved.
  </footer>
</body>
</html>

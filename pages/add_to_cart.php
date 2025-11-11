<?php
session_start();

// Static product list (same as index.php)
$products = [
  ['id'=>1, 'name'=>'RUN Hoodie', 'price'=>45.99, 'image'=>'../images/pexels-kowalievska-1040424.jpg', 'stock'=>12],
  ['id'=>2, 'name'=>'Knitted Vest', 'price'=>29.99, 'image'=>'../images/pexels-anj-namoro-1479642-2850487.jpg', 'stock'=>20],
  ['id'=>3, 'name'=>'Formal Suit', 'price'=>89.00, 'image'=>'../images/pexels-andrew-3178767.jpg', 'stock'=>10],
  ['id'=>4, 'name'=>'Casual Jacket', 'price'=>55.50, 'image'=>'../images/pexels-thomasronveaux-3705262.jpg', 'stock'=>18],
  ['id'=>5, 'name'=>'Maroon Shirt', 'price'=>39.99, 'image'=>'../images/pexels-yogendras31-1760900.jpg', 'stock'=>25],
  ['id'=>6, 'name'=>'Yellow Hoodie', 'price'=>42.00, 'image'=>'../images/pexels-marleneleppanen-1183266.jpg', 'stock'=>15],
  ['id'=>7, 'name'=>'Black T-Shirt', 'price'=>24.50, 'image'=>'../images/pexels-elii-3662357.jpg', 'stock'=>30],
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;

// find product
$found = null;
foreach ($products as $p) {
    if ($p['id'] === $product_id) { $found = $p; break; }
}

if (!$found) {
    // invalid product, redirect back
    header('Location: index.php');
    exit;
}

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// use product id as key for easy increment
$key = (string)$found['id'];
if (isset($_SESSION['cart'][$key])) {
    $_SESSION['cart'][$key]['quantity'] += $quantity;
    // enforce stock if provided
    if (isset($found['stock'])) {
        $_SESSION['cart'][$key]['quantity'] = min($_SESSION['cart'][$key]['quantity'], (int)$found['stock']);
    }
} else {
    $_SESSION['cart'][$key] = [
        'id' => $found['id'],
        'name' => $found['name'],
        'price' => $found['price'],
        'image' => $found['image'],
        'quantity' => $quantity,
        'stock' => $found['stock'],
    ];
}

// After adding, redirect to cart so user can open it directly
header('Location: cart.php');
exit;
?>
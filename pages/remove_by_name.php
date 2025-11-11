<?php
<?php
session_start();

// Name of the product to remove
$target = 'Classic White T-Shirt';

if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $key => $item) {
        if (is_array($item) && isset($item['name']) && $item['name'] === $target) {
            unset($_SESSION['cart'][$key]);
            break;
        }
    }
}

// Redirect back to cart
header('Location: cart.php');
exit;
?>
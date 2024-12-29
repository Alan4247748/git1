<?php
$routes = [
    'home' => function () {
        include 'index.html';
    },
    'cart' => function () {
        include 'cart.html';
    },
    'checkout' => function () {
        include 'checkout.html';
    },
    'contact' => function () {
        include 'contact.html';
    },
    'legal' => function () {
        include 'legal.html';
    },
    'privacy-policy' => function () {
        include 'privacy-policy.html';
    },
    'api/products' => function () {
        header('Content-Type: application/json');
        echo file_get_contents('products.json');
        exit;
    },
    'cart/add' => function () {
        $productId = $_POST['product_id'] ?? null;
        $quantity = $_POST['quantity'] ?? 1;

        if ($productId) {
            addToCart($productId, $quantity);
        }
        header('Location: /cart');
        exit;
    },
    'cart/clear' => function () {
        clearCart();
        header('Location: /cart');
        exit;
    }
];
?>

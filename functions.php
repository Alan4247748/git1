<?php
function loadProducts() {
    $productsJson = file_get_contents('products.json');
    return json_decode($productsJson, true);
}

function addToCart($productId, $quantity) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
}

function clearCart() {
    unset($_SESSION['cart']);
}

function calculateCartTotal($products) {
    $total = 0;
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $productId => $quantity) {
            foreach ($products as $product) {
                if ($product['product_id'] == $productId) {
                    $total += $product['price'] * $quantity;
                    break;
                }
            }
        }
    }
    return $total;
}
?>

<?php

// Load products from JSON
function loadProducts() {
    $productsFile = __DIR__ . '/products.json';
    if (!file_exists($productsFile)) {
        die("Error: 'products.json' file is missing!");
    }
    return json_decode(file_get_contents($productsFile), true);
}

// Render products
function renderProducts($products) {
    echo '<div class="products-grid">';
    foreach ($products as $product) {
        echo '
            <div class="product-card">
                <img src="' . htmlspecialchars($product['image']) . '" alt="' . htmlspecialchars($product['name']) . '">
                <h3>' . htmlspecialchars($product['name']) . '</h3>
                <p>' . htmlspecialchars($product['description']) . '</p>
                <p>$' . number_format($product['price'], 2) . '</p>
                <button hx-post="/index.php?page=cart/add" hx-vals=\'{"product_id":' . $product['product_id'] . '}\' hx-target="#cart-summary">
                    Add to Cart
                </button>
            </div>
        ';
    }
    echo '</div>';
}

// Render cart
function renderCart($products) {
    echo '<div id="cart-summary">';
    if (empty($_SESSION['cart'])) {
        echo '<p>Your cart is empty.</p>';
    } else {
        echo '<ul>';
        foreach ($_SESSION['cart'] as $productId => $quantity) {
            $product = $products[$productId - 1] ?? null;
            if ($product) {
                echo '<li>' . htmlspecialchars($product['name']) . ' (x' . $quantity . ') - $' . number_format($product['price'] * $quantity, 2) . '</li>';
            }
        }
        echo '</ul>';
        echo '<button hx-post="/index.php?page=cart/clear" hx-target="#cart-summary">Clear Cart</button>';
        echo '<a href="/index.php?page=checkout">Proceed to Checkout</a>';
    }
    echo '</div>';
}

// Add to cart
function addToCart($productId) {
    if (!isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] = 0;
    }
    $_SESSION['cart'][$productId]++;
}

// Clear cart
function clearCart() {
    $_SESSION['cart'] = [];
}

// Handle checkout
function handleCheckout() {
    $total = calculateTotal();
    $success = processPayment($total);
    if ($success) {
        clearCart();
        echo "<p>Payment successful! Thank you for your order.</p>";
    } else {
        echo "<p>Payment failed. Please try again.</p>";
    }
}

// Calculate total
function calculateTotal() {
    $products = loadProducts();
    $total = 0;
    foreach ($_SESSION['cart'] as $productId => $quantity) {
        $product = $products[$productId - 1] ?? null;
        if ($product) {
            $total += $product['price'] * $quantity;
        }
    }
    return $total;
}

// Simulate payment processing
function processPayment($amount) {
    // Replace with actual payment gateway integration
    return $amount > 0;
}

?>

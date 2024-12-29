<?php
session_start();

// Load products.json
$products = json_decode(file_get_contents('products.json'), true);

// Handle different routes
if (isset($_GET['page'])) {
    $page = $_GET['page'];

    // API: Fetch all products
    if ($page === 'api/products') {
        header('Content-Type: application/json');
        echo json_encode($products);
        exit;
    }

    // API: Add product to cart
    if ($page === 'cart/add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $cart = $_SESSION['cart'] ?? [];
        $data = json_decode(file_get_contents('php://input'), true);
        $product_id = $data['product_id'];
        $quantity = $data['quantity'] ?? 1;

        // Update cart
        if (!isset($cart[$product_id])) {
            $cart[$product_id] = $quantity;
        } else {
            $cart[$product_id] += $quantity;
        }

        $_SESSION['cart'] = $cart;
        echo json_encode(['success' => true, 'cart' => $cart]);
        exit;
    }

    // API: Get cart contents
    if ($page === 'cart') {
        $cart = $_SESSION['cart'] ?? [];
        $cart_items = [];

        foreach ($cart as $id => $quantity) {
            foreach ($products as $product) {
                if ($product['product_id'] == $id) {
                    $cart_items[] = [
                        'name' => $product['name'],
                        'quantity' => $quantity,
                        'price' => $product['price'],
                        'total' => $product['price'] * $quantity
                    ];
                    break;
                }
            }
        }

        header('Content-Type: application/json');
        echo json_encode($cart_items);
        exit;
    }

    // Clear the cart
    if ($page === 'cart/clear') {
        $_SESSION['cart'] = [];
        echo json_encode(['success' => true]);
        exit;
    }
}

// Default: Render index.html with embedded product data
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Das Products | Filters, Papers, & Cones</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/styles.css">
    <script src="https://unpkg.com/htmx.org"></script>
</head>
<body>
<nav class="navbar">
    <a href="/">
        <img src="/images/ab2.png" alt="Das Logo" width="90">
    </a>
    <div>
        <a href="/">Products</a>
        <a href="/contact">Contact</a>
        <div class="cart-icon" hx-get="/index.php?page=cart" hx-target="#cart-container">
            🛒 <span id="cart-item-count">0</span>
        </div>
    </div>
</nav>

<section id="cart-summary" class="cart-summary" style="display: none;">
    <h3>Cart Summary</h3>
    <div id="cart-container"></div>
    <div class="cart-total">
        <strong>Total:</strong> <span id="cart-total">$0.00</span>
    </div>
    <button onclick="window.location.href='/index.php?page=checkout'">Go to Checkout</button>
</section>

<div class="tabs">
    <button class="tab-button active" hx-get="/index.php?page=api/products" hx-target="#products-container">All Products</button>
</div>

<section id="products-container">
    <?php foreach ($products as $product): ?>
        <div class="product-card">
            <img src="<?= $product['image'] ?>" alt="<?= $product['name'] ?>" class="product-image">
            <div class="product-details">
                <h3><?= $product['name'] ?></h3>
                <p><?= $product['description'] ?></p>
                <p class="product-price">$<?= number_format($product['price'], 2) ?></p>
                <button hx-post="/index.php?page=cart/add" hx-vals='{"product_id": <?= $product['product_id'] ?>, "quantity": 1}'>Add to Cart</button>
            </div>
        </div>
    <?php endforeach; ?>
</section>

<footer>
    <div class="footer-container">
        <div class="footer-left">
            <a href="/">
                <img src="/images/ab.png" alt="Das Logo" width="90">
            </a>
            <p class="tagline">FILTERS, PAPERS, AND CONES.</p>
        </div>
        <div class="footer-center">
            <h3>Site Map</h3>
            <ul>
                <li><a href="/">Products</a></li>
                <li><a href="/contact">Contact</a></li>
                <li><a href="/legal">Legal</a></li>
                <li><a href="/privacy-policy">Privacy Policy</a></li>
            </ul>
        </div>
        <div class="footer-right">
            <h3>Contact Us</h3>
            <p>Email: info@dasfilter.shop</p>
        </div>
    </div>
</footer>
<script>
document.querySelector('.cart-icon').addEventListener('click', function () {
    document.getElementById('cart-summary').style.display = 'block';
});
</script>
</body>
</html>

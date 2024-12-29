<?php

require 'vendor/autoload.php'; // For Authorize.net or other libraries
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

// Start session
session_start();
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// Load product data
$productsFile = __DIR__ . '/products.json';
if (!file_exists($productsFile)) {
    die("Error: 'products.json' file is missing!");
}
$products = json_decode(file_get_contents($productsFile), true);

// Handle routing
$page = $_GET['page'] ?? 'home';

switch ($page) {
    case 'home':
        include 'index.html';
        break;

    case 'products':
        renderProducts($products);
        break;

    case 'cart':
        renderCart($products);
        break;

    case 'cart/add':
        addToCart($_POST['product_id'] ?? null);
        renderCart($products);
        break;

    case 'cart/clear':
        $_SESSION['cart'] = [];
        renderCart($products);
        break;

    case 'checkout':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            handleCheckout();
        } else {
            include 'checkout.html';
        }
        break;

    case 'contact':
        include 'contact.html';
        break;

    case 'legal':
        include 'legal.html';
        break;

    case 'privacy-policy':
        include 'privacy-policy.html';
        break;

    case 'terms-of-service':
        include 'terms-of-service.html';
        break;

    default:
        http_response_code(404);
        echo "<h1>404 Not Found</h1>";
        break;
}

// Functions
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

function addToCart($productId) {
    if (!$productId) {
        echo "<p>Invalid product.</p>";
        return;
    }
    if (!isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] = 0;
    }
    $_SESSION['cart'][$productId]++;
}

function handleCheckout() {
    $total = calculateTotal();
    $success = processPayment($total);
    if ($success) {
        $_SESSION['cart'] = [];
        echo "<p>Payment successful! Thank you for your order.</p>";
    } else {
        echo "<p>Payment failed. Please try again.</p>";
    }
}

function calculateTotal() {
    global $products;
    $total = 0;
    foreach ($_SESSION['cart'] as $productId => $quantity) {
        $product = $products[$productId - 1] ?? null;
        if ($product) {
            $total += $product['price'] * $quantity;
        }
    }
    return $total;
}

function processPayment($amount) {
    // Simulated payment logic
    return $amount > 0; // Replace with Authorize.net integration
}
?>


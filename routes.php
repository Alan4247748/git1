<?php
session_start();

// Helper functions
function loadProducts() {
    return json_decode(file_get_contents('products.json'), true);
}

function addToCart($productId, $quantity) {
    $cart = $_SESSION['cart'] ?? [];
    $cart[$productId] = ($cart[$productId] ?? 0) + $quantity;
    $_SESSION['cart'] = $cart;
}

function clearCart() {
    $_SESSION['cart'] = [];
}

function getCartItems() {
    $products = loadProducts();
    $cart = $_SESSION['cart'] ?? [];
    $cartItems = [];

    foreach ($cart as $id => $quantity) {
        foreach ($products as $product) {
            if ($product['product_id'] == $id) {
                $cartItems[] = [
                    'name' => $product['name'],
                    'quantity' => $quantity,
                    'price' => $product['price'],
                    'total' => $product['price'] * $quantity
                ];
                break;
            }
        }
    }

    return $cartItems;
}

// Route handling
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
        echo json_encode(loadProducts());
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
    },
    'cart/items' => function () {
        header('Content-Type: application/json');
        echo json_encode(getCartItems());
        exit;
    }
];

// Route dispatcher
$page = $_GET['page'] ?? 'home';
if (isset($routes[$page])) {
    $routes[$page]();
} else {
    http_response_code(404);
    echo "404 - Page Not Found";
}
?>

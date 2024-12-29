<?php

require 'functions.php';

// Define all routes
$routes = [
    // Home page
    'home' => function () {
        include 'index.html';
    },

    // Products page
    'products' => function () {
        $products = loadProducts();
        renderProducts($products);
    },

    // Cart page
    'cart' => function () {
        $products = loadProducts();
        renderCart($products);
    },

    // Add to cart action
    'cart/add' => function () {
        $productId = $_POST['product_id'] ?? null;
        if ($productId) {
            addToCart($productId);
        }
        $products = loadProducts();
        renderCart($products);
    },

    // Clear cart action
    'cart/clear' => function () {
        clearCart();
        $products = loadProducts();
        renderCart($products);
    },

    // Checkout page
    'checkout' => function () {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            handleCheckout();
        } else {
            include 'checkout.html';
        }
    },

    // Static pages
    'contact' => function () {
        include 'contact.html';
    },
    'legal' => function () {
        include 'legal.html';
    },
    'privacy-policy' => function () {
        include 'privacy-policy.html';
    },
    'terms-of-service' => function () {
        include 'terms-of-service.html';
    },
];


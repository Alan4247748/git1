<?php
// Load products data
$products = json_decode(file_get_contents('products.json'), true);

// Handle routing
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Start output buffering
ob_start();

// Include the appropriate page based on the route
switch ($page) {
    case 'cart':
        include 'cart.html';
        break;
    case 'checkout':
        include 'checkout.html';
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
    case 'products':
        // Serve the products JSON
        header('Content-Type: application/json');
        echo json_encode($products);
        exit;
    default:
        include 'index.html';
        break;
}

// Get the buffered content and clean the buffer
$content = ob_get_clean();

// Output the final content
echo $content;
?>

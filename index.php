<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page = $_GET['page'] ?? 'home';
echo "<h1>Current Page: $page</h1>";

switch ($page) {
    case 'products':
        echo "<h2>Products Page</h2>";
        break;
    case 'cart':
        echo "<h2>Cart Page</h2>";
        break;
    default:
        echo "<h2>Home Page</h2>";
}
?>

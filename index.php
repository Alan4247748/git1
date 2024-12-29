<?php
session_start();
require 'functions.php';
require 'routes.php';

$page = $_GET['page'] ?? 'home';

// Check if the requested page matches a route
if (isset($routes[$page])) {
    $routes[$page]();
} else {
    // Default to rendering the homepage
    include 'index.html';
}
?>

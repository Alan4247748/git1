<?php
// Load products from the JSON file
$products = json_decode(file_get_contents('products.json'), true);

// Handle routing
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Start output buffering
ob_start();

// Route logic
switch ($page) {
    case 'products':
        // Serve products filtered by category if provided
        $category = $_GET['category'] ?? 'All';
        if ($category === 'All') {
            echo json_encode($products);
        } else {
            $filteredProducts = array_filter($products, function ($product) use ($category) {
                return strpos($product['categories'], $category) !== false;
            });
            echo json_encode(array_values($filteredProducts));
        }
        exit;

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

    default:
        // Render the main index page
        renderIndex($products);
        break;
}

// Get the buffered content and output it
echo ob_get_clean();

// Function to render the main index page with embedded product categories
function renderIndex($products)
{
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
                <div class="cart-icon" hx-get="/?page=cart" hx-trigger="click" hx-target="#cart-summary">
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
            <button onclick="window.location.href='/?page=checkout'">Go to Checkout</button>
        </section>

        <div class="tabs">
            <button class="tab-button active" hx-get="/?page=products&category=All" hx-target="#products-container">All Products</button>
            <button class="tab-button" hx-get="/?page=products&category=Packages" hx-target="#products-container">Packages</button>
            <button class="tab-button" hx-get="/?page=products&category=Papers" hx-target="#products-container">Papers</button>
            <button class="tab-button" hx-get="/?page=products&category=Filters" hx-target="#products-container">Filters</button>
            <button class="tab-button" hx-get="/?page=products&category=Nanner Barrels" hx-target="#products-container">Nanner Barrels</button>
        </div>

        <section id="products-container" class="products-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
                    <div class="product-details">
                        <h3><?= htmlspecialchars($product['name']) ?></h3>
                        <p><?= htmlspecialchars($product['description']) ?></p>
                        <p class="product-price">$<?= number_format($product['price'], 2) ?></p>
                        <button hx-post="/cart/add" hx-include="this" hx-target="#cart-item-count" hx-swap="outerHTML">
                            Add to Cart
                        </button>
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
                        <li><a href="/terms-of-service">Terms of Service</a></li>
                        <li><a href="/privacy-policy">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="footer-right">
                    <h3>Contact Info</h3>
                    <p>Das Filter, LLC</p>
                    <p>903 West Mary, Austin, TX</p>
                    <p>+1-737-334-8042</p>
                </div>
            </div>
        </footer>

        <script>
            document.addEventListener("DOMContentLoaded", () => {
                const cartIcon = document.querySelector("#cart-item-count");
                if (cartIcon) {
                    updateCartCount();
                }
            });

            function updateCartCount() {
                fetch('/?page=cart')
                    .then(response => response.json())
                    .then(cart => {
                        const count = Object.values(cart).reduce((sum, item) => sum + item.quantity, 0);
                        document.getElementById("cart-item-count").textContent = count;
                    })
                    .catch(console.error);
            }
        </script>
    </body>
    </html>
    <?php
}
?>

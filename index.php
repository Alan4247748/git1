<?php
require 'vendor/autoload.php';

use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

// Initialize session for cart and inventory
session_start();
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
if (!isset($_SESSION['inventory'])) $_SESSION['inventory'] = initializeInventory();

// Load products
$products = json_decode(file_get_contents('products.json'), true);

// Handle routing
$page = $_GET['page'] ?? 'home';
ob_start();

switch ($page) {
    case 'products':
        renderProducts();
        break;

    case 'cart':
        renderCart();
        break;

    case 'cart/add':
        addToCart($_POST['product_id']);
        renderCart();
        break;

    case 'cart/clear':
        $_SESSION['cart'] = [];
        renderCart();
        break;

    case 'checkout':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            handleCheckout();
        } else {
            renderCheckout();
        }
        break;

    case 'dashboard':
        renderDashboard();
        break;

    default:
        renderIndex();
}

echo ob_get_clean();

function initializeInventory() {
    global $products;
    $inventory = [];
    foreach ($products as $product) {
        $inventory[$product['product_id']] = $product['inventory'];
    }
    return $inventory;
}

function renderIndex() {
    include 'index.html';
}

function renderProducts() {
    global $products;
    ?>
    <div class="products-grid">
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                <h3><?= htmlspecialchars($product['name']) ?></h3>
                <p><?= htmlspecialchars($product['description']) ?></p>
                <p>$<?= number_format($product['price'], 2) ?></p>
                <button hx-post="/?page=cart/add" hx-vals='{"product_id":<?= $product['product_id'] ?>}' hx-target="#cart-summary">
                    Add to Cart
                </button>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
}

function renderCart() {
    ?>
    <div>
        <?php if (empty($_SESSION['cart'])): ?>
            <p>Your cart is empty.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($_SESSION['cart'] as $productId => $quantity): ?>
                    <li><?= htmlspecialchars(getProduct($productId)['name']) ?> (x<?= $quantity ?>)</li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <button hx-post="/?page=cart/clear" hx-target="#cart-summary">Clear Cart</button>
        <a href="/?page=checkout">Proceed to Checkout</a>
    </div>
    <?php
}

function renderCheckout() {
    ?>
    <form hx-post="/?page=checkout" hx-target="#checkout-summary">
        <h2>Shipping Information</h2>
        <input type="text" name="name" placeholder="Name" required>
        <textarea name="address" placeholder="Address" required></textarea>
        <input type="email" name="email" placeholder="Email" required>

        <h2>Payment Information</h2>
        <input type="text" name="card_number" placeholder="Card Number" required>
        <input type="text" name="exp_date" placeholder="Expiration Date (MM/YY)" required>
        <input type="text" name="cvv" placeholder="CVV" required>
        <button type="submit">Place Order</button>
    </form>
    <?php
}

function renderDashboard() {
    ?>
    <h1>Inventory Dashboard</h1>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Stock</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($_SESSION['inventory'] as $productId => $stock): ?>
                <tr>
                    <td><?= htmlspecialchars(getProduct($productId)['name']) ?></td>
                    <td><?= $stock ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
}

function handleCheckout() {
    $total = calculateTotal();
    $response = processPayment($total);

    if ($response->getMessages()->getResultCode() === 'Ok') {
        updateInventory();
        $_SESSION['cart'] = [];
        echo "<p>Payment successful! Thank you for your order.</p>";
    } else {
        echo "<p>Payment failed: " . $response->getMessages()->getMessage()[0]->getText() . "</p>";
    }
}

function addToCart($productId) {
    if ($_SESSION['inventory'][$productId] > 0) {
        $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + 1;
        $_SESSION['inventory'][$productId]--;
    } else {
        echo "<p>Product out of stock!</p>";
    }
}

function getProduct($productId) {
    global $products;
    foreach ($products as $product) {
        if ($product['product_id'] == $productId) {
            return $product;
        }
    }
    return null;
}

function calculateTotal() {
    global $products;
    $total = 0;
    foreach ($_SESSION['cart'] as $id => $qty) {
        $total += $products[$id - 1]['price'] * $qty;
    }
    return $total;
}

function processPayment($amount) {
    $auth = new AnetAPI\MerchantAuthenticationType();
    $auth->setName('your_login_id');
    $auth->setTransactionKey('your_transaction_key');

    $creditCard = new AnetAPI\CreditCardType();
    $creditCard->setCardNumber("4111111111111111");
    $creditCard->setExpirationDate("2030-12");

    $payment = new AnetAPI\PaymentType();
    $payment->setCreditCard($creditCard);

    $transactionRequest = new AnetAPI\TransactionRequestType();
    $transactionRequest->setTransactionType("authCaptureTransaction");
    $transactionRequest->setAmount($amount);
    $transactionRequest->setPayment($payment);

    $request = new AnetAPI\CreateTransactionRequest();
    $request->setMerchantAuthentication($auth);
    $request->setTransactionRequest($transactionRequest);

    $controller = new AnetController\CreateTransactionController($request);
    return $controller->executeWithApiResponse(AnetAPI\ANetEnvironment::SANDBOX);
}

function updateInventory() {
    foreach ($_SESSION['cart'] as $id => $qty) {
        $_SESSION['inventory'][$id] -= $qty;
    }
}
?>

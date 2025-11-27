<?php
/**
 * Checkout View
 * Displays a summary of all cart items and handles checkout
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Functions/get_cart_count.php';
require_once __DIR__ . '/../Controllers/cart_controller.php';

// Store guest IP in session if not logged in (for cart transfer after registration/login)
if (!is_logged_in()) {
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    if ($ip_address === '::1') {
        $ip_address = '127.0.0.1';
    }
    $_SESSION['guest_ip_address'] = $ip_address;
}

// Initialize cart controller
$cart_controller = new cart_controller();

// Get cart items (works for both logged-in users and guests)
$cart_items = $cart_controller->get_user_cart_ctr();
$cart_total = $cart_controller->get_cart_total_ctr();

// Redirect to cart if empty
if (empty($cart_items)) {
    header('Location: cart.php');
    exit;
}

$placeholderImage = '../../uploads/placeholder.jpg';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Wellness 360</title>
    <link rel="stylesheet" href="../Css/style.css">
    <link rel="stylesheet" href="../Css/shop.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <!-- User Greeting Banner -->
    <?php if (is_logged_in()): ?>
    <div class="user-greeting-banner">
        <div class="container">
            <div class="greeting-content">
                <span class="greeting-text">
                    Hello <?php echo htmlspecialchars(explode(' ', current_user_name())[0]); ?>, get right back in!
                </span>
                <a href="../Actions/logout_action.php" class="logout-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Header -->
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="../index.php">
                        <h1>Wellness 360</h1>
                    </a>
                </div>
                <nav class="main-nav">
                    <ul class="nav-menu">
                        <li><a href="../index.php">Home</a></li>
                        <li><a href="wellness_hub.php">Wellness Hub</a></li>
                        <li><a href="shop.php">Shop</a></li>
                        <li><a href="community.php">Community</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="profile.php">Profile</a></li>
                        <li><a href="cart.php" class="cart-link"><i class="fas fa-shopping-cart"></i> Cart <span id="cart-count"><?php echo get_cart_count(); ?></span></a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Checkout Section -->
    <section class="checkout-section" style="padding: 2rem 0; min-height: 60vh;">
        <div class="container">
            <h1 style="margin-bottom: 2rem;">Checkout</h1>
            
            <div class="checkout-content" style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                <!-- Order Items -->
                <div class="checkout-items">
                    <h2 style="margin-bottom: 1.5rem;">Order Items</h2>
                    <div class="checkout-items-list" style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="checkout-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-bottom: 1px solid #ddd; margin-bottom: 1rem;">
                                <img src="<?php echo htmlspecialchars(get_image_path($item['product_image'] ?: $placeholderImage)); ?>" 
                                     alt="<?php echo htmlspecialchars($item['product_title']); ?>"
                                     style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                <div style="flex: 1;">
                                    <h3 style="margin: 0; font-size: 1rem;"><?php echo htmlspecialchars($item['product_title']); ?></h3>
                                    <p style="margin: 0.5rem 0 0 0; color: #666;">
                                        <span class="item-price" data-price="<?php echo $item['product_price']; ?>">
                                            ₵<?php echo number_format($item['product_price'], 2); ?>
                                        </span>
                                        × 
                                        <span class="item-quantity"><?php echo $item['quantity']; ?></span>
                                    </p>
                                </div>
                                <div style="font-weight: bold;">
                                    ₵<?php echo number_format($item['subtotal'], 2); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <a href="cart.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Cart
                    </a>
                </div>
                
                <!-- Order Summary -->
                <div class="checkout-summary" style="background: #f8f9fa; padding: 2rem; border-radius: 8px; height: fit-content;">
                    <h2 style="margin-top: 0; margin-bottom: 1.5rem;">Order Summary</h2>
                    <div style="margin-bottom: 1rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Subtotal:</span>
                            <span id="checkout-subtotal">₵<?php echo number_format($cart_total, 2); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Tax:</span>
                            <span id="checkout-tax">₵0.00</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Shipping:</span>
                            <span>₵0.00</span>
                        </div>
                        <hr style="margin: 1rem 0; border: none; border-top: 1px solid #ddd;">
                        <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: bold;">
                            <span>Total:</span>
                            <span id="checkout-total">₵<?php echo number_format($cart_total, 2); ?></span>
                        </div>
                    </div>
                    
                    <!-- Payment Method Selection -->
                    <div style="margin-bottom: 1.5rem;">
                        <label for="payment_method" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                            Payment Method <span style="color: red;">*</span>
                        </label>
                        <select id="payment_method" name="payment_method" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                            <option value="">Select Payment Method</option>
                            <option value="paystack">Paystack</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="mobile_money">Mobile Money</option>
                        </select>
                    </div>
                    
                    <!-- Payment Channel Selection (shown only for Paystack) -->
                    <div id="payment_channel_container" style="margin-bottom: 1.5rem; display: none;">
                        <label for="payment_channel" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                            Payment Channel <span style="color: red;">*</span>
                        </label>
                        <select id="payment_channel" name="payment_channel" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                            <option value="">Select Payment Channel</option>
                            <option value="card">Card</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="bank">Bank</option>
                        </select>
                    </div>
                    
                    <div style="background: #e7f3e8; border: 1px solid #7FB685; padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem;">
                        <p style="margin: 0; font-size: 0.9rem; color: #2C3E35;">
                            <i class="fas fa-shield-alt"></i> <strong>Secure Payment</strong><br>
                            Your payment information is encrypted and secure.
                        </p>
                    </div>
                    
                    <button id="pay-now-btn" 
                            class="btn btn-primary" 
                            style="width: 100%; padding: 1rem; font-size: 1.1rem;"
                            disabled>
                        <i class="fas fa-lock"></i> Proceed to Payment
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Wellness 360</h3>
                    <p>Your partner in achieving holistic well-being and optimal health.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="../index.php">Home</a></li>
                        <li><a href="wellness_hub.php">Wellness Hub</a></li>
                        <li><a href="shop.php">Shop</a></li>
                        <li><a href="community.php">Community</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Resources</h4>
                    <ul>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact Us</h4>
                    <ul>
                        <li><i class="fas fa-envelope"></i> wellnessallround@gmail.com</li>
                        <li><i class="fas fa-phone"></i> 0204567321</li>
                        <li><i class="fas fa-map-marker-alt"></i> 3rd Circular rd, Tema</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date("Y"); ?> Wellness 360. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="../js/checkout.js"></script>
</body>
</html>


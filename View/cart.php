<?php
/**
 * Cart View
 * Displays all items in the user's cart
 */

require_once __DIR__ . '/../settings/core.php';
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

// Get cart items
$cart_items = $cart_controller->get_user_cart_ctr();
$cart_total = $cart_controller->get_cart_total_ctr();
$cart_item_count = $cart_controller->get_cart_item_count_ctr();

$placeholderImage = '../../uploads/placeholder.jpg';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Wellness 360</title>
    <link rel="stylesheet" href="../Css/style.css">
    <link rel="stylesheet" href="../Css/shop.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Hide spinner arrows on number input */
        .quantity-input::-webkit-outer-spin-button,
        .quantity-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        
        /* Firefox */
        .quantity-input[type=number] {
            -moz-appearance: textfield;
        }
    </style>
</head>
<body>
    <?php if (is_logged_in()): ?>
        <!-- User Greeting Banner -->
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
                        <li><a href="cart.php" class="cart-link active"><i class="fas fa-shopping-cart"></i> Cart <span id="cart-count"><?php echo $cart_item_count; ?></span></a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Cart Section -->
    <section class="cart-section" style="padding: 2rem 0; min-height: 60vh;">
        <div class="container">
            <h1 style="margin-bottom: 2rem;">Shopping Cart</h1>
            
            <?php if (empty($cart_items)): ?>
                <div class="empty-cart-message" style="text-align: center; padding: 4rem 2rem;">
                    <i class="fas fa-shopping-cart" style="font-size: 4rem; color: #ccc; margin-bottom: 1rem;"></i>
                    <h2>Your cart is empty</h2>
                    <p style="color: #666; margin-bottom: 2rem;">Add some products to get started!</p>
                    <a href="shop.php" class="btn btn-primary">Continue Shopping</a>
                </div>
            <?php else: ?>
                <div class="cart-content">
                    <div class="cart-items" id="cart-items">
                        <table class="cart-table" style="width: 100%; border-collapse: collapse; margin-bottom: 2rem;">
                            <thead>
                                <tr style="border-bottom: 2px solid #ddd;">
                                    <th style="padding: 1rem; text-align: left;">Product</th>
                                    <th style="padding: 1rem; text-align: left;">Price</th>
                                    <th style="padding: 1rem; text-align: center;">Quantity</th>
                                    <th style="padding: 1rem; text-align: right;">Subtotal</th>
                                    <th style="padding: 1rem; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                    <tr data-product-id="<?php echo $item['product_id']; ?>" style="border-bottom: 1px solid #eee;">
                                        <td style="padding: 1rem;">
                                            <div style="display: flex; align-items: center; gap: 1rem;">
                                                <img src="<?php echo htmlspecialchars(get_image_path($item['product_image'] ?: $placeholderImage)); ?>" 
                                                     alt="<?php echo htmlspecialchars($item['product_title']); ?>"
                                                     style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                                                <div>
                                                    <h3 style="margin: 0; font-size: 1.1rem;"><?php echo htmlspecialchars($item['product_title']); ?></h3>
                                                    <?php if (isset($item['stock']) && $item['stock'] < $item['quantity']): ?>
                                                        <p style="color: #dc3545; margin: 0.5rem 0 0 0; font-size: 0.9rem;">
                                                            <i class="fas fa-exclamation-triangle"></i> Only <?php echo $item['stock']; ?> available
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="padding: 1rem;">
                                            <span class="item-price" data-price="<?php echo $item['product_price']; ?>">
                                                ₵<?php echo number_format($item['product_price'], 2); ?>
                                            </span>
                                        </td>
                                        <td style="padding: 1rem; text-align: center;">
                                            <div style="display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                                                <button class="quantity-decrease" data-product-id="<?php echo $item['product_id']; ?>" 
                                                        style="width: 30px; height: 30px; border: 1px solid #ddd; background: #f8f9fa; cursor: pointer; border-radius: 4px;">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <input type="number" 
                                                       class="quantity-input" 
                                                       data-product-id="<?php echo $item['product_id']; ?>"
                                                       value="<?php echo $item['quantity']; ?>" 
                                                       min="1" 
                                                       max="<?php echo $item['stock'] ?? 999; ?>"
                                                       style="width: 60px; text-align: center; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                                                <button class="quantity-increase" data-product-id="<?php echo $item['product_id']; ?>"
                                                        style="width: 30px; height: 30px; border: 1px solid #ddd; background: #f8f9fa; cursor: pointer; border-radius: 4px;">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td style="padding: 1rem; text-align: right;">
                                            <span class="item-subtotal">
                                                ₵<?php echo number_format($item['subtotal'], 2); ?>
                                            </span>
                                        </td>
                                        <td style="padding: 1rem; text-align: center;">
                                            <button class="remove-item-btn" 
                                                    data-product-id="<?php echo $item['product_id']; ?>"
                                                    style="background: #dc3545; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer;">
                                                <i class="fas fa-trash"></i> Remove
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                            <button id="empty-cart-btn" 
                                    class="btn btn-primary">
                                <i class="fas fa-trash-alt"></i> Empty Cart
                            </button>
                            <a href="shop.php" class="btn btn-primary">
                                <i class="fas fa-arrow-left"></i> Continue Shopping
                            </a>
                        </div>
                    </div>
                    
                    <div class="cart-summary" style="background: #f8f9fa; padding: 2rem; border-radius: 8px; height: fit-content;">
                        <h2 style="margin-top: 0; margin-bottom: 1.5rem;">Order Summary</h2>
                        <div style="margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span>Subtotal:</span>
                                <span id="cart-subtotal">₵<?php echo number_format($cart_total, 2); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span>Tax:</span>
                                <span>₵0.00</span>
                            </div>
                            <hr style="margin: 1rem 0; border: none; border-top: 1px solid #ddd;">
                            <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: bold;">
                                <span>Total:</span>
                                <span id="cart-total">₵<?php echo number_format($cart_total, 2); ?></span>
                            </div>
                        </div>
                        
                        <a href="checkout.php" class="btn btn-primary" style="width: 100%; text-align: center; display: block; padding: 1rem; margin-top: 1.5rem;">
                            <i class="fas fa-credit-card"></i> Proceed to Checkout
                        </a>
                    </div>
                </div>
            <?php endif; ?>
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

    <script src="../js/cart.js"></script>
</body>
</html>


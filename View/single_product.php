<?php
/**
 * Single Product View
 * Displays detailed view of a single product
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Functions/get_cart_count.php';
require_once __DIR__ . '/../Controllers/ShopController.php';

// Get product ID from query string
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header('Location: shop.php');
    exit;
}

// Initialize controller and get product
$controller = new ShopController();
$product = $controller->get_product($product_id);

if (!$product) {
    header('Location: shop.php');
    exit;
}

$placeholderImage = 'uploads/placeholder.jpg';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['product_title']); ?> - Wellness 360</title>
    <link rel="stylesheet" href="../Css/style.css">
    <link rel="stylesheet" href="../Css/single_product.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                        <li><a href="shop.php" class="active">Shop</a></li>
                        <li><a href="community.php">Community</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="profile.php">Profile</a></li>
                        <li><a href="cart.php" class="cart-link"><i class="fas fa-shopping-cart"></i> Cart <span id="cart-count"><?php echo get_cart_count(); ?></span></a></li>
                    </ul>
                    <div class="mobile-menu-toggle">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <!-- Single Product Section -->
    <section class="single-product-section">
        <div class="container">
            <!-- Breadcrumb -->
            <nav class="breadcrumb">
                <a href="../index.php">Home</a>
                <span>/</span>
                <a href="shop.php">Shop</a>
                <span>/</span>
                <span><?php echo htmlspecialchars($product['product_title']); ?></span>
            </nav>

            <div class="single-product-layout">
                <!-- Product Image -->
                <div class="product-image-section">
                    <div class="product-main-image">
                        <img src="../<?php echo htmlspecialchars($product['product_image'] ?: $placeholderImage); ?>" 
                             alt="<?php echo htmlspecialchars($product['product_title']); ?>"
                             onerror="this.onerror=null; this.src='../<?php echo $placeholderImage; ?>';">
                    </div>
                </div>

                <!-- Product Details -->
                <div class="product-details-section">
                    <div class="product-header">
                        <div class="product-category-badge">
                            <?php echo htmlspecialchars($product['cat_name'] ?? 'Uncategorized'); ?>
                        </div>
                        <h1 class="product-title"><?php echo htmlspecialchars($product['product_title']); ?></h1>
                        <div class="product-meta">
                            <span class="product-vendor">
                                <i class="fas fa-store"></i>
                                <?php echo htmlspecialchars($product['vendor_name'] ?? 'Unknown Vendor'); ?>
                            </span>
                        </div>
                    </div>

                    <div class="product-price-section">
                        <div class="product-price">₵<?php echo number_format($product['product_price'], 2); ?></div>
                        <?php if ($product['stock'] > 0): ?>
                            <div class="product-stock in-stock">
                                <i class="fas fa-check-circle"></i>
                                In Stock
                            </div>
                        <?php else: ?>
                            <div class="product-stock out-of-stock">
                                <i class="fas fa-times-circle"></i>
                                Out of Stock
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="product-description">
                        <h3>Description</h3>
                        <p><?php echo nl2br(htmlspecialchars($product['product_desc'] ?: 'No description available.')); ?></p>
                    </div>

                    <?php if (!empty($product['product_keywords'])): ?>
                        <div class="product-keywords">
                            <h3>Keywords</h3>
                            <div class="keywords-tags">
                                <?php 
                                $keywords = explode(',', $product['product_keywords']);
                                foreach ($keywords as $keyword): 
                                    $keyword = trim($keyword);
                                    if (!empty($keyword)):
                                ?>
                                    <span class="keyword-tag"><?php echo htmlspecialchars($keyword); ?></span>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="product-actions">
                        <button class="btn-add-to-cart-large add-to-cart-btn" 
                                data-product-id="<?php echo $product['product_id']; ?>"
                                type="button">
                            <i class="fas fa-shopping-cart"></i>
                            Add to Cart
                        </button>
                        <button class="btn-wishlist-large wishlist-btn" 
                                data-product-id="<?php echo $product['product_id']; ?>"
                                onclick="toggleWishlist(<?php echo $product['product_id']; ?>)">
                            <i class="far fa-heart"></i>
                            Add to Wishlist
                        </button>
                    </div>

                    <div class="product-info-grid">
                        <div class="info-item">
                            <i class="fas fa-box"></i>
                            <div>
                                <strong>Category:</strong>
                                <span><?php echo htmlspecialchars($product['cat_name'] ?? 'Uncategorized'); ?></span>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-store"></i>
                            <div>
                                <strong>Vendor:</strong>
                                <span><?php echo htmlspecialchars($product['vendor_name'] ?? 'Unknown Vendor'); ?></span>
                            </div>
                        </div>
                    </div>
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
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date("Y"); ?> Wellness 360. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="../js/main.js"></script>
    <script src="../js/cart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/wishlist.js"></script>
</body>
</html>


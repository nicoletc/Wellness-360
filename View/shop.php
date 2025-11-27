<?php
/**
 * Shop View
 * Includes controller and displays data
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Functions/get_cart_count.php';

// Include Controller
require_once __DIR__ . '/../Controllers/ShopController.php';

// Initialize controller and get data
$controller = new ShopController();
$data = $controller->index();

// Extract data from controller
$categories = $data['categories'] ?? [];
$vendors = $data['vendors'] ?? [];
$products = $data['products'] ?? [];
$priceRange = $data['priceRange'] ?? ['min' => 0, 'max' => 500];
$selectedCategory = $data['selectedCategory'] ?? 'all';
$selectedVendor = $data['selectedVendor'] ?? 'all';
$minPrice = $data['minPrice'] ?? $priceRange['min'];
$maxPrice = $data['maxPrice'] ?? $priceRange['max'];
$searchQuery = $data['searchQuery'] ?? '';
$sortBy = $data['sortBy'] ?? 'date';
$pagination = $data['pagination'] ?? ['page' => 1, 'limit' => 10, 'total' => 0, 'total_pages' => 1];
$placeholderImage = $data['placeholderImage'] ?? '../../uploads/placeholder.jpg';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wellness Shop - Wellness 360</title>
    <link rel="stylesheet" href="../Css/style.css">
    <link rel="stylesheet" href="../Css/shop.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php if (is_logged_in()): ?>
        <!-- User Greeting Banner -->
        <div class="user-greeting-banner">
            <div class="container">
                <div class="greeting-content">
                    <div class="greeting-avatar">
                        <img src="<?php echo htmlspecialchars(get_image_path(current_user_image())); ?>" 
                             alt="<?php echo htmlspecialchars(explode(' ', current_user_name())[0]); ?>"
                             onerror="this.onerror=null; this.src='../../uploads/placeholder_avatar.jpg';">
                    </div>
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

    <?php
    /**
     * Shop View
     * Displays the shop page content
     */

    // Extract data passed from controller
    $categories = $data['categories'] ?? [];
    $products = $data['products'] ?? [];
    $priceRange = $data['priceRange'] ?? ['min' => 0, 'max' => 500];
    $selectedCategory = $data['selectedCategory'] ?? 'all';
    $minPrice = $data['minPrice'] ?? $priceRange['min'];
    $maxPrice = $data['maxPrice'] ?? $priceRange['max'];
    $searchQuery = $data['searchQuery'] ?? '';
    $sortBy = $data['sortBy'] ?? 'featured';
    $placeholderImage = $data['placeholderImage'] ?? '../../uploads/placeholder.jpg';
    ?>

    <!-- Shop Header -->
    <section class="shop-header">
        <div class="container">
            <div class="shop-header-content">
                <h1 class="shop-title">Wellness Shop</h1>
                <p class="shop-description">Curated products from verified vendors across Ghana</p>
                
                <!-- Feature Badges -->
                <div class="shop-badges">
                    <div class="shop-badge">
                        <i class="fas fa-check-circle"></i>
                        <span>All Vendors Verified</span>
                    </div>
                    <div class="shop-badge">
                        <i class="fas fa-mobile-alt"></i>
                        <span>MoMo Accepted</span>
                    </div>
                    <div class="shop-badge">
                        <i class="fas fa-lock"></i>
                        <span>Secure Payments</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Shop Content -->
    <section class="shop-content">
        <div class="container">
            <div class="shop-layout">
                <!-- Left Sidebar - Filters -->
                <aside class="shop-sidebar">
                    <div class="filters-panel">
                        <h2 class="filters-title">Filters</h2>
                        
                        <!-- Search Products -->
                        <div class="filter-section">
                            <label class="filter-label">Search Products</label>
                            <form method="GET" action="shop.php" class="filter-search-form">
                                <div class="filter-search-input">
                                    <i class="fas fa-search"></i>
                                    <input type="text" 
                                           name="search" 
                                           placeholder="Search(keywords also)..." 
                                           value="<?php echo htmlspecialchars($searchQuery); ?>">
                                    <?php if ($selectedCategory !== 'all'): ?>
                                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($selectedCategory); ?>">
                                    <?php endif; ?>
                                    <?php if ($minPrice != $priceRange['min'] || $maxPrice != $priceRange['max']): ?>
                                        <input type="hidden" name="minPrice" value="<?php echo $minPrice; ?>">
                                        <input type="hidden" name="maxPrice" value="<?php echo $maxPrice; ?>">
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Categories -->
                        <div class="filter-section">
                            <label class="filter-label">Categories</label>
                            <div class="filter-categories">
                                <label class="filter-checkbox">
                                    <input type="radio" 
                                           name="category" 
                                           value="all"
                                           <?php echo $selectedCategory === 'all' ? 'checked' : ''; ?>
                                           onchange="this.form.submit()"
                                           form="category-form">
                                    <span>All Categories</span>
                                </label>
                                <?php foreach ($categories as $category): ?>
                                    <label class="filter-checkbox">
                                        <input type="radio" 
                                               name="category" 
                                               value="<?php echo $category['cat_id']; ?>"
                                               <?php echo $selectedCategory == $category['cat_id'] ? 'checked' : ''; ?>
                                               onchange="this.form.submit()"
                                               form="category-form">
                                        <span><?php echo htmlspecialchars($category['cat_name']); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <form id="category-form" method="GET" action="shop.php" style="display: none;">
                                <?php if (!empty($searchQuery)): ?>
                                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>">
                                <?php endif; ?>
                                <?php if ($selectedVendor !== 'all'): ?>
                                    <input type="hidden" name="vendor" value="<?php echo htmlspecialchars($selectedVendor); ?>">
                                <?php endif; ?>
                                <?php if ($minPrice != $priceRange['min'] || $maxPrice != $priceRange['max']): ?>
                                    <input type="hidden" name="minPrice" value="<?php echo $minPrice; ?>">
                                    <input type="hidden" name="maxPrice" value="<?php echo $maxPrice; ?>">
                                <?php endif; ?>
                            </form>
                        </div>
                        
                        <!-- Vendors -->
                        <div class="filter-section">
                            <label class="filter-label">Vendors</label>
                            <div class="filter-categories">
                                <label class="filter-checkbox">
                                    <input type="radio" 
                                           name="vendor" 
                                           value="all"
                                           <?php echo $selectedVendor === 'all' ? 'checked' : ''; ?>
                                           onchange="this.form.submit()"
                                           form="vendor-form">
                                    <span>All Vendors</span>
                                </label>
                                <?php foreach ($vendors as $vendor): ?>
                                    <label class="filter-checkbox">
                                        <input type="radio" 
                                               name="vendor" 
                                               value="<?php echo $vendor['vendor_id']; ?>"
                                               <?php echo $selectedVendor == $vendor['vendor_id'] ? 'checked' : ''; ?>
                                               onchange="this.form.submit()"
                                               form="vendor-form">
                                        <span><?php echo htmlspecialchars($vendor['vendor_name']); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <form id="vendor-form" method="GET" action="shop.php" style="display: none;">
                                <?php if (!empty($searchQuery)): ?>
                                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>">
                                <?php endif; ?>
                                <?php if ($selectedCategory !== 'all'): ?>
                                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($selectedCategory); ?>">
                                <?php endif; ?>
                                <?php if ($minPrice != $priceRange['min'] || $maxPrice != $priceRange['max']): ?>
                                    <input type="hidden" name="minPrice" value="<?php echo $minPrice; ?>">
                                    <input type="hidden" name="maxPrice" value="<?php echo $maxPrice; ?>">
                                <?php endif; ?>
                            </form>
                        </div>
                        
                        <!-- Price Range -->
                        <div class="filter-section">
                            <label class="filter-label">Price Range</label>
                            <div class="price-range-container">
                                <div class="price-range-display">
                                    <span>₵<?php echo number_format($minPrice); ?></span>
                                    <span>₵<?php echo number_format($maxPrice); ?></span>
                                </div>
                                <div class="price-slider-wrapper">
                                    <input type="range" 
                                           id="priceMin" 
                                           min="<?php echo $priceRange['min']; ?>" 
                                           max="<?php echo $priceRange['max']; ?>" 
                                           value="<?php echo $minPrice; ?>"
                                           class="price-slider price-slider-min">
                                    <input type="range" 
                                           id="priceMax" 
                                           min="<?php echo $priceRange['min']; ?>" 
                                           max="<?php echo $priceRange['max']; ?>" 
                                           value="<?php echo $maxPrice; ?>"
                                           class="price-slider price-slider-max">
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
                
                <!-- Main Product Area -->
                <main class="shop-main">
                    <!-- Product Count and Sort -->
                    <div class="shop-toolbar">
                        <div class="product-count">
                            Showing <?php echo count($products); ?> of <?php echo $pagination['total']; ?> products
                        </div>
                        <div class="sort-dropdown">
                            <form method="GET" action="shop.php" class="sort-form">
                                <select name="sort" onchange="this.form.submit()" class="sort-select">
                                    <option value="date" <?php echo $sortBy === 'date' ? 'selected' : ''; ?>>Sort by: Newest</option>
                                    <option value="price_low" <?php echo $sortBy === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                    <option value="price_high" <?php echo $sortBy === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                    <option value="name" <?php echo $sortBy === 'name' ? 'selected' : ''; ?>>Name: A to Z</option>
                                </select>
                                <?php if ($selectedCategory !== 'all'): ?>
                                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($selectedCategory); ?>">
                                <?php endif; ?>
                                <?php if ($selectedVendor !== 'all'): ?>
                                    <input type="hidden" name="vendor" value="<?php echo htmlspecialchars($selectedVendor); ?>">
                                <?php endif; ?>
                                <?php if (!empty($searchQuery)): ?>
                                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>">
                                <?php endif; ?>
                                <?php if ($minPrice != $priceRange['min'] || $maxPrice != $priceRange['max']): ?>
                                    <input type="hidden" name="minPrice" value="<?php echo $minPrice; ?>">
                                    <input type="hidden" name="maxPrice" value="<?php echo $maxPrice; ?>">
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Products Grid -->
                    <?php if (empty($products)): ?>
                        <div class="no-products-message">
                            <i class="fas fa-search"></i>
                            <p>No products found. Try adjusting your filters.</p>
                        </div>
                    <?php else: ?>
                        <div class="products-grid">
                            <?php foreach ($products as $product): ?>
                                <a href="single_product.php?id=<?php echo $product['product_id']; ?>" class="product-card">
                                    <div class="product-image-wrapper">
                                        <img src="<?php echo htmlspecialchars(get_image_path($product['product_image'] ?: $placeholderImage)); ?>" 
                                             alt="<?php echo htmlspecialchars($product['product_title']); ?>"
                                             onerror="this.onerror=null; this.src='../<?php echo $placeholderImage; ?>';">
                                        <button class="product-wishlist-btn wishlist-btn" 
                                                type="button" 
                                                data-product-id="<?php echo $product['product_id']; ?>">
                                            <i class="far fa-heart"></i>
                                        </button>
                                    </div>
                                    <div class="product-info">
                                        <div class="product-category-tag"><?php echo htmlspecialchars($product['cat_name'] ?? 'Uncategorized'); ?></div>
                                        <h3 class="product-name"><?php echo htmlspecialchars($product['product_title']); ?></h3>
                                        <p class="product-vendor"><?php echo htmlspecialchars($product['vendor_name'] ?? 'Unknown Vendor'); ?></p>
                                        <div class="product-price">₵<?php echo number_format($product['product_price'], 2); ?></div>
                                        <button class="btn-add-to-cart add-to-cart-btn" 
                                                data-product-id="<?php echo $product['product_id']; ?>"
                                                type="button">
                                            <i class="fas fa-shopping-cart"></i>
                                            Add to Cart
                                        </button>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($pagination['total_pages'] > 1): ?>
                            <div class="pagination">
                                <?php if ($pagination['page'] > 1): ?>
                                    <a href="?page=<?php echo $pagination['page'] - 1; ?><?php echo $selectedCategory !== 'all' ? '&category=' . $selectedCategory : ''; ?><?php echo $selectedVendor !== 'all' ? '&vendor=' . $selectedVendor : ''; ?><?php echo !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : ''; ?><?php echo $minPrice != $priceRange['min'] || $maxPrice != $priceRange['max'] ? '&minPrice=' . $minPrice . '&maxPrice=' . $maxPrice : ''; ?><?php echo $sortBy !== 'date' ? '&sort=' . $sortBy : ''; ?>" class="pagination-btn">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                <?php endif; ?>
                                
                                <span class="pagination-info">
                                    Page <?php echo $pagination['page']; ?> of <?php echo $pagination['total_pages']; ?>
                                </span>
                                
                                <?php if ($pagination['page'] < $pagination['total_pages']): ?>
                                    <a href="?page=<?php echo $pagination['page'] + 1; ?><?php echo $selectedCategory !== 'all' ? '&category=' . $selectedCategory : ''; ?><?php echo $selectedVendor !== 'all' ? '&vendor=' . $selectedVendor : ''; ?><?php echo !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : ''; ?><?php echo $minPrice != $priceRange['min'] || $maxPrice != $priceRange['max'] ? '&minPrice=' . $minPrice . '&maxPrice=' . $maxPrice : ''; ?><?php echo $sortBy !== 'date' ? '&sort=' . $sortBy : ''; ?>" class="pagination-btn">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </main>
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

    <script src="../js/main.js"></script>
    <script src="../js/activity_tracker.js"></script>
    <script src="../js/cart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/wishlist.js"></script>
    <script src="../js/wellness_chatbot.js"></script>
    <script>
    // Price range slider functionality with debounce
    document.addEventListener('DOMContentLoaded', function() {
        const minSlider = document.getElementById('priceMin');
        const maxSlider = document.getElementById('priceMax');
        let debounceTimer;
        
        if (minSlider && maxSlider) {
            // Update price display immediately while sliding
            minSlider.addEventListener('input', function() {
                if (parseInt(this.value) > parseInt(maxSlider.value)) {
                    maxSlider.value = this.value;
                }
                updatePriceDisplay();
                debounceUpdate();
            });
            
            maxSlider.addEventListener('input', function() {
                if (parseInt(this.value) < parseInt(minSlider.value)) {
                    minSlider.value = this.value;
                }
                updatePriceDisplay();
                debounceUpdate();
            });
        }
        
        // Update the displayed price values immediately
        function updatePriceDisplay() {
            const priceDisplay = document.querySelector('.price-range-display');
            if (priceDisplay) {
                const spans = priceDisplay.querySelectorAll('span');
                if (spans.length >= 2) {
                    spans[0].textContent = '₵' + parseInt(minSlider.value).toLocaleString();
                    spans[1].textContent = '₵' + parseInt(maxSlider.value).toLocaleString();
                }
            }
        }
        
        // Debounce function - only submit after user stops sliding for 500ms
        function debounceUpdate() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function() {
                updatePriceRange();
            }, 500); // Wait 500ms after user stops sliding
        }
        
        // Submit form only after debounce
        function updatePriceRange() {
            const form = document.createElement('form');
            form.method = 'GET';
            form.action = 'shop.php';
            
            <?php if ($selectedCategory !== 'all'): ?>
                const categoryInput = document.createElement('input');
                categoryInput.type = 'hidden';
                categoryInput.name = 'category';
                categoryInput.value = '<?php echo htmlspecialchars($selectedCategory); ?>';
                form.appendChild(categoryInput);
            <?php endif; ?>
            
            <?php if (!empty($searchQuery)): ?>
                const searchInput = document.createElement('input');
                searchInput.type = 'hidden';
                searchInput.name = 'search';
                searchInput.value = '<?php echo htmlspecialchars($searchQuery); ?>';
                form.appendChild(searchInput);
            <?php endif; ?>
            
            const minInput = document.createElement('input');
            minInput.type = 'hidden';
            minInput.name = 'minPrice';
            minInput.value = minSlider.value;
            form.appendChild(minInput);
            
            const maxInput = document.createElement('input');
            maxInput.type = 'hidden';
            maxInput.name = 'maxPrice';
            maxInput.value = maxSlider.value;
            form.appendChild(maxInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    });
    </script>
</body>
</html>

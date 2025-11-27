<?php
/**
 * Product Search Results View
 * Displays search results with filtering options
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Functions/get_cart_count.php';
require_once __DIR__ . '/../Controllers/ShopController.php';

// Get search parameters
$searchQuery = $_GET['search'] ?? $_GET['query'] ?? '';
$selectedCategory = $_GET['category'] ?? 'all';
$selectedVendor = $_GET['vendor'] ?? 'all';
$minPrice = isset($_GET['min_price']) ? floatval($_GET['min_price']) : null;
$maxPrice = isset($_GET['max_price']) ? floatval($_GET['max_price']) : null;
$sortBy = $_GET['sort'] ?? 'date';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Initialize controller
$controller = new ShopController();

// Build filters for composite search
$filters = [];
if (!empty($searchQuery)) {
    $filters['search'] = $searchQuery;
}
if ($selectedCategory !== 'all') {
    $filters['category'] = $selectedCategory;
}
if ($selectedVendor !== 'all') {
    $filters['vendor'] = $selectedVendor;
}
if ($minPrice !== null) {
    $filters['min_price'] = $minPrice;
}
if ($maxPrice !== null) {
    $filters['max_price'] = $maxPrice;
}
$filters['sort'] = $sortBy;

// Get search results
$products = $controller->composite_search($filters, $limit, $offset);
$total = $controller->get_product_count($filters);

// Get categories and vendors for filters
$categories = $controller->get_categories();
$vendors = $controller->get_vendors();
$priceRange = $controller->get_price_range();

$pagination = [
    'page' => $page,
    'limit' => $limit,
    'total' => $total,
    'total_pages' => ceil($total / $limit)
];

$placeholderImage = '../../uploads/placeholder.jpg';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results<?php echo !empty($searchQuery) ? ': ' . htmlspecialchars($searchQuery) : ''; ?> - Wellness 360</title>
    <link rel="stylesheet" href="../Css/style.css">
    <link rel="stylesheet" href="../Css/product_search.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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

    <!-- Search Results Section -->
    <section class="search-results-section">
        <div class="container">
            <div class="search-results-header">
                <h1 class="search-results-title">
                    <?php if (!empty($searchQuery)): ?>
                        Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"
                    <?php else: ?>
                        Search Products
                    <?php endif; ?>
                </h1>
                <p class="search-results-count">
                    Found <?php echo $total; ?> product<?php echo $total !== 1 ? 's' : ''; ?>
                </p>
            </div>

            <div class="search-results-layout">
                <!-- Search and Filters Sidebar -->
                <aside class="search-filters-sidebar">
                    <!-- Search Box -->
                    <div class="filter-section">
                        <h3 class="filter-section-title">Search Products</h3>
                        <form method="GET" action="product_search_result.php" class="search-form" id="searchForm">
                            <div class="search-input-wrapper">
                                <i class="fas fa-search"></i>
                                <input type="text" 
                                       name="search" 
                                       id="searchInput"
                                       placeholder="Search by title, description, or keywords..." 
                                       value="<?php echo htmlspecialchars($searchQuery); ?>">
                                <button type="submit" class="search-submit-btn">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Category Filter -->
                    <div class="filter-section">
                        <h3 class="filter-section-title">Filter by Category</h3>
                        <form method="GET" action="product_search_result.php" id="categoryFilterForm">
                            <div class="filter-options">
                                <label class="filter-option">
                                    <input type="radio" 
                                           name="category" 
                                           value="all"
                                           <?php echo $selectedCategory === 'all' ? 'checked' : ''; ?>
                                           onchange="updateFilters()">
                                    <span>All Categories</span>
                                </label>
                                <?php foreach ($categories as $category): ?>
                                    <label class="filter-option">
                                        <input type="radio" 
                                               name="category" 
                                               value="<?php echo $category['cat_id']; ?>"
                                               <?php echo $selectedCategory == $category['cat_id'] ? 'checked' : ''; ?>
                                               onchange="updateFilters()">
                                        <span><?php echo htmlspecialchars($category['cat_name']); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <?php if (!empty($searchQuery)): ?>
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>">
                            <?php endif; ?>
                            <?php if ($selectedVendor !== 'all'): ?>
                                <input type="hidden" name="vendor" value="<?php echo htmlspecialchars($selectedVendor); ?>">
                            <?php endif; ?>
                            <?php if ($minPrice !== null): ?>
                                <input type="hidden" name="min_price" value="<?php echo $minPrice; ?>">
                            <?php endif; ?>
                            <?php if ($maxPrice !== null): ?>
                                <input type="hidden" name="max_price" value="<?php echo $maxPrice; ?>">
                            <?php endif; ?>
                        </form>
                    </div>

                    <!-- Vendor Filter -->
                    <div class="filter-section">
                        <h3 class="filter-section-title">Filter by Vendor</h3>
                        <form method="GET" action="product_search_result.php" id="vendorFilterForm">
                            <div class="filter-options">
                                <label class="filter-option">
                                    <input type="radio" 
                                           name="vendor" 
                                           value="all"
                                           <?php echo $selectedVendor === 'all' ? 'checked' : ''; ?>
                                           onchange="updateFilters()">
                                    <span>All Vendors</span>
                                </label>
                                <?php foreach ($vendors as $vendor): ?>
                                    <label class="filter-option">
                                        <input type="radio" 
                                               name="vendor" 
                                               value="<?php echo $vendor['vendor_id']; ?>"
                                               <?php echo $selectedVendor == $vendor['vendor_id'] ? 'checked' : ''; ?>
                                               onchange="updateFilters()">
                                        <span><?php echo htmlspecialchars($vendor['vendor_name']); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <?php if (!empty($searchQuery)): ?>
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>">
                            <?php endif; ?>
                            <?php if ($selectedCategory !== 'all'): ?>
                                <input type="hidden" name="category" value="<?php echo htmlspecialchars($selectedCategory); ?>">
                            <?php endif; ?>
                            <?php if ($minPrice !== null): ?>
                                <input type="hidden" name="min_price" value="<?php echo $minPrice; ?>">
                            <?php endif; ?>
                            <?php if ($maxPrice !== null): ?>
                                <input type="hidden" name="max_price" value="<?php echo $maxPrice; ?>">
                            <?php endif; ?>
                        </form>
                    </div>
                </aside>

                <!-- Search Results Main -->
                <main class="search-results-main">
                    <!-- Sort and Results Count -->
                    <div class="results-toolbar">
                        <div class="results-count">
                            Showing <?php echo count($products); ?> of <?php echo $total; ?> results
                        </div>
                        <div class="sort-dropdown">
                            <form method="GET" action="product_search_result.php" class="sort-form">
                                <select name="sort" onchange="this.form.submit()" class="sort-select">
                                    <option value="date" <?php echo $sortBy === 'date' ? 'selected' : ''; ?>>Sort by: Newest</option>
                                    <option value="price_low" <?php echo $sortBy === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                    <option value="price_high" <?php echo $sortBy === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                    <option value="name" <?php echo $sortBy === 'name' ? 'selected' : ''; ?>>Name: A to Z</option>
                                </select>
                                <?php if (!empty($searchQuery)): ?>
                                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>">
                                <?php endif; ?>
                                <?php if ($selectedCategory !== 'all'): ?>
                                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($selectedCategory); ?>">
                                <?php endif; ?>
                                <?php if ($selectedVendor !== 'all'): ?>
                                    <input type="hidden" name="vendor" value="<?php echo htmlspecialchars($selectedVendor); ?>">
                                <?php endif; ?>
                                <?php if ($minPrice !== null): ?>
                                    <input type="hidden" name="min_price" value="<?php echo $minPrice; ?>">
                                <?php endif; ?>
                                <?php if ($maxPrice !== null): ?>
                                    <input type="hidden" name="max_price" value="<?php echo $maxPrice; ?>">
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>

                    <!-- Products Grid -->
                    <?php if (empty($products)): ?>
                        <div class="no-products-message">
                            <i class="fas fa-search"></i>
                            <h3>No products found</h3>
                            <p>Try adjusting your search terms or filters.</p>
                            <a href="shop.php" class="btn-primary">Browse All Products</a>
                        </div>
                    <?php else: ?>
                        <div class="products-grid">
                            <?php foreach ($products as $product): ?>
                                <a href="single_product.php?id=<?php echo $product['product_id']; ?>" class="product-card">
                                    <div class="product-image-wrapper">
                                        <img src="<?php echo htmlspecialchars(get_image_path($product['product_image'] ?: $placeholderImage)); ?>" 
                                             alt="<?php echo htmlspecialchars($product['product_title']); ?>"
                                             onerror="this.onerror=null; this.src='../<?php echo $placeholderImage; ?>';">
                                        <button class="product-wishlist-btn" type="button" onclick="event.preventDefault();">
                                            <i class="far fa-heart"></i>
                                        </button>
                                    </div>
                                    <div class="product-info">
                                        <div class="product-category-tag"><?php echo htmlspecialchars($product['cat_name'] ?? 'Uncategorized'); ?></div>
                                        <h3 class="product-name"><?php echo htmlspecialchars($product['product_title']); ?></h3>
                                        <p class="product-vendor"><?php echo htmlspecialchars($product['vendor_name'] ?? 'Unknown Vendor'); ?></p>
                                        <div class="product-price">₵<?php echo number_format($product['product_price'], 2); ?></div>
                                        <button class="btn-add-to-cart" onclick="event.preventDefault(); addToCart(<?php echo $product['product_id']; ?>);">
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
                                    <a href="?page=<?php echo $pagination['page'] - 1; ?><?php echo !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : ''; ?><?php echo $selectedCategory !== 'all' ? '&category=' . $selectedCategory : ''; ?><?php echo $selectedVendor !== 'all' ? '&vendor=' . $selectedVendor : ''; ?><?php echo $minPrice !== null ? '&min_price=' . $minPrice : ''; ?><?php echo $maxPrice !== null ? '&max_price=' . $maxPrice : ''; ?><?php echo $sortBy !== 'date' ? '&sort=' . $sortBy : ''; ?>" class="pagination-btn">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                <?php endif; ?>
                                
                                <span class="pagination-info">
                                    Page <?php echo $pagination['page']; ?> of <?php echo $pagination['total_pages']; ?>
                                </span>
                                
                                <?php if ($pagination['page'] < $pagination['total_pages']): ?>
                                    <a href="?page=<?php echo $pagination['page'] + 1; ?><?php echo !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : ''; ?><?php echo $selectedCategory !== 'all' ? '&category=' . $selectedCategory : ''; ?><?php echo $selectedVendor !== 'all' ? '&vendor=' . $selectedVendor : ''; ?><?php echo $minPrice !== null ? '&min_price=' . $minPrice : ''; ?><?php echo $maxPrice !== null ? '&max_price=' . $maxPrice : ''; ?><?php echo $sortBy !== 'date' ? '&sort=' . $sortBy : ''; ?>" class="pagination-btn">
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
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date("Y"); ?> Wellness 360. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="../js/main.js"></script>
    <script src="../js/product_search.js"></script>
    <script src="../js/cart_count.js"></script>
    <script src="../js/wellness_chatbot.js"></script>
</body>
</html>


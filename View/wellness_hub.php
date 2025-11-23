<?php
/**
 * Wellness Hub View
 * Includes controller and displays data
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Functions/get_cart_count.php';

// Include Controller
require_once __DIR__ . '/../Controllers/WellnessHubController.php';

// Initialize controller and get data
try {
    $controller = new WellnessHubController();
    $data = $controller->index();
} catch (Exception $e) {
    die('Error loading wellness hub: ' . $e->getMessage());
}

// Extract data from controller
$categories = $data['categories'] ?? [];
$articles = $data['articles'] ?? [];
$selectedCategory = $data['selectedCategory'] ?? 'all';
$searchQuery = $data['searchQuery'] ?? '';
$placeholderImage = $data['placeholderImage'] ?? 'uploads/placeholder.jpg';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wellness Hub - Wellness 360</title>
    <link rel="stylesheet" href="../Css/style.css">
    <link rel="stylesheet" href="../Css/wellness_hub.css">
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
                        <li><a href="wellness_hub.php" class="active">Wellness Hub</a></li>
                        <li><a href="shop.php">Shop</a></li>
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

    <!-- Hero Section -->
    <section class="wellness-hub-hero">
        <div class="container">
            <div class="wellness-hub-hero-content">
                <h1 class="wellness-hub-title">Wellness Hub</h1>
                <p class="wellness-hub-subtitle">Evidence-based health content reviewed by verified professionals</p>
                
                <!-- Search Bar -->
                <div class="search-filters-container">
                    <form method="GET" action="wellness_hub.php" class="wellness-search-form">
                        <div class="search-input-wrapper">
                            <i class="fas fa-search"></i>
                            <input type="text" 
                                   name="search" 
                                   placeholder="Search articles..." 
                                   value="<?php echo htmlspecialchars($searchQuery); ?>"
                                   class="search-input">
                            <?php if (!empty($selectedCategory) && $selectedCategory !== 'all'): ?>
                                <input type="hidden" name="category" value="<?php echo htmlspecialchars($selectedCategory); ?>">
                            <?php endif; ?>
                            <button type="submit" style="display: none;"></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Category Filters -->
    <section class="wellness-categories-section">
        <div class="container">
            <div class="categories-filter" dir="ltr" data-orientation="horizontal" data-slot="tabs">
                <?php foreach ($categories as $key => $label): ?>
                    <a href="wellness_hub.php?category=<?php echo $key; ?><?php echo !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : ''; ?>" 
                       class="category-btn <?php echo $selectedCategory === $key ? 'active' : ''; ?>"
                       role="tab"
                       aria-selected="<?php echo $selectedCategory === $key ? 'true' : 'false'; ?>">
                        <?php echo htmlspecialchars($label); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Articles Grid Section -->
    <section class="all-articles-section">
        <div class="container">
            <?php if (empty($articles)): ?>
                <div class="no-articles-message">
                    <i class="fas fa-search"></i>
                    <p>No content found. Try a different search or filter.</p>
                </div>
            <?php else: ?>
                <div class="articles-grid">
                    <?php foreach ($articles as $article): ?>
                        <a href="single_article.php?id=<?php echo $article['id']; ?>" class="article-card">
                            <div class="article-image-wrapper">
                                <img src="../<?php echo htmlspecialchars($article['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($article['title']); ?>"
                                     onerror="this.onerror=null; this.src='../<?php echo $placeholderImage; ?>';">
                                <div class="article-category-badge"><?php echo htmlspecialchars($article['category']); ?></div>
                                <?php if ($article['has_pdf']): ?>
                                    <div class="article-video-badge">
                                        <i class="fas fa-file-pdf"></i>
                                        PDF
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="article-content">
                                <h3 class="article-title">
                                    <span><?php echo htmlspecialchars($article['title']); ?></span>
                                </h3>
                                <p class="article-excerpt">
                                    <?php echo htmlspecialchars($article['author']); ?>
                                </p>
                                <div class="article-meta">
                                    <div class="article-author">
                                        <i class="fas fa-user"></i>
                                        <span><?php echo htmlspecialchars($article['author']); ?></span>
                                    </div>
                                    <div class="article-stats">
                                        <span>
                                            <i class="fas fa-eye"></i>
                                            <?php echo number_format($article['views']); ?>
                                        </span>
                                        <span>
                                            <i class="fas fa-calendar"></i>
                                            <?php echo htmlspecialchars($article['date']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="article-footer">
                                    <span class="article-date"><?php echo htmlspecialchars($article['date']); ?></span>
                                    <span class="read-more-btn">
                                        Read More
                                        <i class="fas fa-arrow-right"></i>
                                    </span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
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
                        <li><i class="fas fa-envelope"></i> info@wellness360.com</li>
                        <li><i class="fas fa-phone"></i> +1 (555) 123-4567</li>
                        <li><i class="fas fa-map-marker-alt"></i> 123 Wellness St, Health City</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date("Y"); ?> Wellness 360. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="../js/main.js"></script>
    <script>
    // Auto-submit search form on Enter key
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.form.submit();
                }
            });
        }
    });
    </script>
    <script src="../js/cart_count.js"></script>
</body>
</html>

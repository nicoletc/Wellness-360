<?php
/**
 * Single Article View
 * Displays a full view of a single article with PDF
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Functions/get_cart_count.php';
require_once __DIR__ . '/../Controllers/WellnessHubController.php';

// Initialize controller
$controller = new WellnessHubController();

// Get article ID from query parameter
$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Redirect if article ID is invalid or article not found
if ($article_id <= 0) {
    header('Location: wellness_hub.php');
    exit;
}

$article = $controller->get_article($article_id);

if (!$article) {
    header('Location: wellness_hub.php?status=article_not_found');
    exit;
}

// Record the view (will check for duplicates internally)
$controller->record_view($article_id);

// Get updated article data with new view count
$article = $controller->get_article($article_id);

$placeholderImage = '../../uploads/placeholder.jpg';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?> - Wellness 360</title>
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

    <main class="single-article-main">
        <div class="container">
            <!-- Breadcrumb -->
            <nav class="article-breadcrumb">
                <a href="wellness_hub.php">Wellness Hub</a> &gt; 
                <a href="wellness_hub.php?category=<?php echo htmlspecialchars($article['category_id']); ?>">
                    <?php echo htmlspecialchars($article['category']); ?>
                </a> &gt; 
                <span><?php echo htmlspecialchars($article['title']); ?></span>
            </nav>

            <!-- Article Image -->
            <?php if (!empty($article['image']) && $article['image'] !== '../../uploads/placeholder.jpg' && $article['image'] !== '../uploads/placeholder.jpg'): ?>
                <div class="article-featured-image">
                    <img src="<?php echo htmlspecialchars(get_image_path($article['image'])); ?>" 
                         alt="<?php echo htmlspecialchars($article['title']); ?>"
                         onerror="this.onerror=null; this.src='<?php echo htmlspecialchars(get_image_path($placeholderImage)); ?>';">
                </div>
            <?php endif; ?>

            <!-- Article Header -->
            <div class="article-header-section">
                <div class="article-category-badge">
                    <?php echo htmlspecialchars($article['category']); ?>
                </div>
                <h1 class="article-title"><?php echo htmlspecialchars($article['title']); ?></h1>
                <div class="article-meta-info">
                    <div class="article-author">
                        <i class="fas fa-user"></i>
                        <span><?php echo htmlspecialchars($article['author']); ?></span>
                    </div>
                    <div class="article-date">
                        <i class="fas fa-calendar"></i>
                        <span><?php echo htmlspecialchars($article['date']); ?></span>
                    </div>
                    <div class="article-views">
                        <i class="fas fa-eye"></i>
                        <span><?php echo number_format($article['views']); ?> views</span>
                    </div>
                </div>
            </div>

            <!-- PDF Viewer Section -->
            <div class="article-pdf-section">
                <?php if ($article['has_pdf']): ?>
                    <div class="pdf-viewer-wrapper">
                        <iframe 
                            src="../Actions/view_article_pdf_action.php?id=<?php echo $article_id; ?>" 
                            class="pdf-viewer"
                            title="Article PDF">
                            <p>Your browser does not support PDFs. 
                               <a href="../Actions/view_article_pdf_action.php?id=<?php echo $article_id; ?>" target="_blank">Download the PDF</a> instead.
                            </p>
                        </iframe>
                    </div>
                    <div class="pdf-actions">
                        <a href="../Actions/view_article_pdf_action.php?id=<?php echo $article_id; ?>" 
                           target="_blank" 
                           class="btn btn-primary">
                            <i class="fas fa-download"></i>
                            Download PDF
                        </a>
                    </div>
                <?php else: ?>
                    <div class="no-pdf-message">
                        <i class="fas fa-file-pdf"></i>
                        <p>No PDF available for this article.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

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
    <script src="../js/cart_count.js"></script>
    <script src="../js/activity_tracker.js"></script>
    <script src="../js/wellness_chatbot.js"></script>
</body>
</html>


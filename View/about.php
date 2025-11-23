<?php
/**
 * About View
 * Includes controller and displays data
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Functions/get_cart_count.php';

// Include Controller
require_once __DIR__ . '/../Controllers/AboutController.php';

// Initialize controller and get data
$controller = new AboutController();
$data = $controller->index();

// Extract data from controller
$ourStory = $data['ourStory'] ?? [];
$mission = $data['mission'] ?? [];
$vision = $data['vision'] ?? [];
$storyBehind = $data['storyBehind'] ?? [];
$coreValues = $data['coreValues'] ?? [];
$cta = $data['cta'] ?? [];
$placeholderImage = $data['placeholderImage'] ?? 'uploads/placeholder.jpg';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Wellness 360</title>
    <link rel="stylesheet" href="../Css/style.css">
    <link rel="stylesheet" href="../Css/about.css">
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
                        <li><a href="shop.php">Shop</a></li>
                        <li><a href="community.php">Community</a></li>
                        <li><a href="about.php" class="active">About</a></li>
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

    <!-- Our Story Section -->
    <section class="about-our-story-section">
        <div class="container">
            <div class="our-story-content">
                <div class="story-badge"><?php echo htmlspecialchars($ourStory['badge']); ?></div>
                <h1 class="story-title">
                    <?php echo htmlspecialchars($ourStory['title']); ?> 
                    <span class="title-highlight"><?php echo htmlspecialchars($ourStory['titleHighlight']); ?></span>
                </h1>
                <p class="story-description"><?php echo htmlspecialchars($ourStory['description']); ?></p>
            </div>
        </div>
    </section>

    <!-- Mission and Vision Section -->
    <section class="mission-vision-section">
        <div class="container">
            <div class="mission-vision-grid">
                <div class="mission-vision-card">
                    <div class="mv-icon-wrapper">
                        <?php if ($mission['icon'] === 'target'): ?>
                            <i class="fas fa-bullseye"></i>
                        <?php elseif ($mission['icon'] === 'star'): ?>
                            <i class="fas fa-star"></i>
                        <?php endif; ?>
                    </div>
                    <h2 class="mv-title"><?php echo htmlspecialchars($mission['title']); ?></h2>
                    <p class="mv-description"><?php echo htmlspecialchars($mission['description']); ?></p>
                </div>
                <div class="mission-vision-card">
                    <div class="mv-icon-wrapper">
                        <?php if ($vision['icon'] === 'star'): ?>
                            <i class="fas fa-star"></i>
                        <?php elseif ($vision['icon'] === 'target'): ?>
                            <i class="fas fa-bullseye"></i>
                        <?php endif; ?>
                    </div>
                    <h2 class="mv-title"><?php echo htmlspecialchars($vision['title']); ?></h2>
                    <p class="mv-description"><?php echo htmlspecialchars($vision['description']); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- The Story Behind Section -->
    <section class="story-behind-section">
        <div class="container">
            <div class="story-behind-grid">
                <div class="story-behind-content">
                    <h2 class="story-behind-title"><?php echo htmlspecialchars($storyBehind['title']); ?></h2>
                    <?php foreach ($storyBehind['paragraphs'] as $paragraph): ?>
                        <p class="story-behind-text"><?php echo htmlspecialchars($paragraph); ?></p>
                    <?php endforeach; ?>
                </div>
                <div class="story-behind-image">
                    <img src="../<?php echo htmlspecialchars($storyBehind['image']); ?>" 
                         alt="Wellness 360 Story"
                         onerror="this.onerror=null; this.style.display='none';">
                </div>
            </div>
        </div>
    </section>

    <!-- Core Values Section -->
    <section class="core-values-section">
        <div class="container">
            <div class="core-values-header">
                <h2 class="core-values-title"><?php echo htmlspecialchars($coreValues['title']); ?></h2>
                <p class="core-values-subtitle"><?php echo htmlspecialchars($coreValues['subtitle']); ?></p>
            </div>
            <div class="core-values-grid">
                <?php foreach ($coreValues['values'] as $value): ?>
                    <div class="value-card">
                        <div class="value-icon-wrapper">
                            <?php
                            $iconClass = '';
                            switch($value['icon']) {
                                case 'heart':
                                    $iconClass = 'fa-heart';
                                    break;
                                case 'star':
                                    $iconClass = 'fa-star';
                                    break;
                                case 'users':
                                    $iconClass = 'fa-users';
                                    break;
                                case 'target':
                                    $iconClass = 'fa-bullseye';
                                    break;
                                case 'ribbon':
                                    $iconClass = 'fa-ribbon';
                                    break;
                                case 'globe':
                                    $iconClass = 'fa-globe';
                                    break;
                            }
                            ?>
                            <i class="fas <?php echo $iconClass; ?>"></i>
                        </div>
                        <h3 class="value-title"><?php echo htmlspecialchars($value['title']); ?></h3>
                        <p class="value-description"><?php echo htmlspecialchars($value['description']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="about-cta-section">
        <div class="container">
            <div class="about-cta-content">
                <div class="cta-icon-wrapper">
                    <i class="fas fa-heart"></i>
                </div>
                <h2 class="cta-title"><?php echo htmlspecialchars($cta['title']); ?></h2>
                <p class="cta-description"><?php echo htmlspecialchars($cta['description']); ?></p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Wellness 360</h3>
                    <p>Your wellness companion – making health accessible, trusted, and tech-driven for every Ghanaian.</p>
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
                        <li><a href="about.php">About</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Resources</h4>
                    <ul>
                        <li><a href="#">Mental Health</a></li>
                        <li><a href="#">Nutrition Guide</a></li>
                        <li><a href="#">Fitness Plans</a></li>
                        <li><a href="#">FAQs</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Stay Connected</h4>
                    <ul>
                        <li><i class="fas fa-envelope"></i> hello@wellness360.gh</li>
                        <li><i class="fas fa-phone"></i> +233 20 123 4567</li>
                        <li><i class="fas fa-map-marker-alt"></i> Accra, Ghana</li>
                    </ul>
                    <div class="newsletter-section">
                        <p>Subscribe to our newsletter</p>
                        <form class="newsletter-form">
                            <input type="email" placeholder="Your email" class="newsletter-input">
                            <button type="submit" class="btn btn-primary newsletter-btn">Join</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date("Y"); ?> Wellness 360. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="../js/main.js"></script>
    <script src="../js/cart_count.js"></script>
</body>
</html>


<?php
/**
 * Profile View
 * Includes controller and displays data
 */

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Functions/get_cart_count.php';

// Include Controller
require_once __DIR__ . '/../Controllers/ProfileController.php';

// Initialize controller and get data
try {
    $controller = new ProfileController();
    $data = $controller->index();
} catch (Exception $e) {
    die('Error loading profile: ' . $e->getMessage());
}

// Extract data from controller
$userProfile = $data['userProfile'] ?? null;
$userStats = $data['userStats'] ?? [];
$recommendedContent = $data['recommendedContent'] ?? [];
$articlesRead = $data['articlesRead'] ?? [];
$orders = $data['orders'] ?? [];
$wishlist = $data['wishlist'] ?? [];
$reminderHistory = $data['reminderHistory'] ?? [];
$reminderPreferences = $data['reminderPreferences'] ?? null;
$categories = $data['categories'] ?? [];
$activeTab = $data['activeTab'] ?? 'orders';
$memberSince = $data['memberSince'] ?? 'Recently';
$placeholderImage = $data['placeholderImage'] ?? '../../uploads/placeholder.jpg';
$placeholderAvatar = $data['placeholderAvatar'] ?? '../../uploads/placeholder_avatar.jpg';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Wellness 360</title>
    <link rel="stylesheet" href="../Css/style.css">
    <link rel="stylesheet" href="../Css/profile.css">
    <link rel="stylesheet" href="../Css/notifications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Edit Profile Modal Styling */
        .edit-profile-modal {
            padding: 2rem !important;
            max-width: 450px !important;
        }
        
        .edit-profile-modal .swal2-html-container {
            margin: 1.5rem 0 0 0 !important;
            padding: 0 !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        
        .edit-profile-modal .swal2-html-container > div {
            width: 100% !important;
            box-sizing: border-box !important;
            max-width: 100% !important;
        }
        
        .edit-profile-modal #profile-name-input {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            margin: 0 !important;
            display: block !important;
        }
        
        .edit-profile-modal .swal2-input {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            margin: 0 !important;
        }
        
        .edit-profile-modal .swal2-popup {
            max-width: 450px !important;
            width: 450px !important;
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
                        <li><a href="profile.php" class="active">Profile</a></li>
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

    <!-- Profile Content -->
    <main class="profile-main">
        <div class="container">
            <?php if (is_logged_in() && $userProfile): ?>
                <!-- User Profile Section -->
                <section class="profile-section">
                    <div class="profile-card">
                        <div class="profile-header">
                            <div class="profile-info">
                                <div class="profile-avatar">
                                    <img src="<?php echo htmlspecialchars(get_image_path($userProfile['customer_image'] ?? $placeholderAvatar)); ?>" 
                                         alt="<?php echo htmlspecialchars($userProfile['customer_name']); ?>"
                                         onerror="this.onerror=null; this.style.display='none';">
                                </div>
                                <div class="profile-details">
                                    <h1 class="profile-name"><?php echo htmlspecialchars($userProfile['customer_name']); ?></h1>
                                    <p class="profile-meta">Member since <?php echo htmlspecialchars($memberSince); ?></p>
                                </div>
                            </div>
                            <button class="btn-edit-profile">
                                <i class="fas fa-cog"></i> Edit Profile
                            </button>
                        </div>
                    </div>
                </section>

                <!-- Activity Summary Section -->
                <section class="profile-stats-section">
                    <div class="profile-content-block">
                        <h2 class="content-block-title">Your Activity Summary</h2>
                        <div class="overview-stats">
                            <a href="profile.php?tab=articles" class="overview-stat-item" style="text-decoration: none; color: inherit;">
                                <i class="fas fa-book-open"></i>
                                <div>
                                    <h3><?php echo number_format($userStats['articlesRead'] ?? 0); ?></h3>
                                    <p>Articles Read</p>
                                </div>
                            </a>
                            <a href="profile.php?tab=orders" class="overview-stat-item" style="text-decoration: none; color: inherit;">
                                <i class="fas fa-shopping-bag"></i>
                                <div>
                                    <h3><?php echo number_format($userStats['ordersPlaced'] ?? 0); ?></h3>
                                    <p>Orders Placed</p>
                                </div>
                            </a>
                            <a href="profile.php?tab=wishlist" class="overview-stat-item" style="text-decoration: none; color: inherit;">
                                <i class="far fa-heart"></i>
                                <div>
                                    <h3><?php echo number_format($userStats['wishlist'] ?? 0); ?></h3>
                                    <p>Wishlist</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </section>

                <!-- Profile Tabs -->
                <section class="profile-tabs-section">
                    <div class="profile-tabs">
                        <a href="profile.php?tab=articles" class="profile-tab <?php echo $activeTab === 'articles' ? 'active' : ''; ?>">
                            Articles Read
                        </a>
                        <a href="profile.php?tab=orders" class="profile-tab <?php echo $activeTab === 'orders' ? 'active' : ''; ?>">
                            Orders
                        </a>
                        <a href="profile.php?tab=wishlist" class="profile-tab <?php echo $activeTab === 'wishlist' ? 'active' : ''; ?>">
                            Wishlist
                        </a>
                        <a href="profile.php?tab=recommended" class="profile-tab <?php echo $activeTab === 'recommended' ? 'active' : ''; ?>">
                            Recommended
                        </a>
                        <a href="profile.php?tab=reminders" class="profile-tab <?php echo $activeTab === 'reminders' ? 'active' : ''; ?>">
                            Reminders
                        </a>
                        <a href="profile.php?tab=settings" class="profile-tab <?php echo $activeTab === 'settings' ? 'active' : ''; ?>">
                            Settings
                        </a>
                    </div>
                </section>

                <!-- Tab Content -->
                <section class="profile-content-section">
                    <?php if ($activeTab === 'articles'): ?>
                        <!-- Articles Read Tab -->
                        <div class="profile-content-block">
                            <h2 class="content-block-title">Articles You've Read</h2>
                            <?php if (empty($articlesRead)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-book-open"></i>
                                    <p>No articles read yet. Start exploring our wellness articles!</p>
                                </div>
                            <?php else: ?>
                                <div class="articles-read-list">
                                    <?php foreach ($articlesRead as $article): ?>
                                        <div class="article-read-item">
                                            <div class="article-read-info">
                                                <h3 class="article-read-title">
                                                    <a href="single_article.php?id=<?php echo $article['article_id']; ?>" style="text-decoration: none; color: inherit;">
                                                        <?php echo htmlspecialchars($article['title']); ?>
                                                    </a>
                                                </h3>
                                                <div class="article-read-meta">
                                                    <span class="article-read-author">
                                                        <i class="fas fa-user"></i>
                                                        <?php echo htmlspecialchars($article['author']); ?>
                                                    </span>
                                                    <span class="article-read-category">
                                                        <i class="fas fa-tag"></i>
                                                        <?php echo htmlspecialchars($article['category']); ?>
                                                    </span>
                                                    <span class="article-read-date">
                                                        <i class="fas fa-calendar"></i>
                                                        Published: <?php echo htmlspecialchars($article['date_added']); ?>
                                                    </span>
                                                </div>
                                                <p class="article-read-viewed">
                                                    <i class="fas fa-eye"></i>
                                                    Read <?php echo $article['read_count']; ?> time<?php echo $article['read_count'] > 1 ? 's' : ''; ?>
                                                    <?php if ($article['read_count'] > 1): ?>
                                                        <span style="margin-left: 0.5rem; color: var(--muted-foreground);">
                                                            (Last: <?php echo htmlspecialchars($article['last_viewed_at']); ?>)
                                                        </span>
                                                    <?php else: ?>
                                                        <span style="margin-left: 0.5rem; color: var(--muted-foreground);">
                                                            (<?php echo htmlspecialchars($article['last_viewed_at']); ?>)
                                                        </span>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                            <div class="article-read-action">
                                                <a href="single_article.php?id=<?php echo $article['article_id']; ?>" class="btn btn-primary">
                                                    Read Again
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    
                    <?php elseif ($activeTab === 'orders'): ?>
                        <!-- Orders Tab -->
                        <div class="profile-content-block">
                            <h2 class="content-block-title">Your Orders</h2>
                            <?php if (empty($orders)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-shopping-bag"></i>
                                    <p>No orders yet. Start shopping to see your orders here!</p>
                                </div>
                            <?php else: ?>
                                <div class="orders-list">
                                    <?php foreach ($orders as $order): ?>
                                        <div class="order-item" data-order-id="<?php echo $order['order_id'] ?? ''; ?>" style="cursor: pointer;">
                                            <div class="order-info">
                                                <h3 class="order-number"><?php echo htmlspecialchars($order['orderNumber']); ?></h3>
                                                <p class="order-date"><?php echo htmlspecialchars($order['date']); ?></p>
                                                <span class="order-status status-<?php echo strtolower($order['status']); ?>">
                                                    <?php echo htmlspecialchars($order['status']); ?>
                                                </span>
                                            </div>
                                            <div class="order-details">
                                                <p class="order-items"><?php echo $order['items']; ?> item(s)</p>
                                                <p class="order-total">₵<?php echo number_format($order['total'], 2); ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                    <?php elseif ($activeTab === 'wishlist'): ?>
                        <!-- Wishlist Tab -->
                        <div class="profile-content-block">
                            <h2 class="content-block-title">Your Wishlist</h2>
                            <?php if (empty($wishlist)): ?>
                                <div class="empty-state">
                                    <i class="far fa-heart"></i>
                                    <p>No items in your wishlist yet. Start exploring to add items to your wishlist!</p>
                                </div>
                            <?php else: ?>
                                <div class="favorites-grid">
                                    <?php foreach ($wishlist as $item): ?>
                                        <div class="favorite-item">
                                            <a href="single_product.php?id=<?php echo $item['product_id']; ?>" style="text-decoration: none; color: inherit;">
                                                <div class="favorite-image">
                                                    <img src="<?php echo htmlspecialchars(get_image_path($item['image'])); ?>" 
                                                         alt="<?php echo htmlspecialchars($item['title']); ?>"
                                                         onerror="this.onerror=null; this.src='<?php echo get_image_path('../../uploads/placeholder.jpg'); ?>';">
                                                </div>
                                                <div class="favorite-content">
                                                    <h3 class="favorite-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                                                    <p class="favorite-price">₵<?php echo number_format($item['price'], 2); ?></p>
                                                    <p class="favorite-date" style="font-size: 0.85rem; color: #666; margin-top: 0.5rem;">
                                                        Added <?php echo htmlspecialchars($item['date']); ?>
                                                    </p>
                                                </div>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    
                    <?php elseif ($activeTab === 'recommended'): ?>
                        <!-- Recommended Tab -->
                        <div class="profile-content-block">
                            <h2 class="content-block-title">Recommended For You</h2>
                            <?php if (empty($recommendedContent)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-star"></i>
                                    <p>No recommendations available at the moment.</p>
                                </div>
                            <?php else: ?>
                                <div class="recommended-grid">
                                    <?php foreach ($recommendedContent as $item): ?>
                                        <div class="recommended-card">
                                            <div class="recommended-image">
                                                <img src="<?php echo htmlspecialchars(get_image_path($item['image'])); ?>" 
                                                     alt="<?php echo htmlspecialchars($item['title']); ?>"
                                                     onerror="this.onerror=null; this.src='<?php echo get_image_path('../../uploads/placeholder.jpg'); ?>';">
                                                <span class="recommended-badge"><?php echo htmlspecialchars($item['category']); ?></span>
                                            </div>
                                            <div class="recommended-content">
                                                <h3 class="recommended-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                                                <?php if (isset($item['price'])): ?>
                                                    <p class="recommended-price">₵<?php echo number_format($item['price'], 2); ?></p>
                                                <?php elseif (isset($item['date'])): ?>
                                                    <p class="recommended-date"><?php echo date('F j, Y', strtotime($item['date'])); ?></p>
                                                <?php endif; ?>
                                                <p class="recommended-description"><?php echo htmlspecialchars($item['description']); ?></p>
                                                <?php if ($item['type'] === 'product'): ?>
                                                    <a href="single_product.php?id=<?php echo $item['id']; ?>" class="btn btn-primary" style="margin-top: 1rem; display: inline-block;">
                                                        View Product
                                                    </a>
                                                <?php elseif ($item['type'] === 'article'): ?>
                                                    <a href="single_article.php?id=<?php echo $item['id']; ?>" class="btn btn-primary" style="margin-top: 1rem; display: inline-block;">
                                                        Read Article
                                                    </a>
                                                <?php elseif ($item['type'] === 'workshop'): ?>
                                                    <a href="community.php?tab=workshops" class="btn btn-primary" style="margin-top: 1rem; display: inline-block;">
                                                        View Workshop
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    
                    <?php elseif ($activeTab === 'reminders'): ?>
                        <!-- Reminder History Tab -->
                        <div class="profile-content-block">
                            <h2 class="content-block-title">Reminder History</h2>
                            <?php if (empty($reminderHistory)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-bell"></i>
                                    <p>No reminders yet. Your daily wellness reminders will appear here!</p>
                                </div>
                            <?php else: ?>
                                <div class="reminder-history-list">
                                    <?php foreach ($reminderHistory as $reminder): ?>
                                        <div class="reminder-history-item <?php echo $reminder['is_read'] ? 'read' : 'unread'; ?>">
                                            <div class="reminder-history-icon">
                                                <i class="fas fa-lightbulb"></i>
                                            </div>
                                            <div class="reminder-history-content">
                                                <h3 class="reminder-history-title"><?php echo htmlspecialchars($reminder['title']); ?></h3>
                                                <p class="reminder-history-message"><?php echo nl2br(htmlspecialchars($reminder['message'])); ?></p>
                                                <div class="reminder-history-meta">
                                                    <?php if (!empty($reminder['cat_name'])): ?>
                                                        <span class="reminder-category">
                                                            <i class="fas fa-tag"></i>
                                                            <?php echo htmlspecialchars($reminder['cat_name']); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <span class="reminder-date">
                                                        <i class="fas fa-calendar"></i>
                                                        <?php echo date('F j, Y', strtotime($reminder['sent_date'])); ?>
                                                    </span>
                                                    <span class="reminder-type">
                                                        <i class="fas fa-info-circle"></i>
                                                        <?php echo ucfirst(str_replace('_', ' ', $reminder['reminder_type'])); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <?php if (!$reminder['is_read']): ?>
                                                <span class="reminder-unread-badge">New</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    
                    <?php elseif ($activeTab === 'settings'): ?>
                        <!-- Reminder Settings Tab -->
                        <div class="profile-content-block">
                            <h2 class="content-block-title">Reminder Settings</h2>
                            <form id="reminderPreferencesForm" class="reminder-settings-form">
                                <div class="settings-section">
                                    <h3 class="settings-section-title">Reminder Frequency</h3>
                                    <div class="form-group">
                                        <label class="radio-label">
                                            <input type="radio" name="reminder_frequency" value="daily" 
                                                   <?php echo ($reminderPreferences['reminder_frequency'] ?? 'daily') === 'daily' ? 'checked' : ''; ?>>
                                            <span>Daily</span>
                                        </label>
                                        <label class="radio-label">
                                            <input type="radio" name="reminder_frequency" value="weekly"
                                                   <?php echo ($reminderPreferences['reminder_frequency'] ?? 'daily') === 'weekly' ? 'checked' : ''; ?>>
                                            <span>Weekly</span>
                                        </label>
                                        <label class="radio-label">
                                            <input type="radio" name="reminder_frequency" value="never"
                                                   <?php echo ($reminderPreferences['reminder_frequency'] ?? 'daily') === 'never' ? 'checked' : ''; ?>>
                                            <span>Never</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="settings-section">
                                    <h3 class="settings-section-title">Preferred Categories</h3>
                                    <p class="settings-help-text">Select categories you want reminders for (leave empty for all categories)</p>
                                    <div class="category-checkboxes">
                                        <?php 
                                        $selectedCategories = $reminderPreferences['preferred_categories'] ?? null;
                                        foreach ($categories as $catId => $catName): 
                                            if ($catId === 'all') continue;
                                            $isChecked = $selectedCategories === null || in_array($catId, $selectedCategories);
                                        ?>
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="preferred_categories[]" value="<?php echo $catId; ?>"
                                                       <?php echo $isChecked ? 'checked' : ''; ?>>
                                                <span><?php echo htmlspecialchars($catName); ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="settings-section">
                                    <h3 class="settings-section-title">Reminder Time</h3>
                                    <div class="form-group">
                                        <input type="time" name="reminder_time" 
                                               value="<?php 
                                                   $timeValue = $reminderPreferences['reminder_time'] ?? '09:00:00';
                                                   // HTML time input only accepts HH:MM format, so strip seconds if present
                                                   echo htmlspecialchars(substr($timeValue, 0, 5)); 
                                               ?>" 
                                               class="form-control">
                                        <small class="form-help">Choose your preferred time to receive reminders</small>
                                    </div>
                                </div>

                                <div class="settings-section">
                                    <h3 class="settings-section-title">Notification Preferences</h3>
                                    <div class="form-group">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="email_reminders_enabled" value="1"
                                                   <?php echo ($reminderPreferences['email_reminders_enabled'] ?? 0) ? 'checked' : ''; ?>>
                                            <span>Enable Email Reminders</span>
                                        </label>
                                        <small class="form-help">Receive reminders via email (coming soon)</small>
                                    </div>
                                    <div class="form-group">
                                        <small class="form-help" style="color: var(--muted-foreground);">
                                            <i class="fas fa-info-circle"></i> 
                                            Reminders will appear as notifications in the top-right corner of the page.
                                        </small>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i>
                                        Save Preferences
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                </section>

            <?php else: ?>
                <!-- Not Logged In State -->
                <section class="profile-not-logged-in">
                    <div class="not-logged-in-card">
                        <div class="not-logged-in-icon">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <h2 class="not-logged-in-title">Create an account to view your profile</h2>
                        <p class="not-logged-in-description">
                            Join Wellness 360 to track your wellness journey, participate in challenges, and access personalized content.
                        </p>
                        <div class="not-logged-in-actions">
                            <a href="register.php" class="btn btn-primary">
                                <i class="fas fa-user-plus"></i> Create Account
                            </a>
                            <span class="action-divider">or</span>
                            <a href="login.php" class="btn btn-secondary">
                                <i class="fas fa-sign-in-alt"></i> Log In
                            </a>
                        </div>
                        <p class="not-logged-in-subtext">
                            Already have an account? <a href="login.php">Log in</a> to get right back to your content.
                        </p>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </main>

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
                    <p>Subscribe to our newsletter</p>
                    <form class="newsletter-form">
                        <input type="email" placeholder="Your email" class="newsletter-input">
                        <button type="submit" class="btn btn-primary newsletter-btn">Join</button>
                    </form>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date("Y"); ?> Wellness 360. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="../js/main.js"></script>
    <script src="../js/cart_count.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/notifications.js"></script>
    <script src="../js/profile.js"></script>
    <script src="../js/wellness_chatbot.js"></script>
    <?php if ($activeTab === 'settings' || $activeTab === 'reminders'): ?>
    <script src="../js/reminder_preferences.js"></script>
    <?php endif; ?>
</body>
</html>


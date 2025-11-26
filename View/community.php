<?php
/**
 * Community View
 * Includes controller and displays data
 */

// Enable error reporting FIRST before any includes
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Clear any existing output buffer
if (ob_get_level()) {
    ob_end_clean();
}

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Functions/get_cart_count.php';

// Include Controller
require_once __DIR__ . '/../Controllers/CommunityController.php';

// Initialize controller and get data
try {
    $controller = new CommunityController();
    $data = $controller->index();
} catch (Error $e) {
    die('Fatal Error loading community: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
} catch (Exception $e) {
    die('Error loading community: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
}

// Extract data from controller
$stats = $data['stats'] ?? [];
$discussionCategories = $data['discussionCategories'] ?? [];
$discussions = $data['discussions'] ?? [];
$workshops = $data['workshops'] ?? [];
$selectedTab = $data['selectedTab'] ?? 'discussions';
$selectedCategory = $data['selectedCategory'] ?? 'all';
$placeholderImage = $data['placeholderImage'] ?? 'uploads/placeholder.jpg';
$placeholderAvatar = $placeholderImage; // Use same placeholder for avatars

// Debug: Log data (remove in production)
error_log("Community View - discussionCategories count: " . count($discussionCategories));
error_log("Community View - discussions count: " . count($discussions));
if (!empty($discussionCategories)) {
    error_log("Community View - discussionCategories: " . print_r($discussionCategories, true));
}
if (!empty($discussions)) {
    error_log("Community View - first discussion: " . print_r($discussions[0] ?? null, true));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community - Wellness 360</title>
    <link rel="stylesheet" href="../Css/style.css">
    <link rel="stylesheet" href="../Css/community.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <?php if (function_exists('is_logged_in') && is_logged_in()): ?>
        <!-- User Greeting Banner -->
        <div class="user-greeting-banner">
            <div class="container">
                <div class="greeting-content">
                    <span class="greeting-text">
                        <?php
                        // Safely obtain current user name (guard if function missing)
                        $userName = function_exists('current_user_name') ? (string) current_user_name() : '';
                        $firstName = 'User';
                        if ($userName !== '') {
                          $parts = preg_split('/\s+/', trim($userName));
                          if (!empty($parts)) $firstName = $parts[0];
                        }
                        echo 'Hello ' . htmlspecialchars($firstName) . ', get right back in!';
                        ?>
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
                        <li><a href="community.php" class="active">Community</a></li>
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

    <!-- Community Hero Section -->
    <section class="community-hero">
        <div class="container">
            <div class="community-hero-content">
                <h1 class="community-title">Community</h1>
                <p class="community-subtitle">Connect, share, and grow with Ghanaians on their wellness journey</p>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="community-stats-section">
        <div class="container">
            <div class="community-stats-grid">
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['activeMembers'] ?? 0); ?></div>
                    <div class="stat-label">Active Members</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['discussions'] ?? 0); ?></div>
                    <div class="stat-label">Discussions</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['events'] ?? 0); ?></div>
                    <div class="stat-label">Events</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tab Navigation -->
    <section class="community-tabs-section">
        <div class="container">
            <div class="community-tabs">
                <a href="community.php?tab=discussions" class="community-tab <?php echo $selectedTab === 'discussions' ? 'active' : ''; ?>">
                    <i class="fas fa-comments"></i>
                    <span>Discussions</span>
                </a>
                <a href="community.php?tab=workshops" class="community-tab <?php echo $selectedTab === 'workshops' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar"></i>
                    <span>Workshops</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Main Content Area -->
    <section class="community-content-section">
        <div class="container">
            <?php if ($selectedTab === 'discussions'): ?>
                <!-- Post Creation Form -->
                <?php if (is_logged_in()): ?>
                <div class="post-creation-card">
                    <div class="post-creation-header">
                        <div class="user-avatar-placeholder">
                            <span>You</span>
                        </div>
                        <form method="POST" action="" class="post-creation-form" id="postForm">
                            <div class="form-group">
                                <label class="form-label" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--foreground);">Category</label>
                                <div class="category-inputs-row">
                                    <div class="category-input-group">
                                        <label for="postCategoryNew" class="category-label">Type New Category</label>
                                        <input type="text" 
                                               name="category_new" 
                                               id="postCategoryNew" 
                                               class="post-category-input" 
                                               placeholder="Enter a new category name..." 
                                               autocomplete="off">
                                    </div>
                                    <div class="category-separator">
                                        <span>OR</span>
                                    </div>
                                    <div class="category-input-group">
                                        <label for="postCategorySelect" class="category-label">Select Existing Category</label>
                                        <select name="category_select" 
                                                id="postCategorySelect" 
                                                class="post-category-select">
                                            <option value="">Choose from existing...</option>
                                            <?php 
                                            // Add discussion categories (free-form text categories from existing discussions)
                                            // Note: Community categories are NOT from product category table
                                            if (isset($discussionCategories) && is_array($discussionCategories) && !empty($discussionCategories)): 
                                                foreach ($discussionCategories as $cat): 
                                                    if (!empty($cat) && is_string($cat)):
                                            ?>
                                                <option value="<?php echo htmlspecialchars($cat); ?>">
                                                    <?php echo htmlspecialchars($cat); ?>
                                                </option>
                                            <?php 
                                                    endif;
                                                endforeach;
                                            else:
                                                // Debug: Show if no categories found
                                                error_log("View: No discussion categories found. discussionCategories is: " . gettype($discussionCategories) . " | Count: " . (is_array($discussionCategories) ? count($discussionCategories) : 'N/A'));
                                            endif;
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <small class="category-hint" style="display: block; margin-top: 0.5rem; color: var(--muted-foreground); font-size: 0.75rem;">
                                    <i class="fas fa-info-circle"></i> Use either the text field to create a new category or the dropdown to select an existing one.
                                </small>
                                <!-- Hidden field to store the final category value -->
                                <input type="hidden" name="category" id="postCategory" required>
                            </div>
                            <div class="form-group">
                                <input type="text" name="title" id="postTitle" class="post-title-input" placeholder="Title of your discussion..." required>
                            </div>
                            <div class="form-group">
                                <textarea name="content" id="postContent" class="post-content-textarea" placeholder="Share your thoughts, ask questions, or start a discussion..." required></textarea>
                            </div>
                            <div class="form-group">
                                <p class="anonymous-note"><i class="fas fa-shield-alt"></i> This post is completely anonymous</p>
                                <button type="submit" class="btn btn-primary post-submit-btn" id="postSubmitBtn">
                                    <span class="btn-text">Post to Community</span>
                                    <span class="btn-loading" style="display: none;"><i class="fas fa-spinner fa-spin"></i> Posting...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php else: ?>
                <div class="post-creation-card" style="text-align: center; padding: 2rem;">
                    <p>Please <a href="login.php">log in</a> to create a discussion.</p>
                </div>
                <?php endif; ?>

                <!-- Discussions List -->
                <div class="discussions-list">
                    <?php 
                    // Debug
                    if (empty($discussions)) {
                        error_log("View: Discussions list is empty. discussions is: " . gettype($discussions) . " | Count: " . (is_array($discussions) ? count($discussions) : 'N/A'));
                    }
                    if (empty($discussions)): ?>
                        <div class="no-discussions-message">
                            <i class="fas fa-comments"></i>
                            <p>No discussions found. Be the first to start one!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($discussions as $discussion): ?>
                            <div class="discussion-card" data-discussion-id="<?php echo htmlspecialchars($discussion['id'] ?? ''); ?>">
                                <div class="discussion-header">
                                    <div class="discussion-author">
                                        <img src="../<?php echo htmlspecialchars($discussion['authorImage'] ?? $placeholderImage); ?>" 
                                             alt="Anonymous"
                                             class="author-avatar"
                                             onerror="this.onerror=null; this.style.display='none';">
                                        <div class="author-info">
                                            <span class="author-name">Anonymous</span>
                                            <span class="discussion-timestamp"><?php echo htmlspecialchars($discussion['timestamp'] ?? ''); ?></span>
                                        </div>
                                    </div>
                                    <div class="discussion-category-tag">
                                        <?php echo htmlspecialchars($discussion['categoryName'] ?? $discussion['category'] ?? 'Uncategorized'); ?>
                                    </div>
                                </div>
                                <h3 class="discussion-title"><?php echo htmlspecialchars($discussion['title'] ?? ''); ?></h3>
                                <p class="discussion-content"><?php echo htmlspecialchars($discussion['content'] ?? ''); ?></p>
                                <div class="discussion-engagement">
                                    <span class="engagement-item">
                                        <i class="fas fa-comments"></i>
                                        <span class="reply-count"><?php echo number_format($discussion['replies'] ?? 0); ?></span> replies
                                    </span>
                                    <button class="btn-reply-toggle" onclick="toggleReplies(<?php echo htmlspecialchars($discussion['id'] ?? ''); ?>)">
                                        <i class="fas fa-comment-dots"></i> View Replies
                                    </button>
                                </div>
                                
                                <!-- Replies Section -->
                                <div class="replies-section" id="replies-<?php echo htmlspecialchars($discussion['id'] ?? ''); ?>" style="display: none;">
                                    <div class="replies-list" id="replies-list-<?php echo htmlspecialchars($discussion['id'] ?? ''); ?>">
                                        <!-- Replies will be loaded here -->
                                    </div>
                                    
                                    <?php if (is_logged_in()): ?>
                                    <div class="reply-form-container">
                                        <form class="reply-form" id="replyForm-<?php echo htmlspecialchars($discussion['id'] ?? ''); ?>">
                                            <div class="reply-input-group">
                                                <textarea name="content" class="reply-input" placeholder="Write a reply..." required></textarea>
                                                <button type="submit" class="btn btn-primary btn-sm reply-submit-btn">
                                                    <span class="btn-text">Reply</span>
                                                    <span class="btn-loading" style="display: none;"><i class="fas fa-spinner fa-spin"></i></span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    <?php else: ?>
                                    <div class="reply-login-prompt">
                                        <p>Please <a href="login.php">log in</a> to reply to this discussion.</p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            <?php elseif ($selectedTab === 'workshops'): ?>
                <!-- Workshops Grid -->
                <div class="workshops-grid">
                    <?php if (empty($workshops)): ?>
                        <div class="no-discussions-message">
                            <i class="fas fa-calendar-alt"></i>
                            <p>No workshops found. Check back soon!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($workshops as $workshop): ?>
                        <div class="workshop-card">
                            <div class="workshop-image-wrapper">
                                <img src="../<?php echo htmlspecialchars($workshop['image'] ?? $placeholderImage); ?>" 
                                     alt="<?php echo htmlspecialchars($workshop['title'] ?? ''); ?>"
                                     onerror="this.onerror=null; this.style.display='none';">
                                <div class="workshop-type-badge <?php echo htmlspecialchars($workshop['type'] ?? 'virtual'); ?>">
                                    <?php 
                                    $workshopType = $workshop['type'] ?? 'virtual';
                                    if ($workshopType === 'virtual'): 
                                    ?>
                                        <i class="fas fa-video"></i> Virtual
                                    <?php else: ?>
                                        <i class="fas fa-map-marker-alt"></i> In-Person
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="workshop-content">
                                <h3 class="workshop-title"><?php echo htmlspecialchars($workshop['title'] ?? ''); ?></h3>
                                <p class="workshop-description"><?php echo htmlspecialchars($workshop['description'] ?? ''); ?></p>
                                <div class="workshop-details">
                                    <div class="workshop-detail-item">
                                        <i class="fas fa-user"></i>
                                        <span>Hosted by <?php echo htmlspecialchars($workshop['host'] ?? ''); ?></span>
                                    </div>
                                    <div class="workshop-detail-item">
                                        <i class="fas fa-calendar"></i>
                                        <span><?php echo htmlspecialchars($workshop['date'] ?? ''); ?></span>
                                    </div>
                                    <div class="workshop-detail-item">
                                        <i class="fas fa-clock"></i>
                                        <span><?php echo htmlspecialchars($workshop['time'] ?? ''); ?></span>
                                    </div>
                                    <?php if (!empty($workshop['location'])): ?>
                                        <div class="workshop-detail-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span><?php echo htmlspecialchars($workshop['location']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="workshop-registration">
                                    <div class="registration-info">
                                        <span><?php echo $workshop['registered'] ?? 0; ?>/<?php echo $workshop['capacity'] ?? 0; ?> registered</span>
                                        <div class="registration-progress-bar">
                                            <?php 
                                            $registered = $workshop['registered'] ?? 0;
                                            $capacity = $workshop['capacity'] ?? 1;
                                            $progress = $capacity > 0 ? ($registered / $capacity) * 100 : 0;
                                            ?>
                                            <div class="registration-progress" style="width: <?php echo min(100, max(0, $progress)); ?>%"></div>
                                        </div>
                                    </div>
                                    <?php if (is_logged_in()): ?>
                                        <?php if ($workshop['is_registered'] ?? false): ?>
                                            <button class="btn btn-secondary workshop-cancel-btn" 
                                                    onclick="cancelWorkshopRegistration(<?php echo $workshop['id']; ?>, <?php echo $workshop['capacity']; ?>, <?php echo $workshop['registered'] ?? 0; ?>)"
                                                    data-workshop-id="<?php echo $workshop['id']; ?>"
                                                    id="workshop-btn-<?php echo $workshop['id']; ?>">
                                                Cancel Registration
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-primary workshop-register-btn" 
                                                    onclick="registerForWorkshop(<?php echo $workshop['id']; ?>, <?php echo $workshop['capacity']; ?>, <?php echo $workshop['registered'] ?? 0; ?>)"
                                                    data-workshop-id="<?php echo $workshop['id']; ?>"
                                                    id="workshop-btn-<?php echo $workshop['id']; ?>">
                                                Register Now
                                            </button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="login.php" class="btn btn-primary workshop-register-btn">
                                            Log In to Register
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
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

    <script src="../js/main.js"></script>
    <script src="../js/wellness_chatbot.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/workshop_registration.js"></script>
    <script src="../js/cart_count.js"></script>
    <?php if ($selectedTab === 'discussions'): ?>
    <script src="../js/discussions.js"></script>
    <?php endif; ?>
</body>
</html>


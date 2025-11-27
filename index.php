<?php
require_once 'settings/core.php';
require_once 'Functions/get_cart_count.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wellness 360 - Home</title>
    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="Css/notifications.css">
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
                    <a href="Actions/logout_action.php" class="logout-link">
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
                    <a href="index.php">
                        <h1>Wellness 360</h1>
                    </a>
                </div>
                <nav class="main-nav">
                    <ul class="nav-menu">
                        <li><a href="index.php" class="active">Home</a></li>
                        <li><a href="View/wellness_hub.php">Wellness Hub</a></li>
                        <li><a href="View/shop.php">Shop</a></li>
                        <li><a href="View/community.php">Community</a></li>
                        <li><a href="View/about.php">About</a></li>
                        <li><a href="View/contact.php">Contact</a></li>
                        <li><a href="View/profile.php">Profile</a></li>
                        <li><a href="View/cart.php" class="cart-link"><i class="fas fa-shopping-cart"></i> Cart <span id="cart-count"><?php echo get_cart_count(); ?></span></a></li>
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
    // Include image paths and features from home_data.php first
    $heroImage = '../uploads/index.png';
    $placeholderImage = '../uploads/placeholder.jpg';
    
    // Features data array (static content)
    $features = [
        [
            'icon' => 'sparkles',
            'title' => 'Verified Content',
            'description' => 'Evidence-based wellness articles reviewed by health professionals',
        ],
        [
            'icon' => 'shield',
            'title' => 'Trusted Products',
            'description' => 'Curated marketplace with verified vendors and authentic reviews',
        ],
        [
            'icon' => 'users',
            'title' => 'Community Support',
            'description' => 'Connect with others on similar wellness journeys across Ghana',
        ],
    ];
    
    // Include home page data model and get real data from database
    require_once 'Classes/HomeModel.php';
    
    $homeModel = new HomeModel();
    $wellnessTips = $homeModel->getFeaturedArticles(3);
    $products = $homeModel->getTopProducts(4);
    $events = $homeModel->getUpcomingWorkshops(3);
    
    // Debug: Log what we got from database
    error_log("HomeModel - wellnessTips count: " . count($wellnessTips));
    error_log("HomeModel - products count: " . count($products));
    error_log("HomeModel - events count: " . count($events));
    ?>

    <!-- Hero Section -->
    <section class="hero-section-new">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-content-new">
                    <div class="badge-new badge-primary">
                        <i class="fas fa-sparkles"></i>
                        Ghana's #1 Digital Wellness Platform
                    </div>
                    <h1 class="hero-title-new">
                        Your Wellness, Your Way — <span class="text-primary">Anytime, Anywhere</span>
                    </h1>
                    <p class="hero-subtitle-new">
                        Discover evidence-based health content, shop trusted wellness products, and connect with a community that cares — all in one place.
                    </p>
                    <div class="hero-buttons-new">
                        <a href="View/register.php" class="btn btn-primary btn-lg">
                            Join us
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <a href="View/shop.php" class="btn btn-outline btn-lg">
                            Shop Products
                        </a>
                    </div>
                </div>
                <div class="hero-image-wrapper">
                    <div class="hero-image-container">
                        <?php // PHP: Output hero image from uploads folder ?>
                        <img src="<?php echo htmlspecialchars($heroImage); ?>" 
                             alt="Wellness meditation" 
                             class="hero-image"
                             onerror="this.onerror=null; this.style.display='none';">
                        <div class="hero-image-overlay"></div>
                    </div>
                    <!-- Floating Stats -->
                    <div class="floating-stat floating-stat-left">
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">500+</div>
                            <div class="stat-label">Active Users</div>
                        </div>
                    </div>
                    <div class="floating-stat floating-stat-right">
                        <div class="stat-icon-wrapper stat-icon-accent">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">100+</div>
                            <div class="stat-label">Articles</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section-new">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Why Choose Wellness 360?</h2>
                <p class="section-subtitle">
                    We bring together expert knowledge, trusted products, and supportive community in one seamless platform.
                </p>
            </div>
            <div class="features-grid-new">
                <?php // PHP: Loop through features and output HTML ?>
                <?php foreach ($features as $index => $feature): ?>
                    <div class="feature-card-new">
                        <div class="feature-icon-wrapper">
                            <?php
                            // PHP: Determine icon class based on feature icon type
                            $iconClass = '';
                            switch($feature['icon']) {
                                case 'sparkles':
                                    $iconClass = 'fa-sparkles';
                                    break;
                                case 'shield':
                                    $iconClass = 'fa-shield-alt';
                                    break;
                                case 'users':
                                    $iconClass = 'fa-users';
                                    break;
                            }
                            ?>
                            <?php // HTML: Icon element with PHP-generated class ?>
                            <i class="fas <?php echo $iconClass; ?>"></i>
                        </div>
                        <?php // PHP: Output feature title ?>
                        <h3><?php echo htmlspecialchars($feature['title']); ?></h3>
                        <?php // PHP: Output feature description ?>
                        <p><?php echo htmlspecialchars($feature['description']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Wellness Tips Section -->
    <section class="wellness-tips-section">
        <div class="container">
            <div class="section-header-flex">
                <div>
                    <h2 class="section-title">Featured Wellness Tips</h2>
                    <p class="section-subtitle">Expert advice for your health journey</p>
                </div>
                <a href="View/wellness_hub.php" class="btn btn-outline">
                    View All
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="tips-grid">
                <?php if (empty($wellnessTips)): ?>
                    <div class="tip-card">
                        <div class="tip-content">
                            <p>No articles available at the moment.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($wellnessTips as $tip): ?>
                        <div class="tip-card">
                            <div class="tip-image-wrapper">
                                <a href="View/single_article.php?id=<?php echo $tip['article_id']; ?>">
                                    <img src="<?php echo htmlspecialchars($tip['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($tip['title']); ?>"
                                         onerror="this.onerror=null; this.src='<?php echo $placeholderImage; ?>';">
                                </a>
                                <div class="tip-badge"><?php echo htmlspecialchars($tip['category']); ?></div>
                            </div>
                            <div class="tip-content">
                                <a href="View/single_article.php?id=<?php echo $tip['article_id']; ?>" style="text-decoration: none; color: inherit;">
                                    <h3><?php echo htmlspecialchars($tip['title']); ?></h3>
                                </a>
                                <div class="tip-meta">
                                    <span>By <?php echo htmlspecialchars($tip['author']); ?></span>
                                    <span><?php echo htmlspecialchars($tip['readTime']); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Top Products Section -->
    <section class="products-section">
        <div class="container">
            <div class="section-header-flex">
                <div>
                    <h2 class="section-title">Top Wellness Products</h2>
                    <p class="section-subtitle">Verified vendors, authentic reviews</p>
                </div>
                <a href="View/shop.php" class="btn btn-outline">
                    Browse Shop
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="products-grid">
                <?php if (empty($products)): ?>
                    <div class="product-card">
                        <div class="product-content">
                            <p>No products available at the moment.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image-wrapper">
                                <a href="View/single_product.php?id=<?php echo $product['product_id']; ?>">
                                    <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         onerror="this.onerror=null; this.src='<?php echo $placeholderImage; ?>';">
                                </a>
                                <?php if ($product['verified']): ?>
                                    <div class="product-verified-badge">
                                        <i class="fas fa-shield-alt"></i>
                                        Verified
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="product-content">
                                <a href="View/single_product.php?id=<?php echo $product['product_id']; ?>" style="text-decoration: none; color: inherit;">
                                    <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                </a>
                                <p class="product-vendor"><?php echo htmlspecialchars($product['vendor']); ?></p>
                                <div class="product-footer">
                                    <span class="product-price"><?php echo htmlspecialchars($product['price']); ?></span>
                                    <div class="product-rating">
                                        <i class="fas fa-heart"></i>
                                        <span><?php echo $product['rating']; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Events Section -->
    <section class="events-section">
        <div class="container">
            <div class="section-header-flex">
                <div>
                    <h2 class="section-title">Upcoming Events</h2>
                    <p class="section-subtitle">Join workshops, challenges, and community gatherings</p>
                </div>
                <a href="View/community.php?tab=workshops" class="btn btn-outline">
                    View All Events
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="events-grid">
                <?php if (empty($events)): ?>
                    <div class="event-card">
                        <div class="event-content">
                            <p>No upcoming events at the moment.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($events as $event): ?>
                        <div class="event-card">
                            <div class="event-badge"><?php echo htmlspecialchars($event['type']); ?></div>
                            <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                            <div class="event-details">
                                <div class="event-detail-item">
                                    <i class="fas fa-calendar"></i>
                                    <span><?php echo htmlspecialchars($event['date']); ?> • <?php echo htmlspecialchars($event['time']); ?></span>
                                </div>
                                <div class="event-detail-item">
                                    <i class="fas fa-users"></i>
                                    <span><?php echo $event['attendees']; ?> attending</span>
                                </div>
                            </div>
                            <a href="View/community.php?tab=workshops" class="btn btn-outline btn-full">Register Now</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section-new">
        <div class="container">
            <div class="cta-content-new">
                <i class="fas fa-leaf cta-icon"></i>
                <h2>Ready to Start Your Wellness Journey?</h2>
                <p>
                    Join thousands of Ghanaians transforming their health with trusted resources, quality products, and a supportive community.
                </p>
                <a href="View/register.php" class="btn btn-secondary btn-lg">
                    Create Your Profile
                    <i class="fas fa-arrow-right"></i>
                </a>
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
                        <li><a href="index.php">Home</a></li>
                        <li><a href="View/wellness_hub.php">Wellness Hub</a></li>
                        <li><a href="View/shop.php">Shop</a></li>
                        <li><a href="View/community.php">Community</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Resources</h4>
                    <ul>
                        <li><a href="View/about.php">About Us</a></li>
                        <li><a href="View/contact.php">Contact</a></li>
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

    <script src="js/main.js"></script>
    <script src="js/notifications.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/cart.js"></script>
    
    <?php if (is_logged_in()): ?>
    <script>
    // Daily Reminder System - Show at scheduled time
    let reminderCheckInterval = null;
    
    async function loadDailyReminder() {
        try {
            // First, get user preferences to check reminder time
            const prefsResponse = await fetch('Actions/get_reminder_preferences_action.php');
            const prefsResult = await prefsResponse.json();
            
            if (!prefsResult.status || !prefsResult.preferences) {
                return; // No preferences found
            }
            
            const preferences = prefsResult.preferences;
            
            // Check if reminders are enabled
            if (preferences.reminder_frequency === 'never') {
                return; // Reminders disabled
            }
            
            // Check frequency
            if (preferences.reminder_frequency === 'weekly') {
                const dayOfWeek = new Date().getDay(); // 0 = Sunday, 1 = Monday
                if (dayOfWeek !== 1) {
                    return; // Not Monday, skip
                }
            }
            
            // Get reminder time (format: HH:MM:SS) - MUST be defined before using it
            const reminderTime = preferences.reminder_time || '09:00:00';
            
            // Check if reminder was already shown today (but allow if time has passed and we're checking again)
            const today = new Date().toDateString();
            const lastShownDate = localStorage.getItem('reminder_last_shown_date');
            const lastShownTime = localStorage.getItem('reminder_last_shown_time');
            
            // If already shown today, check if we should show again (e.g., if user changed time)
            if (lastShownDate === today && lastShownTime) {
                // Only skip if the reminder time hasn't changed
                if (lastShownTime === reminderTime) {
                    console.log('[Reminder] Already shown today at', lastShownTime);
                    return; // Already shown today with same time
                } else {
                    console.log('[Reminder] Time changed from', lastShownTime, 'to', reminderTime, '- will show again');
                    // Clear the old record to allow showing again
                    localStorage.removeItem('reminder_last_shown_date');
                    localStorage.removeItem('reminder_last_shown_time');
                }
            }
            const [hours, minutes] = reminderTime.split(':').map(Number);
            
            // Get current time
            const now = new Date();
            const currentHours = now.getHours();
            const currentMinutes = now.getMinutes();
            
            // Calculate time in minutes for comparison
            const reminderMinutes = hours * 60 + minutes;
            const currentMinutesTotal = currentHours * 60 + currentMinutes;
            
            // Only show if current time is at or past reminder time
            if (currentMinutesTotal < reminderMinutes) {
                // Not time yet, set up a timer to check again
                const minutesUntilReminder = reminderMinutes - currentMinutesTotal;
                const msUntilReminder = minutesUntilReminder * 60 * 1000;
                
                console.log(`[Reminder] Scheduled for ${reminderTime}. Current time: ${currentHours}:${String(currentMinutes).padStart(2, '0')}. Will check again in ${minutesUntilReminder} minutes.`);
                
                // Set up interval to check every minute
                if (reminderCheckInterval) {
                    clearInterval(reminderCheckInterval);
                }
                
                // Set up interval to check every minute (more reliable than setTimeout)
                reminderCheckInterval = setInterval(checkReminderTime, 60000); // Check every minute
                
                // Also set a timeout for the exact time as backup
                setTimeout(function() {
                    console.log('[Reminder] Timeout reached, checking reminder...');
                    checkReminderTime();
                }, msUntilReminder);
                
                return;
            }
            
            // Time has passed, fetch and show reminder
            console.log(`[Reminder] Time has passed (${currentHours}:${String(currentMinutes).padStart(2, '0')} >= ${reminderTime}). Fetching reminder...`);
            const response = await fetch('Actions/get_daily_reminder_action.php');
            const result = await response.json();
            
            console.log('[Reminder] Response:', result);
            
            if (result.status && result.reminder) {
                const reminder = result.reminder;
                const title = reminder.title || 'Daily Wellness Reminder';
                const message = reminder.message || 'Stay motivated on your wellness journey!';
                
                console.log(`[Reminder] Showing reminder: ${title}`);
                
                // Show as in-page notification in top-right corner (20 seconds)
                if (window.notifications) {
                    window.notifications.info(`<strong>${title}</strong><br>${message}`, 20000);
                } else {
                    console.warn('[Reminder] Notification system not available');
                }
                
                // Mark as shown today with the reminder time
                localStorage.setItem('reminder_last_shown_date', today);
                localStorage.setItem('reminder_last_shown_time', reminderTime);
                
                // Mark as read when displayed
                if (reminder.id && !reminder.is_read) {
                    markReminderAsRead(reminder.id);
                }
                
                // Clear interval since reminder was shown
                if (reminderCheckInterval) {
                    clearInterval(reminderCheckInterval);
                    reminderCheckInterval = null;
                }
            } else {
                console.log('[Reminder] No reminder available:', result.message || 'Unknown reason');
            }
        } catch (error) {
            console.error('Error loading daily reminder:', error);
        }
    }
    
    function checkReminderTime() {
        // Re-check if it's time to show reminder
        console.log('[Reminder] Checking if it\'s time for reminder...');
        loadDailyReminder();
    }
    
    async function markReminderAsRead(reminderId) {
        try {
            await fetch('Actions/mark_reminder_read_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ reminder_id: reminderId })
            });
        } catch (error) {
            console.error('Error marking reminder as read:', error);
        }
    }
    
    // Load reminder when page loads
    document.addEventListener('DOMContentLoaded', function() {
        console.log('[Reminder] System initialized');
        setTimeout(loadDailyReminder, 1000);
    });
    
    // Also check when page becomes visible (user switches back to tab)
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            console.log('[Reminder] Page became visible, checking reminder...');
            loadDailyReminder();
        }
    });
    
    // Clean up interval when page unloads
    window.addEventListener('beforeunload', function() {
        if (reminderCheckInterval) {
            clearInterval(reminderCheckInterval);
        }
    });
    </script>
    <?php endif; ?>
    <script src="js/wellness_chatbot.js"></script>
</body>
</html>


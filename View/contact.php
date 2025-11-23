<?php
/**
 * Contact View
 * Includes controller and displays data
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Functions/get_cart_count.php';

// Include Controller
require_once __DIR__ . '/../Controllers/ContactController.php';

// Initialize controller and get data
$controller = new ContactController();
$data = $controller->index();

// Extract data from controller
$contactMethods = $data['contactMethods'] ?? [];
$supportHours = $data['supportHours'] ?? [];
$faqs = $data['faqs'] ?? [];
$chatMessages = $data['chatMessages'] ?? [];
$quickActions = $data['quickActions'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact & Support - Wellness 360</title>
    <link rel="stylesheet" href="../Css/style.css">
    <link rel="stylesheet" href="../Css/contact.css">
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
                        <li><a href="about.php">About</a></li>
                        <li><a href="contact.php" class="active">Contact</a></li>
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

    <!-- Contact Hero Section -->
    <section class="contact-hero">
        <div class="container">
            <div class="contact-hero-content">
                <h1 class="contact-title">Contact & Support</h1>
                <p class="contact-subtitle">We're here to help! Reach out through any of these channels.</p>
            </div>
        </div>
    </section>

    <!-- Contact Methods Section -->
    <section class="contact-methods-section">
        <div class="container">
            <div class="contact-methods-grid">
                <?php foreach ($contactMethods as $method): ?>
                    <div class="contact-method-card">
                        <div class="contact-method-icon">
                            <?php if ($method['icon'] === 'envelope'): ?>
                                <i class="fas fa-envelope"></i>
                            <?php elseif ($method['icon'] === 'phone'): ?>
                                <i class="fas fa-phone"></i>
                            <?php elseif ($method['icon'] === 'map-marker-alt'): ?>
                                <i class="fas fa-map-marker-alt"></i>
                            <?php endif; ?>
                        </div>
                        <h3 class="contact-method-title"><?php echo htmlspecialchars($method['title']); ?></h3>
                        <p class="contact-method-value"><?php echo htmlspecialchars($method['value']); ?></p>
                        <p class="contact-method-note"><?php echo htmlspecialchars($method['note']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Contact Form and Chat Section -->
    <section class="contact-form-chat-section">
        <div class="container">
            <div class="contact-form-chat-grid">
                <!-- Contact Form -->
                <div class="contact-form-wrapper">
                    <h2 class="section-form-title">Send Us a Message</h2>
                    <form method="POST" action="" class="contact-form" id="contactForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="firstName">Your first name</label>
                                <input type="text" id="firstName" name="firstName" placeholder="Your first name" required>
                            </div>
                            <div class="form-group">
                                <label for="lastName">Your last name</label>
                                <input type="text" id="lastName" name="lastName" placeholder="Your last name" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" placeholder="your.email@example.com" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" placeholder="+233 XX XXX XXXX">
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" placeholder="What's this about?" required>
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="5" placeholder="Tell us how we can help..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary contact-submit-btn">
                            <i class="fas fa-paper-plane"></i>
                            Send Message
                        </button>
                    </form>
                </div>

                <!-- Live Chat Support -->
                <div class="live-chat-wrapper">
                    <div class="chat-header">
                        <h2 class="section-form-title">Live Chat Support</h2>
                        <div class="chat-status">
                            <span class="status-dot"></span>
                            <span>Online</span>
                        </div>
                    </div>
                    <div class="chat-messages" id="chatMessages">
                        <?php foreach ($chatMessages as $chat): ?>
                            <div class="chat-message chat-message-<?php echo $chat['sender']; ?>">
                                <p><?php echo htmlspecialchars($chat['message']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="chat-quick-actions">
                        <?php foreach ($quickActions as $action): ?>
                            <button type="button" class="quick-action-btn"><?php echo htmlspecialchars($action); ?></button>
                        <?php endforeach; ?>
                    </div>
                    <div class="chat-input-wrapper">
                        <input type="text" class="chat-input" placeholder="Type your message...">
                        <button type="button" class="chat-send-btn">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <div class="faq-header">
                <div class="faq-icon-wrapper">
                    <i class="fas fa-question-circle"></i>
                </div>
                <div class="faq-header-text">
                    <h2 class="faq-title">Frequently Asked Questions</h2>
                    <p class="faq-subtitle">Find quick answers to common questions</p>
                </div>
            </div>
            <div class="faq-list">
                <?php foreach ($faqs as $index => $faq): ?>
                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(<?php echo $index; ?>)">
                            <span><?php echo htmlspecialchars($faq['question']); ?></span>
                            <i class="fas fa-chevron-down faq-chevron" id="faqChevron<?php echo $index; ?>"></i>
                        </button>
                        <div class="faq-answer" id="faqAnswer<?php echo $index; ?>">
                            <p><?php echo htmlspecialchars($faq['answer']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Support Hours Section -->
    <section class="support-hours-section">
        <div class="container">
            <div class="support-hours-card">
                <div class="support-hours-content">
                    <div class="support-hours-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="support-hours-info">
                        <h3 class="support-hours-title"><?php echo htmlspecialchars($supportHours['title']); ?></h3>
                        <div class="support-hours-list">
                            <?php foreach ($supportHours['hours'] as $hour): ?>
                                <p><?php echo htmlspecialchars($hour); ?></p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="support-hours-additional">
                        <?php foreach ($supportHours['additional'] as $item): ?>
                            <p><?php echo htmlspecialchars($item); ?></p>
                        <?php endforeach; ?>
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
                        <li><a href="#faq">FAQs</a></li>
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
                <p>&copy; <?php echo date("Y"); ?> Wellness 360. All rights reserved. Made with ❤️ in Ghana</p>
            </div>
        </div>
    </footer>

    <script src="../js/main.js"></script>
    <script>
    // FAQ Accordion Functionality
    function toggleFaq(index) {
        const answer = document.getElementById('faqAnswer' + index);
        const chevron = document.getElementById('faqChevron' + index);
        const faqItem = answer.closest('.faq-item');
        
        // Close all other FAQs
        document.querySelectorAll('.faq-item').forEach((item, i) => {
            if (i !== index) {
                const otherAnswer = item.querySelector('.faq-answer');
                const otherChevron = item.querySelector('.faq-chevron');
                if (otherAnswer && otherChevron) {
                    otherAnswer.style.maxHeight = null;
                    otherAnswer.classList.remove('active');
                    otherChevron.style.transform = 'rotate(0deg)';
                }
            }
        });
        
        // Toggle current FAQ
        if (answer.style.maxHeight) {
            answer.style.maxHeight = null;
            answer.classList.remove('active');
            chevron.style.transform = 'rotate(0deg)';
        } else {
            answer.style.maxHeight = answer.scrollHeight + 'px';
            answer.classList.add('active');
            chevron.style.transform = 'rotate(180deg)';
        }
    }
    </script>
    <script src="../js/cart_count.js"></script>
</body>
</html>


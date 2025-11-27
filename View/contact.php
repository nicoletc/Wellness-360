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
    <link rel="stylesheet" href="../Css/contact.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
                    <h2 class="section-form-title">Tell us about your experience</h2>
                    <form method="POST" action="" class="contact-form" id="contactForm">
                        <div id="contactFormMessage" style="display: none; margin-bottom: 1rem; padding: 1rem; border-radius: 8px;"></div>
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

                <!-- Quick Help Section -->
                <div class="quick-help-wrapper">
                    <div class="quick-help-header">
                        <h2 class="section-form-title">Quick Help</h2>
                        <p class="quick-help-subtitle">Find answers to common questions</p>
                    </div>
                    <div class="quick-help-content">
                        <div class="quick-help-item">
                            <div class="quick-help-icon">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div class="quick-help-info">
                                <h3>AI Wellness Assistant</h3>
                                <p>Chat with our AI assistant for instant answers about wellness, products, and services.</p>
                                <button type="button" class="quick-help-btn" onclick="document.getElementById('wellnessChatbotToggle')?.click();">
                                    Start Chat <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                        <div class="quick-help-item">
                            <div class="quick-help-icon">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <div class="quick-help-info">
                                <h3>Frequently Asked Questions</h3>
                                <p>Browse our FAQ section below for answers to the most common questions.</p>
                                <a href="#faq-section" class="quick-help-btn">
                                    View FAQs <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="quick-help-item">
                            <div class="quick-help-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="quick-help-info">
                                <h3>Wellness Resources</h3>
                                <p>Explore our Wellness Hub for articles, guides, and expert advice on health and wellness.</p>
                                <a href="wellness_hub.php" class="quick-help-btn">
                                    Visit Hub <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq-section" class="faq-section">
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
                        <li><i class="fas fa-envelope"></i> wellnessallround@gmail.com</li>
                        <li><i class="fas fa-phone"></i> 0204567321</li>
                        <li><i class="fas fa-map-marker-alt"></i> 3rd Circular rd, Tema</li>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/notifications.js"></script>
    <script src="../js/wellness_chatbot.js"></script>
    <script>
    // Contact Form Submission
    document.getElementById('contactForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const form = this;
        const submitBtn = form.querySelector('.contact-submit-btn');
        const messageDiv = document.getElementById('contactFormMessage');
        const originalBtnText = submitBtn.innerHTML;
        
        // Disable button and show loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        messageDiv.style.display = 'none';
        
        // Get form data
        const formData = new FormData(form);
        
        try {
            const response = await fetch('../Actions/submit_contact_message_action.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.status) {
                // Show success message with Sweet Alert
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Message Sent!',
                        text: result.message || 'Your message has been sent successfully! We will get back to you soon.',
                        timer: 4000,
                        showConfirmButton: true,
                        confirmButtonColor: '#7FB685'
                    });
                } else if (window.notifications) {
                    window.notifications.success(result.message || 'Your message has been sent successfully!', 5000);
                } else {
                    messageDiv.style.display = 'block';
                    messageDiv.style.background = '#d4edda';
                    messageDiv.style.color = '#155724';
                    messageDiv.style.border = '1px solid #c3e6cb';
                    messageDiv.textContent = result.message || 'Your message has been sent successfully!';
                }
                
                // Reset form
                form.reset();
            } else {
                // Show error message with Sweet Alert
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.message || 'Failed to send message. Please try again.',
                        confirmButtonColor: '#7FB685'
                    });
                } else if (window.notifications) {
                    window.notifications.error(result.message || 'Failed to send message. Please try again.', 5000);
                } else {
                    messageDiv.style.display = 'block';
                    messageDiv.style.background = '#f8d7da';
                    messageDiv.style.color = '#721c24';
                    messageDiv.style.border = '1px solid #f5c6cb';
                    messageDiv.textContent = result.message || 'Failed to send message. Please try again.';
                }
            }
        } catch (error) {
            console.error('Error submitting contact form:', error);
            if (window.notifications) {
                window.notifications.error('An error occurred. Please try again.', 5000);
            } else if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred. Please try again.'
                });
            } else {
                messageDiv.style.display = 'block';
                messageDiv.style.background = '#f8d7da';
                messageDiv.style.color = '#721c24';
                messageDiv.style.border = '1px solid #f5c6cb';
                messageDiv.textContent = 'An error occurred. Please try again.';
            }
        } finally {
            // Re-enable button
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    });
    
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


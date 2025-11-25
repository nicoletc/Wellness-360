<?php
/**
 * Payment Success Page
 * Displays order confirmation after successful payment
 */

require_once __DIR__ . '/../settings/core.php';

// Get order details from URL parameters
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$invoice_no = isset($_GET['invoice_no']) ? htmlspecialchars($_GET['invoice_no']) : '';
$total = isset($_GET['total']) ? htmlspecialchars($_GET['total']) : '0.00';
$reference = isset($_GET['reference']) ? htmlspecialchars($_GET['reference']) : '';

// If no order ID, redirect to home
if ($order_id <= 0) {
    redirect('index.php');
}

// Get customer name
$customer_name = current_user_name();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - Wellness 360</title>
    <link rel="stylesheet" href="../Css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <style>
        .success-container {
            min-height: 70vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: linear-gradient(135deg, #f5f1eb 0%, #e8dcc4 100%);
        }
        .success-card {
            background: white;
            border-radius: 16px;
            padding: 3rem;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .success-icon {
            width: 100px;
            height: 100px;
            background: #7FB685;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            animation: scaleIn 0.5s ease-out;
        }
        .success-icon i {
            font-size: 3rem;
            color: white;
        }
        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
        .success-title {
            font-size: 2rem;
            color: var(--foreground);
            margin-bottom: 1rem;
            font-weight: 600;
        }
        .success-message {
            color: var(--muted-foreground);
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }
        .order-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: left;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 500;
            color: var(--muted-foreground);
        }
        .detail-value {
            color: var(--foreground);
            font-weight: 600;
        }
        .success-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-success-action {
            padding: 0.875rem 2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-primary-action {
            background: var(--primary);
            color: var(--primary-foreground);
        }
        .btn-primary-action:hover {
            background: #6fa875;
            transform: translateY(-2px);
        }
        .btn-outline-action {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }
        .btn-outline-action:hover {
            background: var(--primary);
            color: var(--primary-foreground);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="../index.php">
                        <h1>Wellness 360</h1>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Success Section -->
    <section class="success-container">
        <div class="success-card">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            
            <h1 class="success-title">Payment Successful!</h1>
            <p class="success-message">
                Thank you, <?php echo htmlspecialchars(explode(' ', $customer_name)[0]); ?>! Your order has been confirmed.
            </p>
            
            <div class="order-details">
                <div class="detail-row">
                    <span class="detail-label">Order ID:</span>
                    <span class="detail-value">#<?php echo $order_id; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Invoice Number:</span>
                    <span class="detail-value"><?php echo $invoice_no; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Amount:</span>
                    <span class="detail-value">₵<?php echo number_format((float)$total, 2); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Reference:</span>
                    <span class="detail-value"><?php echo $reference; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date:</span>
                    <span class="detail-value"><?php echo date('F j, Y'); ?></span>
                </div>
            </div>
            
            <div class="success-actions">
                <a href="profile.php?tab=orders" class="btn-success-action btn-primary-action">
                    <i class="fas fa-list"></i>
                    View Orders
                </a>
                <a href="../index.php" class="btn-success-action btn-outline-action">
                    <i class="fas fa-home"></i>
                    Continue Shopping
                </a>
            </div>
        </div>
    </section>

    <script>
        // Trigger confetti animation
        function triggerConfetti() {
            const duration = 3000;
            const animationEnd = Date.now() + duration;
            const defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 0 };

            function randomInRange(min, max) {
                return Math.random() * (max - min) + min;
            }

            const interval = setInterval(function() {
                const timeLeft = animationEnd - Date.now();

                if (timeLeft <= 0) {
                    return clearInterval(interval);
                }

                const particleCount = 50 * (timeLeft / duration);
                
                if (typeof confetti !== 'undefined') {
                    confetti({
                        ...defaults,
                        particleCount,
                        origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 }
                    });
                    confetti({
                        ...defaults,
                        particleCount,
                        origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 }
                    });
                }
            }, 250);
        }

        // Trigger confetti on page load
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(triggerConfetti, 300);
        });
    </script>
</body>
</html>


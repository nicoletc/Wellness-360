<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - Wellness 360</title>
    <link rel="stylesheet" href="../Css/register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <!-- Left Panel - Form -->
            <div class="register-form-panel">
                <div class="form-content">
                    <div class="logo-section">
                        <h1 class="brand-logo reveal">Wellness 360</h1>
                    </div>
                    
                    <div class="form-header">
                        <h2 class="form-title blur-words">Welcome back!</h2>
                        <p class="form-subtitle reveal">Get back to your wellness journey.</p>
                    </div>
                    
                    <div class="login-link-top">
                        <p class="reveal">New here? <a href="register.php">Sign up</a></p>
                    </div>
                    
                    <form method="POST" action="" class="register-form reveal" id="loginForm">
                        <div class="form-group">
                            <input type="email" id="customer_email" name="customer_email" placeholder="Email" required maxlength="100">
                        </div>
                        
                        <div class="form-group">
                            <input type="password" id="customer_pass" name="customer_pass" placeholder="Password" required>
                        </div>
                        
                        <div class="form-options">
                            <label class="remember-me">
                                <input type="checkbox" name="remember">
                                <span>Remember me</span>
                            </label>
                            <a href="#" class="forgot-password">Forgot password?</a>
                        </div>
                        
                        <button type="submit" class="btn-signup" id="submitBtn">
                            <span class="btn-text">Log in</span>
                            <span class="btn-loader" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i> Logging in...
                            </span>
                        </button>
                        
                        <p class="legal-text">By logging in, you agree to the <a href="#">Terms & Conditions</a>.</p>
                    </form>
                </div>
            </div>
            
            <!-- Right Panel - Image -->
            <div class="register-image-panel">
                <div class="image-content">
                    <img src="../../uploads/placeholder.jpg" alt="Wellness Lifestyle" class="register-image reveal">
                </div>
            </div>
        </div>
    </div>
    
    <script src="../js/register-animations.js"></script>
    <script src="../js/login.js"></script>
</body>
</html>


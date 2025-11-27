<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Wellness 360</title>
    <link rel="stylesheet" href="../Css/register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.3/build/css/intlTelInput.css">
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
                        <h2 class="form-title blur-words">Hello there.</h2>
                        <p class="form-subtitle reveal">Create an account to get started.</p>
                    </div>
                    
                    <div class="login-link-top">
                        <p class="reveal">Already a member? <a href="login.php">Log in</a></p>
                    </div>
                    
                    <form method="POST" action="" class="register-form reveal" id="registerForm">
                        <div class="form-group">
                            <input type="text" id="customer_name" name="customer_name" placeholder="Full Name" required maxlength="100">
                        </div>
                        
                        <div class="form-group">
                            <input type="email" id="customer_email" name="customer_email" placeholder="Email" required maxlength="100">
                            <div id="email-feedback" class="field-feedback"></div>
                        </div>
                        
                        <div class="form-group">
                            <input type="password" id="customer_pass" name="customer_pass" placeholder="Password" required>
                            <div id="password-requirements" class="password-requirements">
                                <ul class="requirements-list">
                                    <li id="req-length" class="requirement-item">
                                        <i class="fas fa-times"></i>
                                        <span>At least 8 characters</span>
                                    </li>
                                    <li id="req-uppercase" class="requirement-item">
                                        <i class="fas fa-times"></i>
                                        <span>One uppercase letter</span>
                                    </li>
                                    <li id="req-lowercase" class="requirement-item">
                                        <i class="fas fa-times"></i>
                                        <span>One lowercase letter</span>
                                    </li>
                                    <li id="req-number" class="requirement-item">
                                        <i class="fas fa-times"></i>
                                        <span>One number</span>
                                    </li>
                                    <li id="req-special" class="requirement-item">
                                        <i class="fas fa-times"></i>
                                        <span>One special character</span>
                                    </li>
                                </ul>
                                <div id="password-strength" class="password-strength"></div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm Password" required>
                            <div id="password-match" class="field-feedback"></div>
                        </div>
                        
                        <div class="form-group">
                            <input type="tel" id="customer_contact" name="customer_contact" placeholder="Phone Number" required maxlength="20">
                        </div>
                        
                        <button type="submit" class="btn-signup" id="submitBtn">
                            <span class="btn-text">Sign up</span>
                            <span class="btn-loader" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i> Processing...
                            </span>
                        </button>
                        
                        <p class="legal-text">By signing up, you agree to the <a href="#">Terms & Conditions</a>.</p>
                    </form>
                </div>
            </div>
            
            <!-- Right Panel - Image -->
            <div class="register-image-panel">
                <div class="image-content">
                    <img src="../../uploads/signup.png" alt="Wellness Lifestyle" class="register-image reveal">
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.3/build/js/intlTelInput.min.js"></script>
    <script src="../js/register-animations.js"></script>
    <script src="../js/register.js"></script>
</body>
</html>


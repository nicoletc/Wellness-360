/**
 * Registration Form Validation and Submission
 * Handles password requirements, form validation, and async submission
 */

// Initialize phone number input with country selector
let iti;
document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.querySelector("#customer_contact");
    if (phoneInput) {
        iti = window.intlTelInput(phoneInput, {
            initialCountry: "gh",
            preferredCountries: ["gh", "us", "uk", "ng"],
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.3/build/js/utils.js"
        });
    }

    // Password requirements visibility
    const passwordInput = document.getElementById('customer_pass');
    const passwordRequirements = document.getElementById('password-requirements');
    
    if (passwordInput && passwordRequirements) {
        passwordInput.addEventListener('focus', function() {
            passwordRequirements.style.display = 'block';
        });

        passwordInput.addEventListener('blur', function() {
            // Keep visible if password has value
            if (passwordInput.value.length > 0) {
                passwordRequirements.style.display = 'block';
            } else {
                passwordRequirements.style.display = 'none';
            }
        });

        // Real-time password validation
        passwordInput.addEventListener('input', function() {
            validatePasswordRequirements(this.value);
        });
    }

    // Confirm password matching
    const confirmPasswordInput = document.getElementById('confirmPassword');
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            checkPasswordMatch();
        });
    }

    // Email availability check (debounced)
    const emailInput = document.getElementById('customer_email');
    let emailCheckTimeout;
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            const email = this.value.trim();
            if (email && validateEmail(email)) {
                clearTimeout(emailCheckTimeout);
                emailCheckTimeout = setTimeout(() => {
                    checkEmailAvailability(email);
                }, 500);
            }
        });
    }

    // Form submission
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', handleFormSubmit);
    }
});

/**
 * Validate password requirements
 */
function validatePasswordRequirements(password) {
    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
    };

    // Update requirement indicators
    updateRequirement('req-length', requirements.length);
    updateRequirement('req-uppercase', requirements.uppercase);
    updateRequirement('req-lowercase', requirements.lowercase);
    updateRequirement('req-number', requirements.number);
    updateRequirement('req-special', requirements.special);

    // Calculate password strength
    const metCount = Object.values(requirements).filter(Boolean).length;
    const strengthElement = document.getElementById('password-strength');
    
    if (strengthElement) {
        if (metCount === 5) {
            strengthElement.textContent = 'Strong password ✓';
            strengthElement.className = 'password-strength strong';
        } else if (metCount >= 3) {
            strengthElement.textContent = 'Medium strength';
            strengthElement.className = 'password-strength medium';
        } else if (metCount > 0) {
            strengthElement.textContent = 'Weak password';
            strengthElement.className = 'password-strength weak';
        } else {
            strengthElement.textContent = '';
            strengthElement.className = 'password-strength';
        }
    }

    return Object.values(requirements).every(Boolean);
}

/**
 * Update requirement indicator
 */
function updateRequirement(id, met) {
    const element = document.getElementById(id);
    if (element) {
        const icon = element.querySelector('i');
        if (icon) {
            if (met) {
                icon.className = 'fas fa-check';
                element.classList.add('met');
                element.classList.remove('unmet');
            } else {
                icon.className = 'fas fa-times';
                element.classList.add('unmet');
                element.classList.remove('met');
            }
        }
    }
}

/**
 * Check if passwords match
 */
function checkPasswordMatch() {
    const password = document.getElementById('customer_pass').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const matchFeedback = document.getElementById('password-match');

    if (confirmPassword.length === 0) {
        if (matchFeedback) {
            matchFeedback.textContent = '';
            matchFeedback.className = 'field-feedback';
        }
        return;
    }

    if (matchFeedback) {
        if (password === confirmPassword) {
            matchFeedback.textContent = '✓ Passwords match';
            matchFeedback.className = 'field-feedback success';
        } else {
            matchFeedback.textContent = '✗ Passwords do not match';
            matchFeedback.className = 'field-feedback error';
        }
    }
}

/**
 * Validate email format
 */
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Check email availability
 * Validates email format and checks if email exists in database
 */
async function checkEmailAvailability(email) {
    const feedback = document.getElementById('email-feedback');
    if (!feedback) return;

    // Validate email format in JavaScript (client-side validation)
    if (!email || email.trim().length === 0) {
        feedback.textContent = '';
        feedback.className = 'field-feedback';
        return;
    }

    if (!validateEmail(email)) {
        feedback.textContent = '✗ Invalid email format';
        feedback.className = 'field-feedback error';
        return;
    }

    if (email.length > 100) {
        feedback.textContent = '✗ Email must be less than 100 characters';
        feedback.className = 'field-feedback error';
        return;
    }

    try {
        // Call registration action with check_email flag
        const formData = new FormData();
        formData.append('check_email', 'true');
        formData.append('email', email);

        const response = await fetch('../Actions/register_customer_action.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        
        if (result.status) {
            // status true means email exists
            feedback.textContent = '✗ Email already exists';
            feedback.className = 'field-feedback error';
        } else {
            // status false means email is available
            feedback.textContent = '✓ Email is available';
            feedback.className = 'field-feedback success';
        }
    } catch (error) {
        console.error('Email check error:', error);
        feedback.textContent = '✗ Error checking email availability';
        feedback.className = 'field-feedback error';
    }
}

/**
 * Validate form using regex
 */
function validateForm() {
    const form = document.getElementById('registerForm');
    const formData = new FormData(form);
    
    // Full name validation
    const name = formData.get('customer_name').trim();
    if (!name || name.length < 2 || name.length > 100) {
        return { valid: false, message: 'Full name must be between 2 and 100 characters.' };
    }
    if (!/^[a-zA-Z\s'-]+$/.test(name)) {
        return { valid: false, message: 'Full name can only contain letters, spaces, hyphens, and apostrophes.' };
    }

    // Email validation
    const email = formData.get('customer_email').trim();
    if (!email || !validateEmail(email)) {
        return { valid: false, message: 'Please enter a valid email address.' };
    }
    if (email.length > 100) {
        return { valid: false, message: 'Email must be less than 100 characters.' };
    }

    // Password validation
    const password = formData.get('customer_pass');
    if (!validatePasswordRequirements(password)) {
        return { valid: false, message: 'Password does not meet all requirements.' };
    }

    // Confirm password
    const confirmPassword = formData.get('confirmPassword');
    if (password !== confirmPassword) {
        return { valid: false, message: 'Passwords do not match.' };
    }

    // Contact validation
    const contact = formData.get('customer_contact');
    if (!contact || contact.trim().length === 0) {
        return { valid: false, message: 'Contact number is required.' };
    }
    if (iti && !iti.isValidNumber()) {
        return { valid: false, message: 'Please enter a valid phone number.' };
    }

    return { valid: true };
}

/**
 * Handle form submission
 */
async function handleFormSubmit(e) {
    e.preventDefault();

    // Validate form
    const validation = validateForm();
    if (!validation.valid) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: validation.message,
            confirmButtonColor: '#7FB685'
        });
        return;
    }

    // Show loading state
    const submitBtn = document.getElementById('submitBtn');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoader = submitBtn.querySelector('.btn-loader');
    
    if (btnText) btnText.style.display = 'none';
    if (btnLoader) btnLoader.style.display = 'inline-block';
    submitBtn.disabled = true;

    // Prepare form data
    const form = document.getElementById('registerForm');
    const formData = new FormData(form);
    
    // Get full phone number with country code
    if (iti) {
        const fullNumber = iti.getNumber();
        formData.set('customer_contact', fullNumber);
    }
    
    // Add redirect parameter from URL if present
    const urlParams = new URLSearchParams(window.location.search);
    const redirect = urlParams.get('redirect');
    if (redirect) {
        formData.append('redirect', redirect);
    }

    try {
        const response = await fetch('../Actions/register_customer_action.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        // Reset button state
        if (btnText) btnText.style.display = 'inline';
        if (btnLoader) btnLoader.style.display = 'none';
        submitBtn.disabled = false;

        if (result.status) {
            // Success - show Sweet Alert and redirect
            // Check if user was auto-logged in (came from checkout)
            const isAutoLoggedIn = result.auto_logged_in === true;
            const isCheckoutRedirect = result.redirect && result.redirect.includes('checkout');
            
            Swal.fire({
                icon: 'success',
                title: 'Registration Successful!',
                text: isAutoLoggedIn 
                    ? (result.message || 'Your account has been created and you have been automatically logged in!')
                    : (result.message || 'Your account has been created successfully. Please log in to continue.'),
                confirmButtonColor: '#7FB685',
                confirmButtonText: isAutoLoggedIn ? (isCheckoutRedirect ? 'Go to Checkout' : 'Continue') : 'Go to Login',
                timer: isAutoLoggedIn ? 1500 : 2000,
                timerProgressBar: true
            }).then(() => {
                // Redirect based on result
                if (result.redirect) {
                    window.location.href = result.redirect;
                } else {
                    window.location.href = '../View/login.php';
                }
            });
        } else {
            // Error - show Sweet Alert
            Swal.fire({
                icon: 'error',
                title: 'Registration Failed',
                text: result.message || 'An error occurred. Please try again.',
                confirmButtonColor: '#7FB685'
            });
        }
    } catch (error) {
        console.error('Registration error:', error);
        
        // Reset button state
        if (btnText) btnText.style.display = 'inline';
        if (btnLoader) btnLoader.style.display = 'none';
        submitBtn.disabled = false;

        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Network error. Please check your connection and try again.',
            confirmButtonColor: '#7FB685'
        });
    }
}


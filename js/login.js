/**
 * Login Form Validation and Submission
 * Handles form validation and async login submission
 */

document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLoginSubmit);
    }

    // Real-time email validation
    const emailInput = document.getElementById('customer_email');
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            validateEmailFormat(this.value);
        });
    }
});

/**
 * Validate email format
 */
function validateEmailFormat(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Validate login form
 */
function validateLoginForm() {
    const form = document.getElementById('loginForm');
    const formData = new FormData(form);
    
    // Email validation
    const email = formData.get('customer_email').trim();
    if (!email) {
        return { valid: false, message: 'Email is required.' };
    }
    if (!validateEmailFormat(email)) {
        return { valid: false, message: 'Please enter a valid email address.' };
    }
    if (email.length > 100) {
        return { valid: false, message: 'Email must be less than 100 characters.' };
    }

    // Password validation
    const password = formData.get('customer_pass');
    if (!password) {
        return { valid: false, message: 'Password is required.' };
    }
    if (password.length < 1) {
        return { valid: false, message: 'Password cannot be empty.' };
    }

    return { valid: true };
}

/**
 * Handle login form submission
 */
async function handleLoginSubmit(e) {
    e.preventDefault();

    // Validate form
    const validation = validateLoginForm();
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
    const form = document.getElementById('loginForm');
    const formData = new FormData(form);
    
    // Add redirect parameter from URL if present
    const urlParams = new URLSearchParams(window.location.search);
    const redirect = urlParams.get('redirect');
    if (redirect) {
        formData.append('redirect', redirect);
    }

    try {
        const response = await fetch('../Actions/login_customer_action.php', {
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
            Swal.fire({
                icon: 'success',
                title: 'Login Successful!',
                text: result.message || 'Welcome back!',
                confirmButtonColor: '#7FB685',
                confirmButtonText: 'Continue',
                timer: 1500,
                timerProgressBar: true
            }).then(() => {
                // Redirect based on role (handled by server response)
                if (result.redirect) {
                    window.location.href = result.redirect;
                } else {
                    window.location.href = '../index.php';
                }
            });
        } else {
            // Error - show Sweet Alert
            Swal.fire({
                icon: 'error',
                title: 'Login Failed',
                text: result.message || 'Invalid email or password. Please try again.',
                confirmButtonColor: '#7FB685'
            });
        }
    } catch (error) {
        console.error('Login error:', error);
        
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


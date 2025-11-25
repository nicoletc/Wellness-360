/**
 * Checkout JavaScript
 * Manages Paystack payment integration and checkout flow
 */

// Check if user is logged in
async function checkLoginStatus() {
    try {
        const response = await fetch('../Actions/check_login_status_action.php');
        const result = await response.json();
        return result.is_logged_in === true;
    } catch (error) {
        console.error('Error checking login status:', error);
        return false;
    }
}

// Handle payment method selection
function handlePaymentMethodChange() {
    const paymentMethod = document.getElementById('payment_method').value;
    const paymentChannelContainer = document.getElementById('payment_channel_container');
    const paymentChannel = document.getElementById('payment_channel');
    const payNowBtn = document.getElementById('pay-now-btn');
    
    // Show/hide payment channel based on payment method
    // Only show channel selection for Paystack
    if (paymentMethod === 'paystack') {
        paymentChannelContainer.style.display = 'block';
        paymentChannel.required = true;
        // Enable button only if channel is selected
        if (paymentChannel.value) {
            payNowBtn.disabled = false;
        } else {
            payNowBtn.disabled = true;
        }
    } else {
        // For bank_transfer and mobile_money, channel is auto-set based on method
        paymentChannelContainer.style.display = 'none';
        paymentChannel.required = false;
        paymentChannel.value = '';
        // Enable button immediately for non-Paystack methods
        payNowBtn.disabled = false;
    }
}

// Process payment based on selected method
async function processPayment() {
    const paymentMethod = document.getElementById('payment_method').value;
    
    if (!paymentMethod) {
        Swal.fire({
            icon: 'error',
            title: 'Payment Method Required',
            text: 'Please select a payment method.',
            confirmButtonColor: '#7FB685'
        });
        return false;
    }
    
    if (paymentMethod === 'paystack') {
        return await initializePaystackPayment();
    } else {
        return await processOtherPayment(paymentMethod);
    }
}

// Initialize Paystack payment
async function initializePaystackPayment() {
    // Check if user is logged in first
    const isLoggedIn = await checkLoginStatus();
    
    if (!isLoggedIn) {
        // Redirect to signup with return URL
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'info',
                title: 'Login Required',
                html: `
                    <p>Please sign up or log in to complete your purchase.</p>
                    <p>Don't worry, your cart items will be saved!</p>
                `,
                showCancelButton: true,
                confirmButtonText: 'Sign Up',
                cancelButtonText: 'Log In',
                confirmButtonColor: '#7FB685',
                cancelButtonColor: '#6B7E75'
            }).then((swalResult) => {
                if (swalResult.isConfirmed) {
                    window.location.href = 'register.php?redirect=View/checkout.php';
                } else if (swalResult.dismiss === Swal.DismissReason.cancel) {
                    window.location.href = 'login.php?redirect=View/checkout.php';
                }
            });
        } else {
            if (confirm('Please sign up or log in to complete your purchase. Go to signup page?')) {
                window.location.href = 'register.php?redirect=View/checkout.php';
            } else {
                window.location.href = 'login.php?redirect=View/checkout.php';
            }
        }
        return false;
    }
    
    // Get cart total
    const totalElement = document.getElementById('checkout-total');
    let totalAmount = 0;
    
    if (totalElement) {
        const totalText = totalElement.textContent.replace('₵', '').trim();
        totalAmount = parseFloat(totalText) || 0;
    } else {
        // Fallback: calculate from items
        const items = document.querySelectorAll('.checkout-item');
        items.forEach(item => {
            const priceElement = item.querySelector('.item-price');
            const quantityElement = item.querySelector('.item-quantity');
            if (priceElement && quantityElement) {
                const price = parseFloat(priceElement.getAttribute('data-price')) || 0;
                const quantity = parseInt(quantityElement.textContent) || 0;
                totalAmount += price * quantity;
            }
        });
    }
    
    if (totalAmount <= 0) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Amount',
                text: 'Your cart total is invalid. Please refresh the page and try again.',
                confirmButtonColor: '#7FB685'
            });
        }
        return false;
    }
    
    // Get payment channel
    const paymentChannel = document.getElementById('payment_channel').value;
    
    if (!paymentChannel) {
        Swal.fire({
            icon: 'error',
            title: 'Payment Channel Required',
            text: 'Please select a payment channel.',
            confirmButtonColor: '#7FB685'
        });
        return false;
    }
    
    // Prompt for email
    const { value: email } = await Swal.fire({
        title: 'Enter Your Email',
        html: `
            <p style="margin-bottom: 1rem;">We need your email to process the payment.</p>
            <input type="email" id="swal-email" class="swal2-input" placeholder="your.email@example.com" required>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Continue to Payment',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#7FB685',
        cancelButtonColor: '#6B7E75',
        preConfirm: () => {
            const emailInput = document.getElementById('swal-email');
            const email = emailInput.value.trim();
            
            if (!email) {
                Swal.showValidationMessage('Email is required');
                return false;
            }
            
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                Swal.showValidationMessage('Please enter a valid email address');
                return false;
            }
            
            return email;
        }
    });
    
    if (!email) {
        return false; // User cancelled
    }
    
    try {
        // Show loading state
        Swal.fire({
            title: 'Initializing Payment...',
            text: 'Redirecting to secure payment gateway...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Initialize Paystack transaction
        const response = await fetch('../Actions/paystack_init_transaction.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                amount: totalAmount,
                email: email,
                payment_channel: paymentChannel
            })
        });
        
        const result = await response.json();
        
        Swal.close();
        
        if (result.status === 'success' && result.authorization_url) {
            // Redirect to Paystack payment page
            window.location.href = result.authorization_url;
        } else {
            // Show error message
            Swal.fire({
                icon: 'error',
                title: 'Payment Initialization Failed',
                text: result.message || 'Failed to initialize payment. Please try again.',
                confirmButtonColor: '#7FB685'
            });
            return false;
        }
    } catch (error) {
        console.error('Error initializing payment:', error);
        Swal.close();
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while initializing payment. Please try again.',
            confirmButtonColor: '#7FB685'
        });
        return false;
    }
}

// Process other payment methods (cash, bank_transfer, etc.)
async function processOtherPayment(paymentMethod) {
    // Check if user is logged in first
    const isLoggedIn = await checkLoginStatus();
    
    if (!isLoggedIn) {
        // Redirect to signup with return URL
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'info',
                title: 'Login Required',
                html: `
                    <p>Please sign up or log in to complete your purchase.</p>
                    <p>Don't worry, your cart items will be saved!</p>
                `,
                showCancelButton: true,
                confirmButtonText: 'Sign Up',
                cancelButtonText: 'Log In',
                confirmButtonColor: '#7FB685',
                cancelButtonColor: '#6B7E75'
            }).then((swalResult) => {
                if (swalResult.isConfirmed) {
                    window.location.href = 'register.php?redirect=View/checkout.php';
                } else if (swalResult.dismiss === Swal.DismissReason.cancel) {
                    window.location.href = 'login.php?redirect=View/checkout.php';
                }
            });
        }
        return false;
    }
    
    // Get cart total
    const totalElement = document.getElementById('checkout-total');
    let totalAmount = 0;
    
    if (totalElement) {
        const totalText = totalElement.textContent.replace('₵', '').trim();
        totalAmount = parseFloat(totalText) || 0;
    }
    
    if (totalAmount <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Amount',
            text: 'Your cart total is invalid. Please refresh the page and try again.',
            confirmButtonColor: '#7FB685'
        });
        return false;
    }
    
    // Get payment channel
    // For Paystack, get from dropdown; for others, auto-set based on method
    let paymentChannel;
    if (paymentMethod === 'paystack') {
        paymentChannel = document.getElementById('payment_channel').value;
        if (!paymentChannel) {
            Swal.fire({
                icon: 'error',
                title: 'Payment Channel Required',
                text: 'Please select a payment channel.',
                confirmButtonColor: '#7FB685'
            });
            return false;
        }
    } else if (paymentMethod === 'mobile_money') {
        paymentChannel = 'mobile_money';
    } else if (paymentMethod === 'bank_transfer') {
        paymentChannel = 'bank';
    } else {
        paymentChannel = 'card'; // Fallback (shouldn't happen)
    }
    
    try {
        // Show loading state
        Swal.fire({
            title: 'Processing Order...',
            text: 'Please wait while we process your order.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Process checkout with selected payment method
        const response = await fetch('../Actions/process_checkout_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                payment_method: paymentMethod,
                payment_channel: paymentChannel
            })
        });
        
        const result = await response.json();
        
        Swal.close();
        
        if (result.status === 'success') {
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Order Confirmed!',
                html: `
                    <p>${result.message || 'Your order has been processed successfully.'}</p>
                    <p><strong>Order Reference:</strong> ${result.invoice_no || result.order_id}</p>
                    <p><strong>Total:</strong> ₵${parseFloat(result.total || 0).toFixed(2)}</p>
                    <p><strong>Payment Method:</strong> ${paymentMethod.charAt(0).toUpperCase() + paymentMethod.slice(1).replace('_', ' ')}</p>
                `,
                confirmButtonText: 'View Orders',
                showCancelButton: true,
                cancelButtonText: 'Continue Shopping',
                confirmButtonColor: '#7FB685'
            }).then((swalResult) => {
                if (swalResult.isConfirmed) {
                    window.location.href = 'profile.php?tab=orders';
                } else {
                    window.location.href = '../index.php';
                }
            });
            return true;
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Order Failed',
                text: result.message || 'Failed to process your order. Please try again.',
                confirmButtonColor: '#7FB685'
            });
            return false;
        }
    } catch (error) {
        console.error('Error processing payment:', error);
        Swal.close();
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while processing your order. Please try again.',
            confirmButtonColor: '#7FB685'
        });
        return false;
    }
}

// Show payment modal (routes to appropriate payment handler)
async function showPaymentModal() {
    await processPayment();
}

// Initialize checkout page
document.addEventListener('DOMContentLoaded', function() {
    // Handle payment method change
    const paymentMethodSelect = document.getElementById('payment_method');
    if (paymentMethodSelect) {
        paymentMethodSelect.addEventListener('change', handlePaymentMethodChange);
    }
    
    // Handle payment channel change (for Paystack)
    const paymentChannelSelect = document.getElementById('payment_channel');
    if (paymentChannelSelect) {
        paymentChannelSelect.addEventListener('change', function() {
            const payNowBtn = document.getElementById('pay-now-btn');
            const paymentMethod = document.getElementById('payment_method').value;
            
            if (paymentMethod === 'paystack' && this.value) {
                payNowBtn.disabled = false;
            } else if (paymentMethod === 'paystack' && !this.value) {
                payNowBtn.disabled = true;
            }
        });
    }
    
    // Handle "Pay Now" or "Proceed to Payment" button
    const payNowBtn = document.getElementById('pay-now-btn') || 
                      document.getElementById('proceed-payment-btn') ||
                      document.getElementById('simulate-payment-btn') ||
                      document.querySelector('.btn-pay-now') ||
                      document.querySelector('.proceed-payment-btn');
    
    if (payNowBtn) {
        payNowBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Validate payment method selection
            const paymentMethod = document.getElementById('payment_method').value;
            if (!paymentMethod) {
                Swal.fire({
                    icon: 'error',
                    title: 'Payment Method Required',
                    text: 'Please select a payment method.',
                    confirmButtonColor: '#7FB685'
                });
                return;
            }
            
            if (paymentMethod === 'paystack') {
                const paymentChannel = document.getElementById('payment_channel').value;
                if (!paymentChannel) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Payment Channel Required',
                        text: 'Please select a payment channel.',
                        confirmButtonColor: '#7FB685'
                    });
                    return;
                }
            }
            
            // Disable button during payment initialization
            this.disabled = true;
            
            showPaymentModal();
            
            // Re-enable button after a delay (in case of error)
            setTimeout(() => {
                this.disabled = false;
            }, 2000);
        });
    }
    
    // Handle checkout form submission (if exists)
    const checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            e.preventDefault();
            showPaymentModal();
        });
    }
    
    // Calculate and display totals
    updateCheckoutTotals();
});

// Update checkout totals
function updateCheckoutTotals() {
    const items = document.querySelectorAll('.checkout-item');
    let subtotal = 0;
    
    items.forEach(item => {
        const priceElement = item.querySelector('.item-price');
        const quantityElement = item.querySelector('.item-quantity');
        
        if (priceElement && quantityElement) {
            const price = parseFloat(priceElement.getAttribute('data-price')) || 0;
            const quantity = parseInt(quantityElement.textContent) || 0;
            subtotal += price * quantity;
        }
    });
    
    // Update subtotal display
    const subtotalElement = document.getElementById('checkout-subtotal');
    if (subtotalElement) {
        subtotalElement.textContent = '₵' + subtotal.toFixed(2);
    }
    
    // Calculate tax (if applicable - 0% for now)
    const tax = 0;
    const taxElement = document.getElementById('checkout-tax');
    if (taxElement) {
        taxElement.textContent = '₵' + tax.toFixed(2);
    }
    
    // Calculate total
    const total = subtotal + tax;
    const totalElement = document.getElementById('checkout-total');
    if (totalElement) {
        totalElement.textContent = '₵' + total.toFixed(2);
    }
}


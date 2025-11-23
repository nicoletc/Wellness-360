/**
 * Checkout JavaScript
 * Manages the simulated payment modal and checkout flow
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

// Process checkout function
async function processCheckout() {
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
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#007bff'
            }).then((swalResult) => {
                if (swalResult.isConfirmed) {
                    // Redirect to signup with return URL
                    window.location.href = 'register.php?redirect=View/checkout.php';
                } else if (swalResult.dismiss === Swal.DismissReason.cancel) {
                    // Redirect to login with return URL
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
    
    try {
        // Show loading state
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Processing...',
                text: 'Please wait while we process your order.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
        
        const response = await fetch('../Actions/process_checkout_action.php', {
            method: 'POST'
        });
        
        const result = await response.json();
        
        if (typeof Swal !== 'undefined') {
            Swal.close();
        }
        
        if (result.status === 'success') {
            // Show success message
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Order Confirmed!',
                    html: `
                        <p>${result.message || 'Your order has been processed successfully.'}</p>
                        <p><strong>Order Reference:</strong> ${result.invoice_no || result.order_id}</p>
                        <p><strong>Total:</strong> ₵${parseFloat(result.total || 0).toFixed(2)}</p>
                    `,
                    confirmButtonText: 'View Orders',
                    showCancelButton: true,
                    cancelButtonText: 'Continue Shopping'
                }).then((swalResult) => {
                    if (swalResult.isConfirmed) {
                        // Redirect to orders page (if exists) or home
                        window.location.href = '../View/profile.php?tab=orders';
                    } else {
                        window.location.href = '../View/shop.php';
                    }
                });
            } else {
                alert(`Order confirmed!\nOrder Reference: ${result.invoice_no || result.order_id}\nTotal: ₵${parseFloat(result.total || 0).toFixed(2)}`);
                window.location.href = '../View/shop.php';
            }
            
            return true;
        } else if (result.status === 'partial') {
            // Partial success (order created but payment recording failed)
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Order Created',
                    html: `
                        <p>${result.message}</p>
                        <p><strong>Order Reference:</strong> ${result.invoice_no || result.order_id}</p>
                        <p>Please contact support if you have any questions.</p>
                    `,
                    confirmButtonText: 'OK'
                });
            } else {
                alert(result.message);
            }
            
            return false;
        } else {
            // Show error message
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Checkout Failed',
                    html: `
                        <p>${result.message || 'Failed to process your order. Please try again.'}</p>
                        ${result.errors ? '<ul>' + result.errors.map(e => '<li>' + e + '</li>').join('') + '</ul>' : ''}
                    `,
                    confirmButtonText: 'OK'
                });
            } else {
                alert(result.message || 'Failed to process your order. Please try again.');
            }
            
            return false;
        }
    } catch (error) {
        console.error('Error processing checkout:', error);
        
        if (typeof Swal !== 'undefined') {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while processing your order. Please try again.'
            });
        } else {
            alert('An error occurred while processing your order. Please try again.');
        }
        
        return false;
    }
}

// Show payment confirmation modal
async function showPaymentModal() {
    // Check if user is logged in first
    const isLoggedIn = await checkLoginStatus();
    
    if (!isLoggedIn) {
        // Show login/signup prompt immediately
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
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#007bff'
            }).then((swalResult) => {
                if (swalResult.isConfirmed) {
                    // Redirect to signup with return URL
                    window.location.href = 'register.php?redirect=View/checkout.php';
                } else if (swalResult.dismiss === Swal.DismissReason.cancel) {
                    // Redirect to login with return URL
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
        return;
    }
    
    // User is logged in, show payment confirmation modal
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'question',
            title: 'Simulate Payment',
            html: `
                <p>This is a simulated payment. Click "Yes, I've paid" to complete the checkout.</p>
                <p class="text-muted" style="font-size: 0.9em; margin-top: 1em;">
                    <i class="fas fa-info-circle"></i> In a real application, this would redirect to a payment gateway.
                </p>
            `,
            showCancelButton: true,
            confirmButtonText: "Yes, I've paid",
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                // User confirmed payment
                processCheckout();
            } else {
                // User cancelled
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Payment Cancelled',
                        text: 'Your order has not been processed.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            }
        });
    } else {
        // Fallback to confirm dialog
        if (confirm("This is a simulated payment. Click OK to complete the checkout.")) {
            processCheckout();
        }
    }
}

// Initialize checkout page
document.addEventListener('DOMContentLoaded', function() {
    // Handle "Simulate Payment" button
    const simulatePaymentBtn = document.getElementById('simulate-payment-btn');
    if (simulatePaymentBtn) {
        simulatePaymentBtn.addEventListener('click', function() {
            // Disable button during modal
            this.disabled = true;
            
            showPaymentModal();
            
            // Re-enable button after modal closes (with delay)
            setTimeout(() => {
                this.disabled = false;
            }, 1000);
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


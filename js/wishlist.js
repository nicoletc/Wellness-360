/**
 * Wishlist JavaScript
 * Handles adding and removing products from wishlist
 */

// Track ongoing requests to prevent duplicate clicks
const wishlistRequests = new Set();

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

// Toggle wishlist (add or remove)
async function toggleWishlist(productId) {
    // Prevent duplicate requests for the same product
    if (wishlistRequests.has(productId)) {
        console.log('Wishlist request already in progress for product:', productId);
        return false;
    }
    
    // Check if user is logged in first
    const isLoggedIn = await checkLoginStatus();
    
    if (!isLoggedIn) {
        // Show friendly login/signup message before making any request
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'info',
                title: 'Login Required',
                text: 'Please log in or sign up to add items to your wishlist.',
                showCancelButton: true,
                confirmButtonText: 'Log In',
                cancelButtonText: 'Sign Up',
                confirmButtonColor: '#007bff',
                cancelButtonColor: '#28a745'
            }).then((swalResult) => {
                if (swalResult.isConfirmed) {
                    window.location.href = '../View/login.php';
                } else if (swalResult.dismiss === Swal.DismissReason.cancel) {
                    window.location.href = '../View/register.php';
                }
            });
        } else {
            if (confirm('Please log in or sign up to add items to your wishlist. Go to login page?')) {
                window.location.href = '../View/login.php';
            }
        }
        return false;
    }
    
    // Get current button state to determine action
    const buttons = document.querySelectorAll(`[data-product-id="${productId}"].wishlist-btn, .product-wishlist-btn[data-product-id="${productId}"]`);
    let currentState = false;
    
    if (buttons.length > 0) {
        const firstButton = buttons[0];
        const icon = firstButton.querySelector('i');
        if (icon && icon.classList.contains('fas')) {
            currentState = true; // Currently in wishlist (filled heart)
        }
        
        // Disable button during request
        buttons.forEach(btn => {
            btn.disabled = true;
            btn.style.opacity = '0.6';
            btn.style.cursor = 'wait';
        });
    }
    
    // Add to requests set
    wishlistRequests.add(productId);
    
    try {
        // If button shows as in wishlist, try to remove; otherwise add
        // The server will validate and return appropriate messages
        let result;
        if (currentState) {
            result = await removeFromWishlist(productId);
        } else {
            result = await addToWishlist(productId);
        }
        
        return result;
    } finally {
        // Re-enable button and remove from requests set
        buttons.forEach(btn => {
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.style.cursor = 'pointer';
        });
        wishlistRequests.delete(productId);
    }
}

// Add to wishlist
async function addToWishlist(productId) {
    try {
        const formData = new FormData();
        formData.append('product_id', productId);
        
        const response = await fetch('../Actions/add_to_wishlist_action.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            // Update button state
            updateWishlistButton(productId, true);
            
            // Show success message
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Added to Wishlist',
                    text: result.message || 'Product added to wishlist successfully.',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                alert(result.message || 'Product added to wishlist successfully.');
            }
            
            return true;
        } else {
            // Check if this is a login required error (401 status or requires_login flag)
            if (response.status === 401 || result.requires_login) {
                // Show friendly login/signup message
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Login Required',
                        text: result.message || 'Please log in or sign up to add items to your wishlist.',
                        showCancelButton: true,
                        confirmButtonText: 'Log In',
                        cancelButtonText: 'Sign Up',
                        confirmButtonColor: '#007bff',
                        cancelButtonColor: '#28a745'
                    }).then((swalResult) => {
                        if (swalResult.isConfirmed) {
                            window.location.href = '../View/login.php';
                        } else if (swalResult.dismiss === Swal.DismissReason.cancel) {
                            window.location.href = '../View/register.php';
                        }
                    });
                } else {
                    if (confirm('Please log in or sign up to add items to your wishlist. Go to login page?')) {
                        window.location.href = '../View/login.php';
                    }
                }
                return false;
            }
            
            // If error is "already in wishlist", update button state to reflect reality
            if (result.message && result.message.toLowerCase().includes('already')) {
                updateWishlistButton(productId, true);
                // Re-check status from server to ensure button state is correct
                setTimeout(async () => {
                    try {
                        const checkResponse = await fetch(`../Actions/check_wishlist_action.php?product_id=${productId}`);
                        const checkResult = await checkResponse.json();
                        if (checkResult.status === 'success') {
                            updateWishlistButton(productId, checkResult.in_wishlist || false);
                        }
                    } catch (error) {
                        console.error('Error re-checking wishlist status:', error);
                    }
                }, 500);
            }
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: result.message && result.message.toLowerCase().includes('already') ? 'info' : 'error',
                    title: result.message && result.message.toLowerCase().includes('already') ? 'Already in Wishlist' : 'Error',
                    text: result.message || 'Failed to add product to wishlist.'
                });
            } else {
                alert(result.message || 'Failed to add product to wishlist.');
            }
            
            return false;
        }
    } catch (error) {
        console.error('Error adding to wishlist:', error);
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred. Please try again.'
            });
        } else {
            alert('An error occurred. Please try again.');
        }
        
        return false;
    }
}

// Remove from wishlist
async function removeFromWishlist(productId) {
    // Validate productId
    productId = parseInt(productId);
    if (!productId || productId <= 0) {
        console.error('Invalid productId in removeFromWishlist:', productId);
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Invalid product ID.'
            });
        }
        return false;
    }
    
    try {
        // Directly attempt to remove - let the server handle validation
        const formData = new FormData();
        formData.append('product_id', productId);
        
        console.log('Removing product from wishlist, productId:', productId);
        
        const response = await fetch('../Actions/remove_from_wishlist_action.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        console.log('Remove from wishlist response:', result);
        
        if (result.status === 'success') {
            // Update button state to reflect removal
            updateWishlistButton(productId, false);
            
            // Show success message
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Removed from Wishlist',
                    text: result.message || 'Product removed from wishlist successfully.',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                alert(result.message || 'Product removed from wishlist successfully.');
            }
            
            return true;
        } else {
            // If error says "not in wishlist", update button state to match reality
            if (result.message && (result.message.toLowerCase().includes('not in') || result.message.toLowerCase().includes('not in your wishlist'))) {
                updateWishlistButton(productId, false);
                // Show a less alarming message since the state is now corrected
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Already Removed',
                        text: 'This product is not in your wishlist.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            } else {
                // Other errors
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.message || 'Failed to remove product from wishlist.'
                    });
                } else {
                    alert(result.message || 'Failed to remove product from wishlist.');
                }
            }
            
            return false;
        }
    } catch (error) {
        console.error('Error removing from wishlist:', error);
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred. Please try again.'
            });
        } else {
            alert('An error occurred. Please try again.');
        }
        
        return false;
    }
}

// Update wishlist button state
function updateWishlistButton(productId, isInWishlist) {
    // Find all wishlist buttons for this product
    const buttons = document.querySelectorAll(`[data-product-id="${productId}"].wishlist-btn, .product-wishlist-btn[data-product-id="${productId}"]`);
    
    buttons.forEach(btn => {
        const icon = btn.querySelector('i');
        const textSpan = btn.querySelector('.wishlist-text');
        
        if (icon) {
            if (isInWishlist) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                btn.classList.add('in-wishlist');
                if (textSpan) {
                    textSpan.textContent = 'Remove from Wishlist';
                }
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                btn.classList.remove('in-wishlist');
                if (textSpan) {
                    textSpan.textContent = 'Add to Wishlist';
                }
            }
        }
    });
}


// Initialize wishlist buttons
document.addEventListener('DOMContentLoaded', function() {
    // Handle wishlist buttons on product cards (shop.php)
    document.querySelectorAll('.product-wishlist-btn').forEach(btn => {
        const productId = btn.getAttribute('data-product-id');
        if (productId) {
            // Ensure productId is a valid number
            const productIdNum = parseInt(productId);
            if (productIdNum && productIdNum > 0) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Wishlist button clicked, productId:', productIdNum);
                    toggleWishlist(productIdNum);
                });
            } else {
                console.error('Invalid productId on button:', productId);
            }
        }
    });
    
    // Handle wishlist buttons with .wishlist-btn class (single_product.php)
    document.querySelectorAll('.wishlist-btn').forEach(btn => {
        const productId = btn.getAttribute('data-product-id');
        if (productId) {
            // Check if this button already has a listener (avoid duplicates)
            if (btn.hasAttribute('data-wishlist-listener')) {
                return; // Already has listener
            }
            btn.setAttribute('data-wishlist-listener', 'true');
            
            const productIdNum = parseInt(productId);
            if (productIdNum && productIdNum > 0) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Wishlist button clicked, productId:', productIdNum);
                    toggleWishlist(productIdNum);
                });
            } else {
                console.error('Invalid productId on button:', productId);
            }
        }
    });
    
    // Check wishlist status for all products on page load
    document.querySelectorAll('.product-wishlist-btn, .wishlist-btn').forEach(async btn => {
        const productId = btn.getAttribute('data-product-id');
        if (productId) {
            try {
                const response = await fetch(`../Actions/check_wishlist_action.php?product_id=${productId}`);
                const result = await response.json();
                if (result.status === 'success') {
                    updateWishlistButton(parseInt(productId), result.in_wishlist || false);
                }
            } catch (error) {
                console.error('Error checking wishlist status:', error);
                // On error, default to not in wishlist
                updateWishlistButton(parseInt(productId), false);
            }
        }
    });
});


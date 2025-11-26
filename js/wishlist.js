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
    
    // Get buttons and disable during request
    const buttons = document.querySelectorAll(`[data-product-id="${productId}"].wishlist-btn, .product-wishlist-btn[data-product-id="${productId}"]`);
    
    buttons.forEach(btn => {
        btn.disabled = true;
        btn.style.opacity = '0.6';
        btn.style.cursor = 'wait';
    });
    
    // Add to requests set
    wishlistRequests.add(productId);
    
    try {
        // Always check actual state from server first to avoid sync issues
        let actualState = false;
        try {
            const checkResponse = await fetch(`../Actions/check_wishlist_action.php?product_id=${productId}`);
            const checkResult = await checkResponse.json();
            console.log('=== WISHLIST TOGGLE - Server Check ===');
            console.log('Check response:', checkResult);
            console.log('Check status:', checkResult.status);
            console.log('in_wishlist value:', checkResult.in_wishlist);
            console.log('in_wishlist type:', typeof checkResult.in_wishlist);
            
            if (checkResult.status === 'success') {
                // Handle both boolean true and string 'true'
                actualState = checkResult.in_wishlist === true || checkResult.in_wishlist === 'true' || checkResult.in_wishlist === 1;
                console.log('Actual wishlist state from server (after conversion):', actualState);
                // Update button to match server state
                updateWishlistButton(productId, actualState);
            } else {
                console.warn('Wishlist check failed, defaulting to false');
                actualState = false;
                updateWishlistButton(productId, false);
            }
        } catch (error) {
            console.error('Error checking wishlist status:', error);
            // On error, default to false (not in wishlist)
            actualState = false;
            updateWishlistButton(productId, false);
        }
        
        // Perform action based on actual server state
        let result;
        console.log('=== WISHLIST TOGGLE - Performing Action ===');
        console.log('actualState:', actualState, '(type:', typeof actualState, ')');
        
        if (actualState === true) {
            // Product is in wishlist, remove it
            console.log('→ Calling removeFromWishlist (product IS in wishlist)');
            result = await removeFromWishlist(productId);
        } else {
            // Product is not in wishlist, add it
            console.log('→ Calling addToWishlist (product is NOT in wishlist)');
            result = await addToWishlist(productId);
        }
        
        console.log('=== WISHLIST TOGGLE - Action Complete ===');
        console.log('Result:', result);
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
        console.log('Add to wishlist response:', result);
        console.log('Add response status:', result.status, 'type:', typeof result.status);
        
        // Check for success (handle both string 'success' and boolean true)
        const isSuccess = result.status === 'success' || result.status === true;
        
        if (isSuccess) {
            // Update button state to reflect addition
            console.log('Addition successful - updating button state to true (added)');
            updateWishlistButton(productId, true);
            
            // Verify the update worked by checking again after a short delay
            setTimeout(() => {
                fetch(`../Actions/check_wishlist_action.php?product_id=${productId}`)
                    .then(response => response.json())
                    .then(checkResult => {
                        console.log('Re-check wishlist state after addition:', checkResult);
                        if (checkResult.status === 'success') {
                            const inWishlist = checkResult.in_wishlist === true;
                            console.log('Re-check result - in_wishlist:', inWishlist);
                            updateWishlistButton(productId, inWishlist);
                        }
                    })
                    .catch(error => console.error('Error verifying wishlist state:', error));
            }, 200);
            
            // Show success message for ADDITION
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
            
            // If error is "already in wishlist", verify and update button state
            if (result.message && (result.message.toLowerCase().includes('already') || result.message.toLowerCase().includes('already in'))) {
                // Re-check actual state from server
                try {
                    const checkResponse = await fetch(`../Actions/check_wishlist_action.php?product_id=${productId}`);
                    const checkResult = await checkResponse.json();
                    if (checkResult.status === 'success') {
                        updateWishlistButton(productId, checkResult.in_wishlist || false);
                    }
                } catch (error) {
                    console.error('Error re-checking wishlist status:', error);
                    // Default to true if already in wishlist
                    updateWishlistButton(productId, true);
                }
                
                // Show info message (not error)
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Already in Wishlist',
                        text: 'This product is already in your wishlist.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            } else {
                // Other errors - verify state and show error
                try {
                    const checkResponse = await fetch(`../Actions/check_wishlist_action.php?product_id=${productId}`);
                    const checkResult = await checkResponse.json();
                    if (checkResult.status === 'success') {
                        updateWishlistButton(productId, checkResult.in_wishlist || false);
                    }
                } catch (error) {
                    console.error('Error re-checking wishlist status:', error);
                }
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.message || 'Failed to add product to wishlist.'
                    });
                } else {
                    alert(result.message || 'Failed to add product to wishlist.');
                }
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
        console.log('Remove response status:', result.status, 'type:', typeof result.status);
        
        // Check for success (handle both string 'success' and boolean true)
        const isSuccess = result.status === 'success' || result.status === true;
        
        if (isSuccess) {
            // Update button state to reflect removal FIRST
            console.log('Removal successful - updating button state to false (removed)');
            updateWishlistButton(productId, false);
            
            // Verify the update worked by checking again after a short delay
            setTimeout(() => {
                // Re-check to ensure button state is correct
                fetch(`../Actions/check_wishlist_action.php?product_id=${productId}`)
                    .then(response => response.json())
                    .then(checkResult => {
                        console.log('Re-check wishlist state after removal:', checkResult);
                        if (checkResult.status === 'success') {
                            const inWishlist = checkResult.in_wishlist === true;
                            console.log('Re-check result - in_wishlist:', inWishlist);
                            updateWishlistButton(productId, inWishlist);
                        }
                    })
                    .catch(error => console.error('Error verifying wishlist state:', error));
            }, 200);
            
            // Show success message for REMOVAL
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
            // If error says "not in wishlist", verify actual state and update button
            if (result.message && (result.message.toLowerCase().includes('not in') || result.message.toLowerCase().includes('not in your wishlist'))) {
                // Re-check actual state from server
                try {
                    const checkResponse = await fetch(`../Actions/check_wishlist_action.php?product_id=${productId}`);
                    const checkResult = await checkResponse.json();
                    if (checkResult.status === 'success') {
                        updateWishlistButton(productId, checkResult.in_wishlist || false);
                    }
                } catch (error) {
                    console.error('Error re-checking wishlist status:', error);
                    // Default to false if not in wishlist
                    updateWishlistButton(productId, false);
                }
                
                // Show info message (not error)
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Not in Wishlist',
                        text: 'This product is not in your wishlist.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            } else {
                // Other errors - verify state and show error
                try {
                    const checkResponse = await fetch(`../Actions/check_wishlist_action.php?product_id=${productId}`);
                    const checkResult = await checkResponse.json();
                    if (checkResult.status === 'success') {
                        updateWishlistButton(productId, checkResult.in_wishlist || false);
                    }
                } catch (error) {
                    console.error('Error re-checking wishlist status:', error);
                }
                
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
    // Convert to boolean to ensure correct comparison
    const inWishlist = isInWishlist === true || isInWishlist === 'true' || isInWishlist === 1;
    
    // Find all wishlist buttons for this product - use more flexible selectors
    const selectors = [
        `.wishlist-btn[data-product-id="${productId}"]`,
        `.product-wishlist-btn[data-product-id="${productId}"]`,
        `[data-product-id="${productId}"].wishlist-btn`,
        `button[data-product-id="${productId}"].wishlist-btn`,
        `.btn-wishlist-large[data-product-id="${productId}"]`,
        `[data-product-id="${productId}"]`
    ];
    
    let buttons = [];
    selectors.forEach(selector => {
        try {
            const found = document.querySelectorAll(selector);
            found.forEach(btn => {
                // Only include buttons that have wishlist-related classes or contain heart icons
                const hasWishlistClass = btn.classList.contains('wishlist-btn') || 
                                       btn.classList.contains('product-wishlist-btn') ||
                                       btn.classList.contains('btn-wishlist-large');
                const hasHeartIcon = btn.querySelector('i.fa-heart, i.fas.fa-heart, i.far.fa-heart');
                
                if ((hasWishlistClass || hasHeartIcon) && !buttons.includes(btn)) {
                    buttons.push(btn);
                }
            });
        } catch (e) {
            console.warn('Error with selector:', selector, e);
        }
    });
    
    console.log(`=== UPDATE WISHLIST BUTTON ===`);
    console.log(`Product ID: ${productId}`);
    console.log(`isInWishlist (raw): ${isInWishlist} (type: ${typeof isInWishlist})`);
    console.log(`inWishlist (converted): ${inWishlist}`);
    console.log(`Buttons found: ${buttons.length}`);
    
    buttons.forEach((btn, index) => {
        const icon = btn.querySelector('i');
        const textSpan = btn.querySelector('.wishlist-text');
        
        console.log(`Button ${index + 1}:`, btn.className, 'Icon:', icon ? icon.className : 'none');
        
        if (icon) {
            // Remove all heart-related classes first
            icon.classList.remove('fas', 'far', 'fa-heart');
            
            if (inWishlist) {
                // In wishlist - filled heart
                icon.classList.add('fas', 'fa-heart');
                btn.classList.add('in-wishlist');
                btn.setAttribute('aria-label', 'Remove from wishlist');
                if (textSpan) {
                    textSpan.textContent = 'Remove from Wishlist';
                }
                console.log(`  → Updated to: IN wishlist (filled heart)`);
            } else {
                // Not in wishlist - outline heart
                icon.classList.add('far', 'fa-heart');
                btn.classList.remove('in-wishlist');
                btn.setAttribute('aria-label', 'Add to wishlist');
                if (textSpan) {
                    textSpan.textContent = 'Add to Wishlist';
                }
                console.log(`  → Updated to: NOT in wishlist (outline heart)`);
            }
            console.log(`  → Final icon classes:`, icon.className);
        } else {
            console.warn(`  → No icon found in button`);
        }
    });
    
    // If no buttons found, log for debugging
    if (buttons.length === 0) {
        console.warn(`⚠️ No wishlist buttons found for product ${productId}`);
        const allButtons = document.querySelectorAll(`[data-product-id="${productId}"]`);
        console.log(`Found ${allButtons.length} elements with data-product-id="${productId}":`, allButtons);
    }
    
    console.log(`=== END UPDATE BUTTON ===`);
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


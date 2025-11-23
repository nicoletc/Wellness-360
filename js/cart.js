/**
 * Cart JavaScript
 * Handles all UI interactions for the cart: adding, removing, updating, and emptying items
 */

// Add to cart function
async function addToCart(productId, quantity = 1) {
    try {
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('quantity', quantity);
        
        const response = await fetch('../Actions/add_to_cart_action.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            // Show success message
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: result.message || 'Product added to cart successfully.',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                alert(result.message || 'Product added to cart successfully.');
            }
            
            // Update cart count if element exists
            await updateCartCount();
            
            return true;
        } else {
            // Show error message
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.message || 'Failed to add product to cart.'
                });
            } else {
                alert(result.message || 'Failed to add product to cart.');
            }
            
            return false;
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
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

// Remove from cart function
async function removeFromCart(productId) {
    // Show SweetAlert confirmation
    let confirmResult;
    if (typeof Swal !== 'undefined') {
        confirmResult = await Swal.fire({
            icon: 'question',
            title: 'Remove Item?',
            text: 'Are you sure you want to remove this item from your cart?',
            showCancelButton: true,
            confirmButtonText: 'Yes, remove it',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d'
        });
    } else {
        // Fallback to browser confirm
        if (!confirm('Are you sure you want to remove this item from your cart?')) {
            return false;
        }
        confirmResult = { isConfirmed: true };
    }
    
    if (!confirmResult.isConfirmed) {
        return false;
    }
    
    try {
        const formData = new FormData();
        formData.append('product_id', productId);
        
        const response = await fetch('../Actions/remove_from_cart_action.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            // Show success message
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Removed',
                    text: result.message || 'Product removed from cart.',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
            
            // Reload cart page or remove item from DOM
            if (document.getElementById('cart-items')) {
                location.reload();
            } else {
                // Remove item from DOM if on a different page
                const itemElement = document.querySelector(`[data-product-id="${productId}"]`);
                if (itemElement) {
                    itemElement.remove();
                }
                updateCartCount();
            }
            
            return true;
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.message || 'Failed to remove product from cart.'
                });
            } else {
                alert(result.message || 'Failed to remove product from cart.');
            }
            
            return false;
        }
    } catch (error) {
        console.error('Error removing from cart:', error);
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

// Update quantity function
async function updateQuantity(productId, quantity) {
    if (quantity < 0) {
        return false;
    }
    
    if (quantity === 0) {
        // Remove item if quantity is 0
        return await removeFromCart(productId);
    }
    
    try {
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('quantity', quantity);
        
        const response = await fetch('../Actions/update_quantity_action.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            // Update the subtotal in the DOM
            updateItemSubtotal(productId, quantity);
            
            // Update cart total
            updateCartTotal();
            
            return true;
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.message || 'Failed to update quantity.'
                });
            } else {
                alert(result.message || 'Failed to update quantity.');
            }
            
            // Reload to get correct values
            location.reload();
            
            return false;
        }
    } catch (error) {
        console.error('Error updating quantity:', error);
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

// Empty cart function
async function emptyCart() {
    // Show SweetAlert confirmation
    let confirmResult;
    if (typeof Swal !== 'undefined') {
        confirmResult = await Swal.fire({
            icon: 'warning',
            title: 'Empty Cart?',
            text: 'Are you sure you want to empty your cart? This action cannot be undone.',
            showCancelButton: true,
            confirmButtonText: 'Yes, empty it',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d'
        });
    } else {
        // Fallback to browser confirm
        if (!confirm('Are you sure you want to empty your cart? This action cannot be undone.')) {
            return false;
        }
        confirmResult = { isConfirmed: true };
    }
    
    if (!confirmResult.isConfirmed) {
        return false;
    }
    
    try {
        const response = await fetch('../Actions/empty_cart_action.php', {
            method: 'POST'
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Cart Emptied',
                    text: result.message || 'Your cart has been emptied.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                alert(result.message || 'Your cart has been emptied.');
                location.reload();
            }
            
            return true;
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.message || 'Failed to empty cart.'
                });
            } else {
                alert(result.message || 'Failed to empty cart.');
            }
            
            return false;
        }
    } catch (error) {
        console.error('Error emptying cart:', error);
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

// Update item subtotal
function updateItemSubtotal(productId, quantity) {
    const itemElement = document.querySelector(`[data-product-id="${productId}"]`);
    if (!itemElement) return;
    
    const priceElement = itemElement.querySelector('.item-price');
    const subtotalElement = itemElement.querySelector('.item-subtotal');
    
    if (priceElement && subtotalElement) {
        const price = parseFloat(priceElement.getAttribute('data-price')) || 0;
        const subtotal = price * quantity;
        subtotalElement.textContent = '₵' + subtotal.toFixed(2);
    }
}

// Update cart total
function updateCartTotal() {
    const items = document.querySelectorAll('[data-product-id]');
    let total = 0;
    
    items.forEach(item => {
        const priceElement = item.querySelector('.item-price');
        const quantityInput = item.querySelector('.quantity-input');
        
        if (priceElement && quantityInput) {
            const price = parseFloat(priceElement.getAttribute('data-price')) || 0;
            const quantity = parseInt(quantityInput.value) || 0;
            total += price * quantity;
        }
    });
    
    // Update subtotal (since tax is 0, subtotal = total)
    const subtotalElement = document.getElementById('cart-subtotal');
    if (subtotalElement) {
        subtotalElement.textContent = '₵' + total.toFixed(2);
    }
    
    // Update total (same as subtotal since tax is 0)
    const totalElement = document.getElementById('cart-total');
    if (totalElement) {
        totalElement.textContent = '₵' + total.toFixed(2);
    }
}

// Update cart count (for cart icon/badge)
async function updateCartCount() {
    try {
        const response = await fetch('../Actions/get_cart_count_action.php');
        const result = await response.json();
        
        if (result.status === 'success') {
            const cartCountElement = document.getElementById('cart-count');
            if (cartCountElement) {
                cartCountElement.textContent = result.count || 0;
                // Show/hide badge based on count
                if (result.count > 0) {
                    cartCountElement.style.display = 'inline';
                } else {
                    cartCountElement.style.display = 'none';
                }
            }
        }
    } catch (error) {
        console.error('Error updating cart count:', error);
    }
}

// Initialize cart page
document.addEventListener('DOMContentLoaded', function() {
    // Handle quantity input changes
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            const productId = this.getAttribute('data-product-id');
            const quantity = parseInt(this.value) || 1;
            
            if (quantity < 1) {
                this.value = 1;
                return;
            }
            
            updateQuantity(productId, quantity);
        });
    });
    
    // Handle quantity buttons
    document.querySelectorAll('.quantity-decrease').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const input = document.querySelector(`.quantity-input[data-product-id="${productId}"]`);
            
            if (input) {
                const currentQty = parseInt(input.value) || 1;
                const newQty = Math.max(1, currentQty - 1);
                input.value = newQty;
                updateQuantity(productId, newQty);
            }
        });
    });
    
    document.querySelectorAll('.quantity-increase').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const input = document.querySelector(`.quantity-input[data-product-id="${productId}"]`);
            
            if (input) {
                const currentQty = parseInt(input.value) || 1;
                const newQty = currentQty + 1;
                input.value = newQty;
                updateQuantity(productId, newQty);
            }
        });
    });
    
    // Handle remove buttons
    document.querySelectorAll('.remove-item-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            removeFromCart(productId);
        });
    });
    
    // Handle empty cart button
    const emptyCartBtn = document.getElementById('empty-cart-btn');
    if (emptyCartBtn) {
        emptyCartBtn.addEventListener('click', function() {
            emptyCart();
        });
    }
    
    // Handle add to cart buttons on product pages
    // Remove any existing onclick handlers to prevent double-firing
    document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
        // Remove onclick attribute to prevent double-firing
        btn.removeAttribute('onclick');
        
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const productId = this.getAttribute('data-product-id');
            const quantity = parseInt(this.getAttribute('data-quantity')) || 1;
            
            // Disable button during request
            this.disabled = true;
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            
            addToCart(productId, quantity).then(success => {
                this.disabled = false;
                this.innerHTML = originalText;
            });
        });
    });
    
    // Load cart count on page load
    updateCartCount();
});


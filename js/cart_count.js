/**
 * Cart Count JavaScript
 * Updates cart count on all pages
 */

// Update cart count function
async function updateCartCount() {
    try {
        const response = await fetch('../Actions/get_cart_count_action.php');
        const result = await response.json();
        
        if (result.status === 'success') {
            const cartCountElement = document.getElementById('cart-count');
            if (cartCountElement) {
                const count = result.count || 0;
                cartCountElement.textContent = count;
                // Show/hide badge based on count
                if (count > 0) {
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

// Update cart count on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
});


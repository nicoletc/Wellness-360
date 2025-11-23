/**
 * Product Search JavaScript
 * Handles dynamic search, filtering, and async operations
 */

// Debounce function for search input
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Update filters when category or vendor changes
function updateFilters() {
    const categoryForm = document.getElementById('categoryFilterForm');
    const vendorForm = document.getElementById('vendorFilterForm');
    
    // Determine which form to submit
    const activeForm = event.target.closest('form');
    if (activeForm) {
        activeForm.submit();
    }
}

// Async search function
async function performAsyncSearch(query) {
    if (!query || query.trim().length < 2) {
        return;
    }

    try {
        const response = await fetch(`../Actions/product_actions.php?action=search_products&query=${encodeURIComponent(query)}`);
        const result = await response.json();

        if (result.status) {
            // Update results dynamically (if using AJAX)
            updateSearchResults(result.products);
        }
    } catch (error) {
        console.error('Search error:', error);
    }
}

// Update search results dynamically
function updateSearchResults(products) {
    const productsGrid = document.querySelector('.products-grid');
    if (!productsGrid) return;

    productsGrid.innerHTML = products.map(product => `
        <a href="single_product.php?id=${product.product_id}" class="product-card">
            <div class="product-image-wrapper">
                <img src="../${product.product_image || 'uploads/placeholder.jpg'}" 
                     alt="${product.product_title}"
                     onerror="this.onerror=null; this.src='../uploads/placeholder.jpg';">
            </div>
            <div class="product-info">
                <div class="product-category-tag">${product.cat_name || 'Uncategorized'}</div>
                <h3 class="product-name">${product.product_title}</h3>
                <p class="product-vendor">${product.vendor_name || 'Unknown Vendor'}</p>
                <div class="product-price">₵${parseFloat(product.product_price).toFixed(2)}</div>
                <button class="btn-add-to-cart" onclick="event.preventDefault(); addToCart(${product.product_id});">
                    <i class="fas fa-shopping-cart"></i>
                    Add to Cart
                </button>
            </div>
        </a>
    `).join('');
}

// Add to cart function (placeholder)
function addToCart(productId) {
    // Placeholder - will be implemented with actual cart functionality
    alert('Add to cart functionality will be implemented soon. Product ID: ' + productId);
}

// Initialize search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchForm = document.getElementById('searchForm');

    if (searchInput) {
        // Debounced search for real-time suggestions (optional)
        const debouncedSearch = debounce((query) => {
            // You can implement real-time search suggestions here
            console.log('Searching for:', query);
        }, 500);

        searchInput.addEventListener('input', function() {
            debouncedSearch(this.value);
        });

        // Handle form submission
        if (searchForm) {
            searchForm.addEventListener('submit', function(e) {
                const query = searchInput.value.trim();
                if (!query || query.length < 2) {
                    e.preventDefault();
                    alert('Please enter at least 2 characters to search.');
                    return false;
                }
            });
        }
    }

    // Dynamic category and vendor filter loading (if needed)
    loadFilterOptions();
});

// Load filter options dynamically
async function loadFilterOptions() {
    try {
        // Load categories
        const categoriesResponse = await fetch('../Actions/product_actions.php?action=get_categories');
        const categoriesResult = await categoriesResponse.json();

        // Load vendors
        const vendorsResponse = await fetch('../Actions/product_actions.php?action=get_vendors');
        const vendorsResult = await vendorsResponse.json();

        // Update filter dropdowns if needed
        // This is optional - filters are already loaded server-side
    } catch (error) {
        console.error('Error loading filter options:', error);
    }
}

// Composite search with multiple filters
async function performCompositeSearch(filters) {
    try {
        const params = new URLSearchParams();
        params.append('action', 'composite_search');
        
        Object.keys(filters).forEach(key => {
            if (filters[key] !== null && filters[key] !== '' && filters[key] !== 'all') {
                params.append(key, filters[key]);
            }
        });

        const response = await fetch(`../Actions/product_actions.php?${params.toString()}`);
        const result = await response.json();

        if (result.status) {
            return result.products;
        }
    } catch (error) {
        console.error('Composite search error:', error);
    }
    return [];
}

// Export functions for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        debounce,
        performAsyncSearch,
        performCompositeSearch,
        addToCart
    };
}


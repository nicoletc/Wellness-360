/**
 * Category Management JavaScript
 * Handles validation and asynchronous operations for category CRUD
 */

document.addEventListener('DOMContentLoaded', function() {
    // Load categories on page load
    loadCategories();

    // Form event listeners
    const addForm = document.getElementById('addCategoryForm');
    if (addForm) {
        addForm.addEventListener('submit', handleAddCategory);
    }

    const updateForm = document.getElementById('updateCategoryForm');
    if (updateForm) {
        updateForm.addEventListener('submit', handleUpdateCategory);
    }

    // Search functionality
    const searchInput = document.getElementById('categorySearch');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(handleSearch, 300));
    }

    // Filter functionality
    const filterSelect = document.getElementById('categoryFilter');
    if (filterSelect) {
        filterSelect.addEventListener('change', handleFilter);
    }
});

/**
 * Load all categories from the server
 */
async function loadCategories() {
    try {
        const response = await fetch('../Actions/fetch_category_action.php');
        const result = await response.json();

        if (result.status) {
            displayCategories(result.categories || []);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'Failed to load categories.',
                confirmButtonColor: '#7FB685'
            });
        }
    } catch (error) {
        console.error('Error loading categories:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Network error. Please check your connection and try again.',
            confirmButtonColor: '#7FB685'
        });
    }
}

/**
 * Display categories in the table
 */
function displayCategories(categories) {
    const tbody = document.querySelector('#categoriesTable tbody');
    if (!tbody) return;

    if (categories.length === 0) {
        tbody.innerHTML = '<tr><td colspan="2" style="text-align: center;">No categories found.</td></tr>';
        return;
    }

    tbody.innerHTML = categories.map(category => `
        <tr>
            <td>
                <div class="vendor-cell">
                    <div class="vendor-avatar">${getInitials(category.cat_name)}</div>
                    <span>${escapeHtml(category.cat_name)}</span>
                </div>
            </td>
            <td>
                <div class="action-buttons">
                    <button class="btn-icon" title="Edit" onclick="openEditModal(${category.cat_id}, '${escapeHtml(category.cat_name)}')">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon" title="View" onclick="viewCategory(${category.cat_id}, '${escapeHtml(category.cat_name)}')">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-icon" title="Delete" onclick="deleteCategory(${category.cat_id}, '${escapeHtml(category.cat_name)}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

/**
 * Handle add category form submission
 */
async function handleAddCategory(e) {
    e.preventDefault();

    const form = e.target;
    const catNameInput = form.querySelector('[name="cat_name"]');
    const submitBtn = form.querySelector('button[type="submit"]');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoader = submitBtn.querySelector('.btn-loader');

    const catName = catNameInput.value.trim();

    // Validation
    if (!validateCategoryName(catName)) {
        return;
    }

    // Show loading state
    if (btnText) btnText.style.display = 'none';
    if (btnLoader) btnLoader.style.display = 'inline-block';
    submitBtn.disabled = true;

    const formData = new FormData();
    formData.append('cat_name', catName);

    try {
        const response = await fetch('../Actions/add_category_action.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        // Reset button state
        if (btnText) btnText.style.display = 'inline';
        if (btnLoader) btnLoader.style.display = 'none';
        submitBtn.disabled = false;

        if (result.status) {
            // Close modal first
            closeAddModal();
            
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: result.message || 'Category added successfully.',
                confirmButtonColor: '#7FB685',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                form.reset();
                loadCategories();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'Failed to add category. Please try again.',
                confirmButtonColor: '#7FB685'
            });
        }
    } catch (error) {
        console.error('Add category error:', error);
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

/**
 * Handle update category form submission
 */
async function handleUpdateCategory(e) {
    e.preventDefault();

    const form = e.target;
    const catIdInput = form.querySelector('[name="cat_id"]');
    const catNameInput = form.querySelector('[name="cat_name"]');
    const submitBtn = form.querySelector('button[type="submit"]');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoader = submitBtn.querySelector('.btn-loader');

    const catId = catIdInput.value;
    const catName = catNameInput.value.trim();

    // Validation
    if (!validateCategoryName(catName)) {
        return;
    }

    // Show loading state
    if (btnText) btnText.style.display = 'none';
    if (btnLoader) btnLoader.style.display = 'inline-block';
    submitBtn.disabled = true;

    const formData = new FormData();
    formData.append('cat_id', catId);
    formData.append('cat_name', catName);

    try {
        const response = await fetch('../Actions/update_category_action.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        // Reset button state
        if (btnText) btnText.style.display = 'inline';
        if (btnLoader) btnLoader.style.display = 'none';
        submitBtn.disabled = false;

        if (result.status) {
            // Close modal first
            closeUpdateModal();
            
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: result.message || 'Category updated successfully.',
                confirmButtonColor: '#7FB685',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                loadCategories();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'Failed to update category. Please try again.',
                confirmButtonColor: '#7FB685'
            });
        }
    } catch (error) {
        console.error('Update category error:', error);
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

/**
 * Delete a category
 */
async function deleteCategory(catId, catName) {
    const result = await Swal.fire({
        title: 'Delete Category?',
        text: `Are you sure you want to delete "${catName}"? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d4183d',
        cancelButtonColor: '#7FB685',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    });

    if (!result.isConfirmed) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('cat_id', catId);

        const response = await fetch('../Actions/delete_category_action.php', {
            method: 'POST',
            body: formData
        });

        const deleteResult = await response.json();

        if (deleteResult.status) {
            Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: deleteResult.message || 'Category deleted successfully.',
                confirmButtonColor: '#7FB685',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                loadCategories();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: deleteResult.message || 'Failed to delete category. Please try again.',
                confirmButtonColor: '#7FB685'
            });
        }
    } catch (error) {
        console.error('Delete category error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Network error. Please check your connection and try again.',
            confirmButtonColor: '#7FB685'
        });
    }
}

/**
 * Open edit modal with category data
 */
function openEditModal(catId, catName) {
    const modal = document.getElementById('updateCategoryModal');
    if (!modal) return;

    const form = document.getElementById('updateCategoryForm');
    if (form) {
        form.querySelector('[name="cat_id"]').value = catId;
        form.querySelector('[name="cat_name"]').value = catName;
    }

    // Show modal (using Bootstrap if available, otherwise simple display)
    if (typeof bootstrap !== 'undefined') {
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    } else {
        modal.style.display = 'block';
    }
}

/**
 * View category details
 */
async function viewCategory(catId, catName) {
    try {
        const response = await fetch('../Actions/fetch_category_action.php');
        const result = await response.json();

        if (result.status) {
            const category = result.categories.find(cat => cat.cat_id == catId);
            
            if (category) {
                // Get product count
                const productCount = await getProductCount(catId);
                
                // Show view modal
                openViewModal(category, productCount);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Category not found.',
                    confirmButtonColor: '#7FB685'
                });
            }
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'Failed to load category details.',
                confirmButtonColor: '#7FB685'
            });
        }
    } catch (error) {
        console.error('View category error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Network error. Please check your connection and try again.',
            confirmButtonColor: '#7FB685'
        });
    }
}

/**
 * Get product count for a category
 */
async function getProductCount(catId) {
    try {
        const response = await fetch('../Actions/fetch_category_action.php');
        const result = await response.json();
        
        if (result.status) {
            const category = result.categories.find(cat => cat.cat_id == catId);
            return category ? (category.product_count || 0) : 0;
        }
        return 0;
    } catch (error) {
        return 0;
    }
}

/**
 * Open view modal with category information
 */
function openViewModal(category, productCount) {
    const modal = document.getElementById('viewCategoryModal');
    if (!modal) return;

    // Populate modal content
    document.getElementById('view_cat_id').textContent = category.cat_id;
    document.getElementById('view_cat_name').textContent = category.cat_name;
    document.getElementById('view_product_count').textContent = productCount || category.product_count || 0;

    // Show modal
    modal.style.display = 'block';
}

/**
 * Close view modal
 */
function closeViewModal() {
    const modal = document.getElementById('viewCategoryModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

/**
 * Validate category name
 */
function validateCategoryName(catName) {
    if (!catName || catName.trim().length === 0) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Category name is required.',
            confirmButtonColor: '#7FB685'
        });
        return false;
    }

    if (catName.length < 2) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Category name must be at least 2 characters long.',
            confirmButtonColor: '#7FB685'
        });
        return false;
    }

    if (catName.length > 100) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Category name must not exceed 100 characters.',
            confirmButtonColor: '#7FB685'
        });
        return false;
    }

    // Check for valid characters (letters, numbers, spaces, hyphens, underscores)
    const nameRegex = /^[a-zA-Z0-9\s\-_]+$/;
    if (!nameRegex.test(catName)) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Category name can only contain letters, numbers, spaces, hyphens, and underscores.',
            confirmButtonColor: '#7FB685'
        });
        return false;
    }

    return true;
}

/**
 * Handle search
 */
function handleSearch(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#categoriesTable tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
}

/**
 * Handle filter
 */
function handleFilter(e) {
    const filterValue = e.target.value;
    // Filter logic can be implemented here
    loadCategories();
}

/**
 * Utility: Get initials from category name
 */
function getInitials(name) {
    if (!name) return 'CA';
    const words = name.trim().split(/\s+/);
    if (words.length >= 2) {
        return (words[0][0] + words[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
}

/**
 * Close add modal
 */
function closeAddModal() {
    const modal = document.getElementById('addCategoryModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

/**
 * Close update modal
 */
function closeUpdateModal() {
    const modal = document.getElementById('updateCategoryModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

/**
 * Utility: Format date
 */
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

/**
 * Utility: Escape HTML
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Utility: Debounce function
 */
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


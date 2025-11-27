/**
 * Product Management JavaScript
 * Handles validation and asynchronous operations for product CRUD
 */

let categories = [];
let vendors = [];

document.addEventListener('DOMContentLoaded', function() {
    // Load categories and vendors for dropdowns
    loadCategories();
    loadVendors();
    
    // Load products on page load
    loadProducts();

    // Form event listeners
    const addForm = document.getElementById('addProductForm');
    if (addForm) {
        addForm.addEventListener('submit', handleAddProduct);
    }

    const updateForm = document.getElementById('updateProductForm');
    if (updateForm) {
        updateForm.addEventListener('submit', handleUpdateProduct);
    }

    // Image upload handler
    const imageInput = document.getElementById('product_image');
    if (imageInput) {
        imageInput.addEventListener('change', handleImagePreview);
    }

    const updateImageInput = document.getElementById('update_product_image');
    if (updateImageInput) {
        updateImageInput.addEventListener('change', handleUpdateImagePreview);
    }

    // Search functionality
    const searchInput = document.getElementById('productSearch');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(handleSearch, 300));
    }
});

/**
 * Load categories for dropdown
 */
async function loadCategories() {
    try {
        const response = await fetch('../Actions/fetch_categories_action.php');
        const result = await response.json();

        if (result.status) {
            categories = result.categories || [];
            populateCategoryDropdowns();
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

/**
 * Load vendors for dropdown (used as brands)
 */
async function loadVendors() {
    try {
        const response = await fetch('../Actions/fetch_vendors_action.php');
        const result = await response.json();

        if (result.status) {
            vendors = result.vendors || [];
            populateVendorDropdowns();
        }
    } catch (error) {
        console.error('Error loading vendors:', error);
    }
}

/**
 * Populate category dropdowns
 */
function populateCategoryDropdowns() {
    const addSelect = document.getElementById('add_product_cat');
    const updateSelect = document.getElementById('update_product_cat');

    [addSelect, updateSelect].forEach(select => {
        if (select) {
            select.innerHTML = '<option value="">Select Category</option>';
            categories.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat.cat_id;
                option.textContent = cat.cat_name;
                select.appendChild(option);
            });
        }
    });
}

/**
 * Populate vendor dropdowns (brands)
 */
function populateVendorDropdowns() {
    const addSelect = document.getElementById('add_product_vendor');
    const updateSelect = document.getElementById('update_product_vendor');

    [addSelect, updateSelect].forEach(select => {
        if (select) {
            select.innerHTML = '<option value="">Select Vendor (Brand)</option>';
            vendors.forEach(vendor => {
                const option = document.createElement('option');
                option.value = vendor.vendor_id;
                option.textContent = vendor.vendor_name;
                select.appendChild(option);
            });
        }
    });
}

/**
 * Load all products from the server
 */
async function loadProducts() {
    const tbody = document.querySelector('#productsTable tbody');
    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Loading products...</td></tr>';
    }

    try {
        const response = await fetch('../Actions/fetch_product_action.php');
        const result = await response.json();

        if (result.status) {
            displayProducts(result.products || []);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'Failed to load products.',
                confirmButtonColor: '#7FB685'
            });
        }
    } catch (error) {
        console.error('Error loading products:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Network error. Please check your connection and try again.',
            confirmButtonColor: '#7FB685'
        });
    }
}

/**
 * Display products in the table
 */
function displayProducts(products) {
    const tbody = document.querySelector('#productsTable tbody');
    if (!tbody) return;

    if (products.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No products found.</td></tr>';
        return;
    }

    tbody.innerHTML = products.map(product => {
        // Check if product_image exists and is not empty
        const hasImage = product.product_image && product.product_image.trim() !== '';
        // Database stores paths as ../../uploads/... which is correct for Admin/ folder
        // Don't prepend ../ since the path already includes the correct relative path
        let imageSrc = '';
        if (hasImage) {
            // If path already starts with ../../ or ../, use as-is
            if (product.product_image.startsWith('../../') || product.product_image.startsWith('../')) {
                imageSrc = escapeHtml(product.product_image);
            } else if (product.product_image.startsWith('uploads/')) {
                // If it starts with uploads/, add ../../
                imageSrc = '../../' + escapeHtml(product.product_image);
            } else {
                // Default: assume it needs ../../uploads/ prefix
                imageSrc = '../../uploads/' + escapeHtml(product.product_image);
            }
        }
        
        return `
        <tr>
            <td>
                <div class="product-cell">
                    ${hasImage ? 
                        `<img src="${imageSrc}" alt="${escapeHtml(product.product_title)}" class="product-thumbnail" onerror="this.onerror=null; this.style.display='none'; this.parentElement.innerHTML='<div class=\\'product-image-placeholder\\'>IMG</div><span>${escapeHtml(product.product_title)}</span>';">` :
                        '<div class="product-image-placeholder">IMG</div>'
                    }
                    <span>${escapeHtml(product.product_title)}</span>
                </div>
            </td>
            <td>${escapeHtml(product.cat_name || 'N/A')}</td>
            <td>${escapeHtml(product.vendor_name || 'N/A')}</td>
            <td>₵${parseFloat(product.product_price || 0).toFixed(2)}</td>
            <td>${product.stock || 0}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-icon" title="Edit" onclick="openEditModal(${product.product_id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon" title="View" onclick="viewProduct(${product.product_id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-icon" title="Delete" onclick="deleteProduct(${product.product_id}, '${escapeHtml(product.product_title)}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `;
    }).join('');
}

/**
 * Handle image preview for add form
 */
function handleImagePreview(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('add_image_preview');
    
    if (file && preview) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}

/**
 * Handle image preview for update form
 */
function handleUpdateImagePreview(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('update_image_preview');
    
    if (file && preview) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}

/**
 * Upload product image
 */
async function uploadProductImage(file, productId = 0) {
    if (!file) {
        return { status: 'success', path: '' };
    }

    const formData = new FormData();
    formData.append('product_image', file);
    formData.append('product_id', productId);

    try {
        const response = await fetch('../Actions/upload_product_image_action.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        
        // Convert response format to match our expected format
        if (result.status === 'success') {
            return {
                status: true,
                image_path: result.path
            };
        } else {
            return {
                status: false,
                message: result.message || 'Failed to upload image.'
            };
        }
    } catch (error) {
        console.error('Image upload error:', error);
        return {
            status: false,
            message: 'Failed to upload image. Please try again.'
        };
    }
}

/**
 * Handle add product form submission
 */
async function handleAddProduct(e) {
    e.preventDefault();

    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoader = submitBtn.querySelector('.btn-loader');

    const productTitle = form.querySelector('[name="product_title"]').value.trim();
    const productCat = form.querySelector('[name="product_cat"]').value;
    const productVendor = form.querySelector('[name="product_vendor"]').value;
    const productPrice = form.querySelector('[name="product_price"]').value;
    const productDesc = form.querySelector('[name="product_desc"]').value.trim();
    const productKeywords = form.querySelector('[name="product_keywords"]').value.trim();
    const stock = form.querySelector('[name="stock"]').value;
    const imageFile = form.querySelector('[name="product_image"]').files[0];

    // Validation
    if (!validateProductForm(productTitle, productCat, productVendor, productPrice, stock)) {
        return;
    }

    // Show loading state
    if (btnText) btnText.style.display = 'none';
    if (btnLoader) btnLoader.style.display = 'inline-block';
    submitBtn.disabled = true;

    try {
        // First, add product without image to get product_id
        const formData = new FormData();
        formData.append('product_title', productTitle);
        formData.append('product_cat', productCat);
        formData.append('product_vendor', productVendor);
        formData.append('product_price', productPrice);
        formData.append('product_desc', productDesc);
        formData.append('product_keywords', productKeywords);
        formData.append('stock', stock);
        formData.append('product_image', ''); // Empty initially

        let addResponse = await fetch('../Actions/add_product_action.php', {
            method: 'POST',
            body: formData
        });

        let addResult = await addResponse.json();

        if (!addResult.status) {
            throw new Error(addResult.message || 'Failed to add product.');
        }

        const productId = addResult.product_id;

        // Upload image if provided (database is updated directly in upload action)
        if (imageFile) {
            const uploadResult = await uploadProductImage(imageFile, productId);
            if (!uploadResult.status) {
                // Product was added but image upload failed
                Swal.fire({
                    icon: 'warning',
                    title: 'Product Added',
                    text: 'Product added but image upload failed: ' + (uploadResult.message || 'Unknown error'),
                    confirmButtonColor: '#7FB685'
                });
            }
            // Note: Image path is automatically saved to database in upload_product_image_action.php
        }

        // Reset button state
        if (btnText) btnText.style.display = 'inline';
        if (btnLoader) btnLoader.style.display = 'none';
        submitBtn.disabled = false;

        // Close modal and show success
        closeAddModal();
        
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: addResult.message || 'Product added successfully.',
            confirmButtonColor: '#7FB685',
            timer: 1500,
            showConfirmButton: false
        }).then(() => {
            form.reset();
            const preview = document.getElementById('add_image_preview');
            if (preview) preview.style.display = 'none';
            loadProducts();
        });

    } catch (error) {
        console.error('Add product error:', error);
        if (btnText) btnText.style.display = 'inline';
        if (btnLoader) btnLoader.style.display = 'none';
        submitBtn.disabled = false;
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Failed to add product. Please try again.',
            confirmButtonColor: '#7FB685'
        });
    }
}

/**
 * Handle update product form submission
 */
async function handleUpdateProduct(e) {
    e.preventDefault();

    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoader = submitBtn.querySelector('.btn-loader');

    const productId = form.querySelector('[name="product_id"]').value;
    const productTitle = form.querySelector('[name="product_title"]').value.trim();
    const productCat = form.querySelector('[name="product_cat"]').value;
    const productVendor = form.querySelector('[name="product_vendor"]').value;
    const productPrice = form.querySelector('[name="product_price"]').value;
    const productDesc = form.querySelector('[name="product_desc"]').value.trim();
    const productKeywords = form.querySelector('[name="product_keywords"]').value.trim();
    const stock = form.querySelector('[name="stock"]').value;
    const imageFile = form.querySelector('[name="product_image"]').files[0];
    const currentImagePath = form.querySelector('[name="current_image_path"]').value;

    // Validation
    if (!validateProductForm(productTitle, productCat, productVendor, productPrice, stock)) {
        return;
    }

    // Show loading state
    if (btnText) btnText.style.display = 'none';
    if (btnLoader) btnLoader.style.display = 'inline-block';
    submitBtn.disabled = true;

    try {
        let imagePath = currentImagePath; // Keep current image by default

        // Upload new image if provided
        if (imageFile) {
            const uploadResult = await uploadProductImage(imageFile, productId);
            if (uploadResult.status) {
                imagePath = uploadResult.image_path;
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Image Upload Failed',
                    text: uploadResult.message || 'Failed to upload new image. Product will be updated with current image.',
                    confirmButtonColor: '#7FB685'
                });
            }
        }

        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('product_title', productTitle);
        formData.append('product_cat', productCat);
        formData.append('product_vendor', productVendor);
        formData.append('product_price', productPrice);
        formData.append('product_desc', productDesc);
        formData.append('product_keywords', productKeywords);
        formData.append('stock', stock);
        formData.append('product_image', imagePath);

        const response = await fetch('../Actions/update_product_action.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        // Reset button state
        if (btnText) btnText.style.display = 'inline';
        if (btnLoader) btnLoader.style.display = 'none';
        submitBtn.disabled = false;

        if (result.status) {
            closeUpdateModal();
            
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: result.message || 'Product updated successfully.',
                confirmButtonColor: '#7FB685',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                loadProducts();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'Failed to update product. Please try again.',
                confirmButtonColor: '#7FB685'
            });
        }
    } catch (error) {
        console.error('Update product error:', error);
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
 * Open edit modal with product data
 */
async function openEditModal(productId) {
    try {
        const response = await fetch(`../Actions/fetch_product_action.php?product_id=${productId}`);
        const result = await response.json();

        if (result.status && result.product) {
            const product = result.product;
            const form = document.getElementById('updateProductForm');
            
            if (form) {
                form.querySelector('[name="product_id"]').value = product.product_id;
                form.querySelector('[name="product_title"]').value = product.product_title;
                form.querySelector('[name="product_cat"]').value = product.product_cat;
                form.querySelector('[name="product_vendor"]').value = product.product_vendor;
                form.querySelector('[name="product_price"]').value = product.product_price;
                form.querySelector('[name="product_desc"]').value = product.product_desc || '';
                form.querySelector('[name="product_keywords"]').value = product.product_keywords || '';
                form.querySelector('[name="stock"]').value = product.stock || 0;
                form.querySelector('[name="current_image_path"]').value = product.product_image || '';

                // Set image preview
                const preview = document.getElementById('update_image_preview');
                if (preview && product.product_image) {
                    // Database stores paths as ../../uploads/... which is correct for Admin/ folder
                    let imageSrc = product.product_image;
                    if (!imageSrc.startsWith('../../') && !imageSrc.startsWith('../')) {
                        if (imageSrc.startsWith('uploads/')) {
                            imageSrc = '../../' + imageSrc;
                        } else {
                            imageSrc = '../../uploads/' + imageSrc;
                        }
                    }
                    preview.src = imageSrc;
                    preview.style.display = 'block';
                } else if (preview) {
                    preview.style.display = 'none';
                }
            }

            // Show modal
            document.getElementById('updateProductModal').style.display = 'block';
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'Product not found.',
                confirmButtonColor: '#7FB685'
            });
        }
    } catch (error) {
        console.error('Error loading product:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Network error. Please check your connection and try again.',
            confirmButtonColor: '#7FB685'
        });
    }
}

/**
 * View product details
 */
async function viewProduct(productId) {
    try {
        const response = await fetch(`../Actions/fetch_product_action.php?product_id=${productId}`);
        const result = await response.json();

        if (result.status && result.product) {
            openViewModal(result.product);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'Product not found.',
                confirmButtonColor: '#7FB685'
            });
        }
    } catch (error) {
        console.error('View product error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Network error. Please check your connection and try again.',
            confirmButtonColor: '#7FB685'
        });
    }
}

/**
 * Open view modal with product information
 */
function openViewModal(product) {
    const modal = document.getElementById('viewProductModal');
    if (!modal) return;

    document.getElementById('view_product_id').textContent = product.product_id;
    document.getElementById('view_product_title').textContent = product.product_title;
    document.getElementById('view_product_cat').textContent = product.cat_name || 'N/A';
    document.getElementById('view_product_vendor').textContent = product.vendor_name || 'N/A';
    document.getElementById('view_product_price').textContent = '₵' + parseFloat(product.product_price || 0).toFixed(2);
    document.getElementById('view_product_desc').textContent = product.product_desc || 'N/A';
    document.getElementById('view_product_keywords').textContent = product.product_keywords || 'N/A';
    document.getElementById('view_product_stock').textContent = product.stock || 0;
    
    const imagePreview = document.getElementById('view_product_image');
    if (imagePreview && product.product_image) {
        // Database stores paths as ../../uploads/... which is correct for Admin/ folder
        let imageSrc = product.product_image;
        if (!imageSrc.startsWith('../../') && !imageSrc.startsWith('../')) {
            if (imageSrc.startsWith('uploads/')) {
                imageSrc = '../../' + imageSrc;
            } else {
                imageSrc = '../../uploads/' + imageSrc;
            }
        }
        imagePreview.src = imageSrc;
        imagePreview.style.display = 'block';
    } else if (imagePreview) {
        imagePreview.style.display = 'none';
    }

    modal.style.display = 'block';
}

/**
 * Delete a product
 */
async function deleteProduct(productId, productTitle) {
    const result = await Swal.fire({
        title: 'Delete Product?',
        text: `Are you sure you want to delete "${productTitle}"? This action cannot be undone.`,
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
        formData.append('product_id', productId);

        const response = await fetch('../Actions/delete_product_action.php', {
            method: 'POST',
            body: formData
        });

        const deleteResult = await response.json();

        if (deleteResult.status) {
            Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: deleteResult.message || 'Product deleted successfully.',
                confirmButtonColor: '#7FB685',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                loadProducts();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: deleteResult.message || 'Failed to delete product. Please try again.',
                confirmButtonColor: '#7FB685'
            });
        }
    } catch (error) {
        console.error('Delete product error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Network error. Please check your connection and try again.',
            confirmButtonColor: '#7FB685'
        });
    }
}

/**
 * Validate product form
 */
function validateProductForm(productTitle, productCat, productVendor, productPrice, stock) {
    if (!productTitle || productTitle.trim().length === 0) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Product title is required.',
            confirmButtonColor: '#7FB685'
        });
        return false;
    }

    if (productTitle.length < 3) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Product title must be at least 3 characters long.',
            confirmButtonColor: '#7FB685'
        });
        return false;
    }

    if (!productCat || productCat <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Please select a category.',
            confirmButtonColor: '#7FB685'
        });
        return false;
    }

    if (!productVendor || productVendor <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Please select a vendor (brand).',
            confirmButtonColor: '#7FB685'
        });
        return false;
    }

    if (!productPrice || parseFloat(productPrice) < 0) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Product price is required and must be 0 or greater.',
            confirmButtonColor: '#7FB685'
        });
        return false;
    }

    if (parseInt(stock) < 0) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Stock cannot be negative.',
            confirmButtonColor: '#7FB685'
        });
        return false;
    }

    return true;
}

/**
 * Close add modal
 */
function closeAddModal() {
    const modal = document.getElementById('addProductModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

/**
 * Close update modal
 */
function closeUpdateModal() {
    const modal = document.getElementById('updateProductModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

/**
 * Close view modal
 */
function closeViewModal() {
    const modal = document.getElementById('viewProductModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

/**
 * Handle search
 */
function handleSearch(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#productsTable tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
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


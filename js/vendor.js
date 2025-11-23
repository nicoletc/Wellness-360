/**
 * Vendor Management JavaScript
 * Handles validation and asynchronous operations for vendor CRUD
 */

document.addEventListener('DOMContentLoaded', function() {
    // Load vendors on page load
    loadVendors();

    // Form event listeners
    const addForm = document.getElementById('addVendorForm');
    if (addForm) {
        addForm.addEventListener('submit', handleAddVendor);
    }

    const updateForm = document.getElementById('updateVendorForm');
    if (updateForm) {
        updateForm.addEventListener('submit', handleUpdateVendor);
    }

    // Search functionality
    const searchInput = document.getElementById('vendorSearch');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(handleSearch, 300));
    }

    // Filter functionality
    const filterSelect = document.getElementById('vendorFilter');
    if (filterSelect) {
        filterSelect.addEventListener('change', handleFilter);
    }
});

/**
 * Load all vendors from the server
 */
async function loadVendors() {
    try {
        const response = await fetch('../Actions/fetch_vendor_action.php');
        const result = await response.json();

        if (result.status) {
            displayVendors(result.vendors || []);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'Failed to load vendors.',
                confirmButtonColor: '#7FB685'
            });
        }
    } catch (error) {
        console.error('Error loading vendors:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Network error. Please check your connection and try again.',
            confirmButtonColor: '#7FB685'
        });
    }
}

/**
 * Display vendors in the table
 */
function displayVendors(vendors) {
    const tbody = document.querySelector('#vendorsTable tbody');
    if (!tbody) return;

    if (vendors.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No vendors found.</td></tr>';
        return;
    }

    tbody.innerHTML = vendors.map(vendor => `
        <tr>
            <td>
                <div class="vendor-cell">
                    <div class="vendor-avatar">${getInitials(vendor.vendor_name)}</div>
                    <span>${escapeHtml(vendor.vendor_name)}</span>
                </div>
            </td>
            <td>${escapeHtml(vendor.vendor_email)}</td>
            <td>${escapeHtml(vendor.vendor_contact || 'N/A')}</td>
            <td>${vendor.product_stock || 0}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-icon" title="Edit" onclick="openEditModal(${vendor.vendor_id}, '${escapeHtml(vendor.vendor_name)}', '${escapeHtml(vendor.vendor_email)}', '${escapeHtml(vendor.vendor_contact || '')}', ${vendor.product_stock || 0})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon" title="View" onclick="viewVendor(${vendor.vendor_id}, '${escapeHtml(vendor.vendor_name)}')">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-icon" title="Delete" onclick="deleteVendor(${vendor.vendor_id}, '${escapeHtml(vendor.vendor_name)}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

/**
 * Handle add vendor form submission
 */
async function handleAddVendor(e) {
    e.preventDefault();

    const form = e.target;
    const vendorNameInput = form.querySelector('[name="vendor_name"]');
    const vendorEmailInput = form.querySelector('[name="vendor_email"]');
    const vendorContactInput = form.querySelector('[name="vendor_contact"]');
    const productStockInput = form.querySelector('[name="product_stock"]');
    const submitBtn = form.querySelector('button[type="submit"]');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoader = submitBtn.querySelector('.btn-loader');

    const vendorName = vendorNameInput.value.trim();
    const vendorEmail = vendorEmailInput.value.trim();
    const vendorContact = vendorContactInput.value.trim();
    const productStock = parseInt(productStockInput.value) || 0;

    // Validation
    if (!validateVendorForm(vendorName, vendorEmail, productStock)) {
        return;
    }

    // Show loading state
    if (btnText) btnText.style.display = 'none';
    if (btnLoader) btnLoader.style.display = 'inline-block';
    submitBtn.disabled = true;

    const formData = new FormData();
    formData.append('vendor_name', vendorName);
    formData.append('vendor_email', vendorEmail);
    formData.append('vendor_contact', vendorContact);
    formData.append('product_stock', productStock);

    try {
        const response = await fetch('../Actions/add_vendor_action.php', {
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
                text: result.message || 'Vendor added successfully.',
                confirmButtonColor: '#7FB685',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                form.reset();
                loadVendors();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'Failed to add vendor. Please try again.',
                confirmButtonColor: '#7FB685'
            });
        }
    } catch (error) {
        console.error('Add vendor error:', error);
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
 * Handle update vendor form submission
 */
async function handleUpdateVendor(e) {
    e.preventDefault();

    const form = e.target;
    const vendorIdInput = form.querySelector('[name="vendor_id"]');
    const vendorNameInput = form.querySelector('[name="vendor_name"]');
    const vendorEmailInput = form.querySelector('[name="vendor_email"]');
    const vendorContactInput = form.querySelector('[name="vendor_contact"]');
    const productStockInput = form.querySelector('[name="product_stock"]');
    const submitBtn = form.querySelector('button[type="submit"]');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoader = submitBtn.querySelector('.btn-loader');

    const vendorId = vendorIdInput.value;
    const vendorName = vendorNameInput.value.trim();
    const vendorEmail = vendorEmailInput.value.trim();
    const vendorContact = vendorContactInput.value.trim();
    const productStock = parseInt(productStockInput.value) || 0;

    // Validation
    if (!validateVendorForm(vendorName, vendorEmail, productStock)) {
        return;
    }

    // Show loading state
    if (btnText) btnText.style.display = 'none';
    if (btnLoader) btnLoader.style.display = 'inline-block';
    submitBtn.disabled = true;

    const formData = new FormData();
    formData.append('vendor_id', vendorId);
    formData.append('vendor_name', vendorName);
    formData.append('vendor_email', vendorEmail);
    formData.append('vendor_contact', vendorContact);
    formData.append('product_stock', productStock);

    try {
        const response = await fetch('../Actions/update_vendor_action.php', {
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
                text: result.message || 'Vendor updated successfully.',
                confirmButtonColor: '#7FB685',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                loadVendors();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'Failed to update vendor. Please try again.',
                confirmButtonColor: '#7FB685'
            });
        }
    } catch (error) {
        console.error('Update vendor error:', error);
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
 * Delete a vendor
 */
async function deleteVendor(vendorId, vendorName) {
    const result = await Swal.fire({
        title: 'Delete Vendor?',
        text: `Are you sure you want to delete "${vendorName}"? This action cannot be undone.`,
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
        formData.append('vendor_id', vendorId);

        const response = await fetch('../Actions/delete_vendor_action.php', {
            method: 'POST',
            body: formData
        });

        const deleteResult = await response.json();

        if (deleteResult.status) {
            Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: deleteResult.message || 'Vendor deleted successfully.',
                confirmButtonColor: '#7FB685',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                loadVendors();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: deleteResult.message || 'Failed to delete vendor. Please try again.',
                confirmButtonColor: '#7FB685'
            });
        }
    } catch (error) {
        console.error('Delete vendor error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Network error. Please check your connection and try again.',
            confirmButtonColor: '#7FB685'
        });
    }
}

/**
 * Open edit modal with vendor data
 */
function openEditModal(vendorId, vendorName, vendorEmail, vendorContact, productStock) {
    const modal = document.getElementById('updateVendorModal');
    if (!modal) return;

    const form = document.getElementById('updateVendorForm');
    if (form) {
        form.querySelector('[name="vendor_id"]').value = vendorId;
        form.querySelector('[name="vendor_name"]').value = vendorName;
        form.querySelector('[name="vendor_email"]').value = vendorEmail;
        form.querySelector('[name="vendor_contact"]').value = vendorContact || '';
        form.querySelector('[name="product_stock"]').value = productStock || 0;
    }

    // Show modal
    modal.style.display = 'block';
}

/**
 * View vendor details
 */
async function viewVendor(vendorId, vendorName) {
    try {
        const response = await fetch('../Actions/fetch_vendor_action.php');
        const result = await response.json();

        if (result.status) {
            const vendor = result.vendors.find(v => v.vendor_id == vendorId);
            
            if (vendor) {
                // Show view modal
                openViewModal(vendor);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Vendor not found.',
                    confirmButtonColor: '#7FB685'
                });
            }
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'Failed to load vendor details.',
                confirmButtonColor: '#7FB685'
            });
        }
    } catch (error) {
        console.error('View vendor error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Network error. Please check your connection and try again.',
            confirmButtonColor: '#7FB685'
        });
    }
}

/**
 * Open view modal with vendor information
 */
function openViewModal(vendor) {
    const modal = document.getElementById('viewVendorModal');
    if (!modal) return;

    // Populate modal content
    document.getElementById('view_vendor_id').textContent = vendor.vendor_id;
    document.getElementById('view_vendor_name').textContent = vendor.vendor_name;
    document.getElementById('view_vendor_email').textContent = vendor.vendor_email;
    document.getElementById('view_vendor_contact').textContent = vendor.vendor_contact || 'N/A';
    document.getElementById('view_product_stock').textContent = vendor.product_stock || 0;

    // Show modal
    modal.style.display = 'block';
}

/**
 * Close view modal
 */
function closeViewModal() {
    const modal = document.getElementById('viewVendorModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

/**
 * Close add modal
 */
function closeAddModal() {
    const modal = document.getElementById('addVendorModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

/**
 * Close update modal
 */
function closeUpdateModal() {
    const modal = document.getElementById('updateVendorModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

/**
 * Validate vendor form
 */
function validateVendorForm(vendorName, vendorEmail, productStock) {
    if (!vendorName || vendorName.trim().length === 0) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Vendor name is required.',
            confirmButtonColor: '#7FB685'
        });
        return false;
    }

    if (vendorName.length < 2) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Vendor name must be at least 2 characters long.',
            confirmButtonColor: '#7FB685'
        });
        return false;
    }

    if (vendorName.length > 100) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Vendor name must not exceed 100 characters.',
            confirmButtonColor: '#7FB685'
        });
        return false;
    }

    if (!vendorEmail || vendorEmail.trim().length === 0) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Vendor email is required.',
            confirmButtonColor: '#7FB685'
        });
        return false;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(vendorEmail)) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Please enter a valid email address.',
            confirmButtonColor: '#7FB685'
        });
        return false;
    }

    if (vendorEmail.length > 100) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Email must not exceed 100 characters.',
            confirmButtonColor: '#7FB685'
        });
        return false;
    }

    if (productStock < 0) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Product stock cannot be negative.',
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
    const rows = document.querySelectorAll('#vendorsTable tbody tr');

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
    loadVendors();
}

/**
 * Utility: Get initials from vendor name
 */
function getInitials(name) {
    if (!name) return 'VD';
    const words = name.trim().split(/\s+/);
    if (words.length >= 2) {
        return (words[0][0] + words[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
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


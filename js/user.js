/**
 * User Management JavaScript
 * Handles validation and asynchronous operations for user management (View and Delete only)
 */

document.addEventListener('DOMContentLoaded', function() {
    // Load users on page load
    loadUsers();

    // Search functionality
    const searchInput = document.getElementById('userSearch');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(handleSearch, 300));
    }

    // Filter functionality
    const filterSelect = document.getElementById('userFilter');
    if (filterSelect) {
        filterSelect.addEventListener('change', handleFilter);
    }
});

/**
 * Load all users from the server
 */
async function loadUsers() {
    try {
        const response = await fetch('../Actions/fetch_user_action.php');
        const result = await response.json();

        if (result.status) {
            displayUsers(result.users || []);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'Failed to load users.',
                confirmButtonColor: '#7FB685'
            });
        }
    } catch (error) {
        console.error('Error loading users:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Network error. Please check your connection and try again.',
            confirmButtonColor: '#7FB685'
        });
    }
}

/**
 * Display users in the table
 */
function displayUsers(users) {
    const tbody = document.querySelector('#usersTable tbody');
    if (!tbody) return;

    if (users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No users found.</td></tr>';
        return;
    }

    tbody.innerHTML = users.map(user => `
        <tr>
            <td>
                <div class="vendor-cell">
                    <div class="vendor-avatar">${getInitials(user.customer_name)}</div>
                    <span>${escapeHtml(user.customer_name)}</span>
                </div>
            </td>
            <td>${escapeHtml(user.customer_email)}</td>
            <td>${escapeHtml(user.customer_contact || 'N/A')}</td>
            <td>${formatDate(user.date_joined)}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-icon" title="View" onclick="viewUser(${user.customer_id}, '${escapeHtml(user.customer_name)}')">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-icon" title="Delete" onclick="deleteUser(${user.customer_id}, '${escapeHtml(user.customer_name)}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

/**
 * Delete a user (Disabled - shows alert)
 */
async function deleteUser(customerId, userName) {
    Swal.fire({
        icon: 'info',
        title: 'User Deletion Disabled',
        text: 'Users cannot be deleted from this interface. Please contact the system administrator for user management.',
        confirmButtonColor: '#7FB685',
        confirmButtonText: 'OK'
    });
}

/**
 * View user details
 */
async function viewUser(customerId, userName) {
    try {
        const response = await fetch('../Actions/fetch_user_action.php');
        const result = await response.json();

        if (result.status) {
            const user = result.users.find(u => u.customer_id == customerId);
            
            if (user) {
                // Show view modal
                openViewModal(user);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'User not found.',
                    confirmButtonColor: '#7FB685'
                });
            }
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'Failed to load user details.',
                confirmButtonColor: '#7FB685'
            });
        }
    } catch (error) {
        console.error('View user error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Network error. Please check your connection and try again.',
            confirmButtonColor: '#7FB685'
        });
    }
}

/**
 * Open view modal with user information
 */
function openViewModal(user) {
    const modal = document.getElementById('viewUserModal');
    if (!modal) return;

    // Populate modal content
    document.getElementById('view_user_id').textContent = user.customer_id;
    document.getElementById('view_user_name').textContent = user.customer_name;
    document.getElementById('view_user_email').textContent = user.customer_email;
    document.getElementById('view_user_contact').textContent = user.customer_contact || 'N/A';
    document.getElementById('view_user_role').textContent = user.user_role == 1 ? 'Admin' : 'Customer';
    document.getElementById('view_date_joined').textContent = formatDate(user.date_joined);

    // Show modal
    modal.style.display = 'block';
}

/**
 * Close view modal
 */
function closeViewModal() {
    const modal = document.getElementById('viewUserModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

/**
 * Handle search
 */
function handleSearch(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#usersTable tbody tr');

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
    loadUsers();
}

/**
 * Utility: Get initials from user name
 */
function getInitials(name) {
    if (!name) return 'US';
    const words = name.trim().split(/\s+/);
    if (words.length >= 2) {
        return (words[0][0] + words[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
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


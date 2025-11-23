<?php
/**
 * Vendors Management Page
 * Admin page for managing vendors (CRUD operations)
 */

require_once __DIR__ . '/../settings/core.php';

// Check if user is logged in and is admin
require_admin();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendors - Wellness 360 Admin</title>
    <link rel="stylesheet" href="../Css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <i class="fas fa-leaf"></i>
                    <span>Wellness 360</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <a href="overview.php" class="nav-item">
                    <i class="fas fa-th-large"></i>
                    <span>Overview</span>
                </a>
                <a href="users.php" class="nav-item">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
                <a href="categories.php" class="nav-item">
                    <i class="fas fa-tags"></i>
                    <span>Categories</span>
                </a>
                <a href="vendors.php" class="nav-item active">
                    <i class="fas fa-store"></i>
                    <span>Vendors</span>
                </a>
                <a href="products.php" class="nav-item">
                    <i class="fas fa-box"></i>
                    <span>Products</span>
                </a>
                <a href="articles.php" class="nav-item">
                    <i class="fas fa-file-alt"></i>
                    <span>Articles</span>
                </a>
                <a href="workshops.php" class="nav-item">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>Workshops</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <a href="../Actions/logout_action.php" class="btn-logout-sidebar">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-content">
                <div class="admin-header">
                    <div>
                        <h1 class="admin-title">Vendors Management</h1>
                        <p class="admin-subtitle">Manage all verified vendors</p>
                    </div>
                    <div class="admin-actions">
                        <button class="btn-admin-primary" onclick="openAddModal()">
                            <i class="fas fa-plus"></i>
                            Add New Vendor
                        </button>
                    </div>
                </div>

                <div class="admin-table-container">
                    <div class="table-filters">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="vendorSearch" placeholder="Search vendors...">
                        </div>
                        <select class="filter-select" id="vendorFilter">
                            <option>All Vendors</option>
                            <option>Verified</option>
                            <option>Pending Verification</option>
                            <option>Suspended</option>
                        </select>
                    </div>

                    <table class="admin-table" id="vendorsTable">
                        <thead>
                            <tr>
                                <th>Vendor Name</th>
                                <th>Email</th>
                                <th>Contact</th>
                                <th>Product Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" style="text-align: center;">Loading vendors...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Vendor Modal -->
    <div class="modal" id="addVendorModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Vendor</h2>
                <span class="close" onclick="closeAddModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="addVendorForm">
                    <div class="form-group">
                        <label for="add_vendor_name">Vendor Name <span class="required">*</span></label>
                        <input type="text" id="add_vendor_name" name="vendor_name" placeholder="Enter vendor name" required maxlength="100">
                        <small class="form-help">Vendor name must be between 2-100 characters.</small>
                    </div>
                    <div class="form-group">
                        <label for="add_vendor_email">Email <span class="required">*</span></label>
                        <input type="email" id="add_vendor_email" name="vendor_email" placeholder="Enter vendor email" required maxlength="100">
                        <small class="form-help">Email must be unique and valid.</small>
                    </div>
                    <div class="form-group">
                        <label for="add_vendor_contact">Contact Number</label>
                        <input type="text" id="add_vendor_contact" name="vendor_contact" placeholder="Enter contact number" maxlength="20">
                    </div>
                    <div class="form-group">
                        <label for="add_product_stock">Product Stock</label>
                        <input type="number" id="add_product_stock" name="product_stock" placeholder="Enter product stock" min="0" value="0">
                        <small class="form-help">Number of products in stock (must be 0 or greater).</small>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary" onclick="closeAddModal()">Cancel</button>
                        <button type="submit" class="btn-admin-primary">
                            <span class="btn-text">Add Vendor</span>
                            <span class="btn-loader" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i> Adding...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Vendor Modal -->
    <div class="modal" id="updateVendorModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Update Vendor</h2>
                <span class="close" onclick="closeUpdateModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="updateVendorForm">
                    <input type="hidden" name="vendor_id" id="update_vendor_id">
                    <div class="form-group">
                        <label for="update_vendor_name">Vendor Name <span class="required">*</span></label>
                        <input type="text" id="update_vendor_name" name="vendor_name" placeholder="Enter vendor name" required maxlength="100">
                        <small class="form-help">Vendor name must be between 2-100 characters.</small>
                    </div>
                    <div class="form-group">
                        <label for="update_vendor_email">Email <span class="required">*</span></label>
                        <input type="email" id="update_vendor_email" name="vendor_email" placeholder="Enter vendor email" required maxlength="100">
                        <small class="form-help">Email must be unique and valid.</small>
                    </div>
                    <div class="form-group">
                        <label for="update_vendor_contact">Contact Number</label>
                        <input type="text" id="update_vendor_contact" name="vendor_contact" placeholder="Enter contact number" maxlength="20">
                    </div>
                    <div class="form-group">
                        <label for="update_product_stock">Product Stock</label>
                        <input type="number" id="update_product_stock" name="product_stock" placeholder="Enter product stock" min="0" value="0">
                        <small class="form-help">Number of products in stock (must be 0 or greater).</small>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary" onclick="closeUpdateModal()">Cancel</button>
                        <button type="submit" class="btn-admin-primary">
                            <span class="btn-text">Update Vendor</span>
                            <span class="btn-loader" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i> Updating...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Vendor Modal -->
    <div class="modal" id="viewVendorModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Vendor Details</h2>
                <span class="close" onclick="closeViewModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="view-category-info">
                    <div class="info-row">
                        <span class="info-label">Vendor ID:</span>
                        <span class="info-value" id="view_vendor_id">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Vendor Name:</span>
                        <span class="info-value" id="view_vendor_name">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value" id="view_vendor_email">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Contact:</span>
                        <span class="info-value" id="view_vendor_contact">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Product Stock:</span>
                        <span class="info-value" id="view_product_stock">0</span>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-admin-primary" onclick="closeViewModal()">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/admin.js"></script>
    <script src="../js/vendor.js"></script>
    <script>
        // Modal functions
        function openAddModal() {
            document.getElementById('addVendorModal').style.display = 'block';
            document.getElementById('addVendorForm').reset();
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const addModal = document.getElementById('addVendorModal');
            const updateModal = document.getElementById('updateVendorModal');
            const viewModal = document.getElementById('viewVendorModal');
            if (event.target == addModal) {
                addModal.style.display = 'none';
            }
            if (event.target == updateModal) {
                updateModal.style.display = 'none';
            }
            if (event.target == viewModal) {
                viewModal.style.display = 'none';
            }
        }
    </script>
</body>
</html>

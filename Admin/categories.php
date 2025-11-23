<?php
/**
 * Categories Management Page
 * Admin page for managing categories (CRUD operations)
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
    <title>Categories - Wellness 360 Admin</title>
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
                <a href="categories.php" class="nav-item active">
                    <i class="fas fa-tags"></i>
                    <span>Categories</span>
                </a>
                <a href="vendors.php" class="nav-item">
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
                        <h1 class="admin-title">Categories Management</h1>
                        <p class="admin-subtitle">Manage product and content categories</p>
                    </div>
                    <div class="admin-actions">
                        <button class="btn-admin-primary" onclick="openAddModal()">
                            <i class="fas fa-plus"></i>
                            Add New Category
                        </button>
                    </div>
                </div>

                <div class="admin-table-container">
                    <div class="table-filters">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="categorySearch" placeholder="Search categories...">
                        </div>
                        <select class="filter-select" id="categoryFilter">
                            <option>All Categories</option>
                            <option>Product Categories</option>
                            <option>Content Categories</option>
                            <option>Active</option>
                            <option>Inactive</option>
                        </select>
                    </div>

                    <table class="admin-table" id="categoriesTable">
                        <thead>
                            <tr>
                                <th>Category Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="2" style="text-align: center;">Loading categories...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Category Modal -->
    <div class="modal" id="addCategoryModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Category</h2>
                <span class="close" onclick="closeAddModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="addCategoryForm">
                    <div class="form-group">
                        <label for="add_cat_name">Category Name <span class="required">*</span></label>
                        <input type="text" id="add_cat_name" name="cat_name" placeholder="Enter category name" required maxlength="100">
                        <small class="form-help">Category name must be unique and between 2-100 characters.</small>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary" onclick="closeAddModal()">Cancel</button>
                        <button type="submit" class="btn-admin-primary">
                            <span class="btn-text">Add Category</span>
                            <span class="btn-loader" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i> Adding...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Category Modal -->
    <div class="modal" id="updateCategoryModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Update Category</h2>
                <span class="close" onclick="closeUpdateModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="updateCategoryForm">
                    <input type="hidden" name="cat_id" id="update_cat_id">
                    <div class="form-group">
                        <label for="update_cat_name">Category Name <span class="required">*</span></label>
                        <input type="text" id="update_cat_name" name="cat_name" placeholder="Enter category name" required maxlength="100">
                        <small class="form-help">Category name must be unique and between 2-100 characters.</small>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary" onclick="closeUpdateModal()">Cancel</button>
                        <button type="submit" class="btn-admin-primary">
                            <span class="btn-text">Update Category</span>
                            <span class="btn-loader" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i> Updating...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Category Modal -->
    <div class="modal" id="viewCategoryModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Category Details</h2>
                <span class="close" onclick="closeViewModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="view-category-info">
                    <div class="info-row">
                        <span class="info-label">Category ID:</span>
                        <span class="info-value" id="view_cat_id">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Category Name:</span>
                        <span class="info-value" id="view_cat_name">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Products Count:</span>
                        <span class="info-value" id="view_product_count">0</span>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-admin-primary" onclick="closeViewModal()">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/admin.js"></script>
    <script src="../js/category.js"></script>
    <script>
        // Modal functions
        function openAddModal() {
            document.getElementById('addCategoryModal').style.display = 'block';
            document.getElementById('addCategoryForm').reset();
        }

        function closeAddModal() {
            document.getElementById('addCategoryModal').style.display = 'none';
        }

        function closeUpdateModal() {
            document.getElementById('updateCategoryModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const addModal = document.getElementById('addCategoryModal');
            const updateModal = document.getElementById('updateCategoryModal');
            const viewModal = document.getElementById('viewCategoryModal');
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

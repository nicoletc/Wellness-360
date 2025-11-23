<?php
/**
 * Products Management Page
 * Admin page for managing products (CRUD operations)
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
    <title>Products - Wellness 360 Admin</title>
    <link rel="stylesheet" href="../Css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .product-thumbnail {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 10px;
        }
        .product-image-placeholder {
            width: 50px;
            height: 50px;
            background: #E8DCC4;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            color: #6B7E75;
            margin-right: 10px;
        }
        .product-cell {
            display: flex;
            align-items: center;
        }
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            border-radius: 4px;
            display: none;
        }
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
    </style>
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
                <a href="vendors.php" class="nav-item">
                    <i class="fas fa-store"></i>
                    <span>Vendors</span>
                </a>
                <a href="products.php" class="nav-item active">
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
                        <h1 class="admin-title">Products Management</h1>
                        <p class="admin-subtitle">Manage all products in the shop</p>
                    </div>
                    <div class="admin-actions" style="flex-direction: column; align-items: flex-end; gap: 0.75rem;">
                        <button class="btn-admin-primary" onclick="openAddModal()" style="min-width: 200px;">
                            <i class="fas fa-plus"></i>
                            Add New Product
                        </button>
                        <a href="../View/shop.php" class="btn-admin-primary" target="_blank" style="min-width: 200px;">
                            <i class="fas fa-store"></i>
                            View Storefront
                        </a>
                    </div>
                </div>

                <div class="admin-table-container">
                    <div class="table-filters">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="productSearch" placeholder="Search products...">
                        </div>
                        <select class="filter-select" id="productFilter">
                            <option value="all">All Products</option>
                            <option value="in_stock">In Stock</option>
                            <option value="out_of_stock">Out of Stock</option>
                        </select>
                    </div>

                    <table class="admin-table" id="productsTable">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Vendor (Brand)</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" style="text-align: center;">Loading products...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Product Modal -->
    <div class="modal" id="addProductModal" style="display: none;">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h2>Add New Product</h2>
                <span class="close" onclick="closeAddModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="addProductForm">
                    <div class="form-group">
                        <label for="add_product_title">Product Title <span class="required">*</span></label>
                        <input type="text" id="add_product_title" name="product_title" placeholder="Enter product title" required maxlength="200">
                        <small class="form-help">Product title must be between 3-200 characters.</small>
                    </div>
                    <div class="form-group">
                        <label for="add_product_cat">Product Category <span class="required">*</span></label>
                        <select id="add_product_cat" name="product_cat" required>
                            <option value="">Select Category</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="add_product_vendor">Product Vendor (Brand) <span class="required">*</span></label>
                        <select id="add_product_vendor" name="product_vendor" required>
                            <option value="">Select Vendor (Brand)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="add_product_price">Product Price <span class="required">*</span></label>
                        <input type="number" id="add_product_price" name="product_price" placeholder="0.00" step="0.01" min="0" required>
                        <small class="form-help">Price in Ghana Cedis (₵).</small>
                    </div>
                    <div class="form-group">
                        <label for="add_product_desc">Product Description</label>
                        <textarea id="add_product_desc" name="product_desc" placeholder="Enter product description"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="add_product_keywords">Product Keywords</label>
                        <input type="text" id="add_product_keywords" name="product_keywords" placeholder="Enter keywords separated by commas">
                        <small class="form-help">Keywords for search optimization (e.g., organic, natural, wellness).</small>
                    </div>
                    <div class="form-group">
                        <label for="add_stock">Stock Quantity</label>
                        <input type="number" id="add_stock" name="stock" placeholder="0" min="0" value="0">
                        <small class="form-help">Number of items available in stock.</small>
                    </div>
                    <div class="form-group">
                        <label for="product_image">Product Image</label>
                        <input type="file" id="product_image" name="product_image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                        <small class="form-help">Upload product image (JPEG, PNG, GIF, or WebP, max 5MB).</small>
                        <img id="add_image_preview" class="image-preview" alt="Image preview">
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary" onclick="closeAddModal()">Cancel</button>
                        <button type="submit" class="btn-admin-primary">
                            <span class="btn-text">Add Product</span>
                            <span class="btn-loader" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i> Adding...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Product Modal -->
    <div class="modal" id="updateProductModal" style="display: none;">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h2>Update Product</h2>
                <span class="close" onclick="closeUpdateModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="updateProductForm">
                    <input type="hidden" name="product_id" id="update_product_id">
                    <input type="hidden" name="current_image_path" id="current_image_path">
                    <div class="form-group">
                        <label for="update_product_title">Product Title <span class="required">*</span></label>
                        <input type="text" id="update_product_title" name="product_title" placeholder="Enter product title" required maxlength="200">
                        <small class="form-help">Product title must be between 3-200 characters.</small>
                    </div>
                    <div class="form-group">
                        <label for="update_product_cat">Product Category <span class="required">*</span></label>
                        <select id="update_product_cat" name="product_cat" required>
                            <option value="">Select Category</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="update_product_vendor">Product Vendor (Brand) <span class="required">*</span></label>
                        <select id="update_product_vendor" name="product_vendor" required>
                            <option value="">Select Vendor (Brand)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="update_product_price">Product Price <span class="required">*</span></label>
                        <input type="number" id="update_product_price" name="product_price" placeholder="0.00" step="0.01" min="0" required>
                        <small class="form-help">Price in Ghana Cedis (₵).</small>
                    </div>
                    <div class="form-group">
                        <label for="update_product_desc">Product Description</label>
                        <textarea id="update_product_desc" name="product_desc" placeholder="Enter product description"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="update_product_keywords">Product Keywords</label>
                        <input type="text" id="update_product_keywords" name="product_keywords" placeholder="Enter keywords separated by commas">
                        <small class="form-help">Keywords for search optimization (e.g., organic, natural, wellness).</small>
                    </div>
                    <div class="form-group">
                        <label for="update_stock">Stock Quantity</label>
                        <input type="number" id="update_stock" name="stock" placeholder="0" min="0" value="0">
                        <small class="form-help">Number of items available in stock.</small>
                    </div>
                    <div class="form-group">
                        <label for="update_product_image">Product Image</label>
                        <input type="file" id="update_product_image" name="product_image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                        <small class="form-help">Upload new image to replace current one (JPEG, PNG, GIF, or WebP, max 5MB).</small>
                        <img id="update_image_preview" class="image-preview" alt="Image preview">
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary" onclick="closeUpdateModal()">Cancel</button>
                        <button type="submit" class="btn-admin-primary">
                            <span class="btn-text">Update Product</span>
                            <span class="btn-loader" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i> Updating...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Product Modal -->
    <div class="modal" id="viewProductModal" style="display: none;">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2>Product Details</h2>
                <span class="close" onclick="closeViewModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="view-category-info">
                    <div class="info-row">
                        <span class="info-label">Product ID:</span>
                        <span class="info-value" id="view_product_id">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Product Title:</span>
                        <span class="info-value" id="view_product_title">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Category:</span>
                        <span class="info-value" id="view_product_cat">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Vendor (Brand):</span>
                        <span class="info-value" id="view_product_vendor">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Price:</span>
                        <span class="info-value" id="view_product_price">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Stock:</span>
                        <span class="info-value" id="view_product_stock">0</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Keywords:</span>
                        <span class="info-value" id="view_product_keywords">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Description:</span>
                        <span class="info-value" id="view_product_desc" style="text-align: left; white-space: pre-wrap;">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Product Image:</span>
                        <span class="info-value">
                            <img id="view_product_image" class="image-preview" alt="Product image" style="max-width: 300px;">
                        </span>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-admin-primary" onclick="closeViewModal()">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/admin.js"></script>
    <script src="../js/product.js"></script>
    <script>
        // Modal functions
        function openAddModal() {
            document.getElementById('addProductModal').style.display = 'block';
            document.getElementById('addProductForm').reset();
            const preview = document.getElementById('add_image_preview');
            if (preview) preview.style.display = 'none';
        }

        // Close modal when clicking outside (only on the modal backdrop, not inside modal-content)
        window.addEventListener('click', function(event) {
            const addModal = document.getElementById('addProductModal');
            const updateModal = document.getElementById('updateProductModal');
            const viewModal = document.getElementById('viewProductModal');
            
            // Only close if clicking directly on the modal backdrop, not on modal-content or its children
            if (event.target == addModal) {
                addModal.style.display = 'none';
            }
            if (event.target == updateModal) {
                updateModal.style.display = 'none';
            }
            if (event.target == viewModal) {
                viewModal.style.display = 'none';
            }
        });

        // Prevent modal from closing when clicking inside modal-content
        document.addEventListener('DOMContentLoaded', function() {
            const modals = ['addProductModal', 'updateProductModal', 'viewProductModal'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (modal) {
                    const modalContent = modal.querySelector('.modal-content');
                    if (modalContent) {
                        modalContent.addEventListener('click', function(e) {
                            e.stopPropagation(); // Prevent click from bubbling to modal backdrop
                        });
                    }
                }
            });
        });
    </script>
</body>
</html>

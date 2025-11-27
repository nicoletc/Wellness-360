<?php
/**
 * Articles Management Page
 * Admin page for managing articles (CRUD operations)
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
    <title>Articles - Wellness 360 Admin</title>
    <link rel="stylesheet" href="../Css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .pdf-preview {
            margin-top: 10px;
            padding: 10px;
            background: #F5F1EB;
            border-radius: 4px;
            font-size: 0.875rem;
            color: #6B7E75;
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
                <a href="products.php" class="nav-item">
                    <i class="fas fa-box"></i>
                    <span>Products</span>
                </a>
                <a href="articles.php" class="nav-item active">
                    <i class="fas fa-file-alt"></i>
                    <span>Articles</span>
                </a>
                <a href="workshops.php" class="nav-item">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>Workshops</span>
                </a>
                <a href="messages.php?status=new" class="nav-item">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                    <?php 
                    $newCount = get_new_message_count();
                    if ($newCount > 0): ?>
                        <span class="badge"><?php echo $newCount; ?></span>
                    <?php endif; ?>
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
                        <h1 class="admin-title">Articles Management</h1>
                        <p class="admin-subtitle">Manage wellness hub articles and content</p>
                    </div>
                    <div class="admin-actions" style="flex-direction: column; align-items: flex-end; gap: 0.75rem;">
                        <button class="btn-admin-primary" onclick="openAddModal()" style="min-width: 200px;">
                            <i class="fas fa-plus"></i>
                            Create New Article
                        </button>
                        <a href="../View/wellness_hub.php" class="btn-admin-primary" target="_blank" style="min-width: 200px;">
                            <i class="fas fa-store"></i>
                            View Storefront
                        </a>
                    </div>
                </div>

                <div class="admin-table-container">
                    <div class="table-filters">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="articleSearch" placeholder="Search articles...">
                        </div>
                        <select class="filter-select" id="articleFilter">
                            <option value="all">All Articles</option>
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>

                    <table class="admin-table" id="articlesTable">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>Views</th>
                                <th>Published</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" style="text-align: center;">Loading articles...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Article Modal -->
    <div class="modal" id="addArticleModal" style="display: none;">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h2>Create New Article</h2>
                <span class="close" onclick="closeAddModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="addArticleForm">
                    <div class="form-group">
                        <label for="add_article_title">Article Title <span class="required">*</span></label>
                        <input type="text" id="add_article_title" name="article_title" placeholder="Enter article title" required maxlength="200">
                        <small class="form-help">Article title must be between 3-200 characters.</small>
                    </div>
                    <div class="form-group">
                        <label for="add_article_author">Author <span class="required">*</span></label>
                        <input type="text" id="add_article_author" name="article_author" placeholder="Enter author name" required maxlength="100">
                        <small class="form-help">Author name must be between 2-100 characters.</small>
                    </div>
                    <div class="form-group">
                        <label for="add_article_cat">Category <span class="required">*</span></label>
                        <select id="add_article_cat" name="article_cat" required>
                            <option value="">Select Category</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="add_article_image">Article Image</label>
                        <input type="file" id="add_article_image" name="article_image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                        <small class="form-help">Upload article image (JPEG, PNG, GIF, or WebP, max 5MB).</small>
                        <div id="add_image_preview" style="margin-top: 10px; display: none;">
                            <img id="add_image_preview_img" style="max-width: 200px; max-height: 200px; border-radius: 4px;" alt="Preview">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="article_pdf">Article PDF <span class="required">*</span></label>
                        <input type="file" id="article_pdf" name="article_pdf" accept="application/pdf" required>
                        <small class="form-help">Upload article PDF document (max 10MB).</small>
                        <div id="add_pdf_preview" class="pdf-preview"></div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary" onclick="closeAddModal()">Cancel</button>
                        <button type="submit" class="btn-admin-primary">
                            <span class="btn-text">Create Article</span>
                            <span class="btn-loader" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i> Creating...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Article Modal -->
    <div class="modal" id="updateArticleModal" style="display: none;">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h2>Update Article</h2>
                <span class="close" onclick="closeUpdateModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="updateArticleForm">
                    <input type="hidden" name="article_id" id="update_article_id">
                    <input type="hidden" name="current_pdf_path" id="current_pdf_path">
                    <div class="form-group">
                        <label for="update_article_title">Article Title <span class="required">*</span></label>
                        <input type="text" id="update_article_title" name="article_title" placeholder="Enter article title" required maxlength="200">
                        <small class="form-help">Article title must be between 3-200 characters.</small>
                    </div>
                    <div class="form-group">
                        <label for="update_article_author">Author <span class="required">*</span></label>
                        <input type="text" id="update_article_author" name="article_author" placeholder="Enter author name" required maxlength="100">
                        <small class="form-help">Author name must be between 2-100 characters.</small>
                    </div>
                    <div class="form-group">
                        <label for="update_article_cat">Category <span class="required">*</span></label>
                        <select id="update_article_cat" name="article_cat" required>
                            <option value="">Select Category</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="update_article_image">Article Image</label>
                        <input type="file" id="update_article_image" name="article_image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                        <small class="form-help">Upload new image to replace current one (JPEG, PNG, GIF, or WebP, max 5MB). Leave empty to keep current image.</small>
                        <img id="update_image_preview" class="image-preview" alt="Image preview" style="max-width: 200px; max-height: 200px; border-radius: 4px; margin-top: 10px; display: none;">
                    </div>
                    <div class="form-group">
                        <label for="update_article_pdf">Article PDF</label>
                        <input type="file" id="update_article_pdf" name="article_pdf" accept="application/pdf">
                        <small class="form-help">Upload new PDF to replace current one (max 10MB). Leave empty to keep current PDF.</small>
                        <div id="update_pdf_preview" class="pdf-preview"></div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary" onclick="closeUpdateModal()">Cancel</button>
                        <button type="submit" class="btn-admin-primary">
                            <span class="btn-text">Update Article</span>
                            <span class="btn-loader" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i> Updating...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Article Modal -->
    <div class="modal" id="viewArticleModal" style="display: none;">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2>Article Details</h2>
                <span class="close" onclick="closeViewModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="view-category-info">
                    <div class="info-row">
                        <span class="info-label">Article ID:</span>
                        <span class="info-value" id="view_article_id">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Title:</span>
                        <span class="info-value" id="view_article_title">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Author:</span>
                        <span class="info-value" id="view_article_author">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Category:</span>
                        <span class="info-value" id="view_article_cat">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Views:</span>
                        <span class="info-value" id="view_article_views">0</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Date Added:</span>
                        <span class="info-value" id="view_date_added">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">PDF Document:</span>
                        <span class="info-value">
                            <a id="view_article_pdf_link" href="#" target="_blank" class="btn-admin-primary" style="display: none;">
                                <i class="fas fa-file-pdf"></i> View PDF
                            </a>
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
    <script src="../js/article.js"></script>
    <script>
        // Modal functions
        function openAddModal() {
            document.getElementById('addArticleModal').style.display = 'block';
            document.getElementById('addArticleForm').reset();
            const preview = document.getElementById('add_pdf_preview');
            if (preview) preview.style.display = 'none';
        }

        // Close modal when clicking outside (only on the modal backdrop, not inside modal-content)
        window.addEventListener('click', function(event) {
            const addModal = document.getElementById('addArticleModal');
            const updateModal = document.getElementById('updateArticleModal');
            const viewModal = document.getElementById('viewArticleModal');
            
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
            const modals = ['addArticleModal', 'updateArticleModal', 'viewArticleModal'];
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

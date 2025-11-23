<?php
/**
 * Workshops Management Page
 * Admin page for managing workshops (CRUD operations)
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
    <title>Workshops - Wellness 360 Admin</title>
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
                <a href="workshops.php" class="nav-item active">
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
                        <h1 class="admin-title">Workshops Management</h1>
                        <p class="admin-subtitle">Manage community workshops and events</p>
                    </div>
                    <div class="admin-actions" style="flex-direction: column; align-items: flex-end; gap: 0.75rem;">
                        <button class="btn-admin-primary" onclick="openAddModal()" style="width: 200px; justify-content: center;">
                            <i class="fas fa-plus"></i>
                            Create New Workshop
                        </button>
                        <a href="../View/community.php?tab=workshops" class="btn-admin-primary" target="_blank" style="width: 200px; justify-content: center;">
                            <i class="fas fa-store"></i>
                            View Storefront
                        </a>
                    </div>
                </div>

                <div class="admin-table-container">
                    <div class="table-filters">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="workshopSearch" placeholder="Search workshops...">
                        </div>
                        <select class="filter-select" id="workshopFilter">
                            <option value="all">All Workshops</option>
                            <option value="in-person">In-Person</option>
                            <option value="virtual">Virtual</option>
                            <option value="upcoming">Upcoming</option>
                            <option value="past">Past</option>
                        </select>
                    </div>

                    <table class="admin-table" id="workshopsTable">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Leader</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Type</th>
                                <th>Location</th>
                                <th>Max Participants</th>
                                <th>Registered</th>
                                <th>View Attendees</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="10" style="text-align: center;">Loading workshops...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Workshop Modal -->
    <div class="modal" id="addWorkshopModal" style="display: none;">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h2>Create New Workshop</h2>
                <span class="close" onclick="closeAddModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="addWorkshopForm">
                    <div class="form-group">
                        <label for="add_workshop_title">Workshop Title <span class="required">*</span></label>
                        <input type="text" id="add_workshop_title" name="workshop_title" placeholder="Enter workshop title" required maxlength="200">
                        <small class="form-help">Workshop title must be between 3-200 characters.</small>
                    </div>
                    <div class="form-group">
                        <label for="add_workshop_desc">Description</label>
                        <textarea id="add_workshop_desc" name="workshop_desc" placeholder="Enter workshop description" rows="4"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="add_workshop_image">Workshop Image</label>
                        <input type="file" id="add_workshop_image" name="workshop_image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                        <small class="form-help">Upload workshop image (JPEG, PNG, GIF, or WebP, max 5MB).</small>
                        <div id="add_image_preview" style="margin-top: 10px; display: none;">
                            <img id="add_image_preview_img" style="max-width: 200px; max-height: 200px; border-radius: 4px;" alt="Preview">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="add_workshop_leader">Workshop Leader <span class="required">*</span></label>
                        <input type="text" id="add_workshop_leader" name="workshop_leader" placeholder="Enter leader name" required>
                    </div>
                    <div class="form-group">
                        <label for="add_workshop_date">Date <span class="required">*</span></label>
                        <input type="date" id="add_workshop_date" name="workshop_date" required>
                    </div>
                    <div class="form-group">
                        <label for="add_workshop_time">Time <span class="required">*</span></label>
                        <input type="time" id="add_workshop_time" name="workshop_time" required>
                    </div>
                    <div class="form-group">
                        <label for="add_workshop_type">Type <span class="required">*</span></label>
                        <select id="add_workshop_type" name="workshop_type" required onchange="toggleLocationField(this.value, 'add')">
                            <option value="">Select Type</option>
                            <option value="in-person">In-Person</option>
                            <option value="virtual">Virtual</option>
                        </select>
                    </div>
                    <div class="form-group" id="add_location_group">
                        <label for="add_location">Location <span class="required" id="add_location_required">*</span></label>
                        <input type="text" id="add_location" name="location" placeholder="Enter location">
                        <small class="form-help">Required for in-person workshops.</small>
                    </div>
                    <div class="form-group">
                        <label for="add_max_participants">Max Participants <span class="required">*</span></label>
                        <input type="number" id="add_max_participants" name="max_participants" placeholder="Enter maximum participants" required min="1">
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary" onclick="closeAddModal()">Cancel</button>
                        <button type="submit" class="btn-admin-primary">
                            <span class="btn-text">Create Workshop</span>
                            <span class="btn-loader" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i> Creating...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Workshop Modal -->
    <div class="modal" id="updateWorkshopModal" style="display: none;">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h2>Update Workshop</h2>
                <span class="close" onclick="closeUpdateModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="updateWorkshopForm">
                    <input type="hidden" name="workshop_id" id="update_workshop_id">
                    <div class="form-group">
                        <label for="update_workshop_title">Workshop Title <span class="required">*</span></label>
                        <input type="text" id="update_workshop_title" name="workshop_title" placeholder="Enter workshop title" required maxlength="200">
                        <small class="form-help">Workshop title must be between 3-200 characters.</small>
                    </div>
                    <div class="form-group">
                        <label for="update_workshop_desc">Description</label>
                        <textarea id="update_workshop_desc" name="workshop_desc" placeholder="Enter workshop description" rows="4"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="update_workshop_image">Workshop Image</label>
                        <input type="file" id="update_workshop_image" name="workshop_image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                        <small class="form-help">Upload new image to replace current one (JPEG, PNG, GIF, or WebP, max 5MB).</small>
                        <div id="update_image_preview" style="margin-top: 10px; display: none;">
                            <img id="update_image_preview_img" style="max-width: 200px; max-height: 200px; border-radius: 4px;" alt="Preview">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="update_workshop_leader">Workshop Leader <span class="required">*</span></label>
                        <input type="text" id="update_workshop_leader" name="workshop_leader" placeholder="Enter leader name" required>
                    </div>
                    <div class="form-group">
                        <label for="update_workshop_date">Date <span class="required">*</span></label>
                        <input type="date" id="update_workshop_date" name="workshop_date" required>
                    </div>
                    <div class="form-group">
                        <label for="update_workshop_time">Time <span class="required">*</span></label>
                        <input type="time" id="update_workshop_time" name="workshop_time" required>
                    </div>
                    <div class="form-group">
                        <label for="update_workshop_type">Type <span class="required">*</span></label>
                        <select id="update_workshop_type" name="workshop_type" required onchange="toggleLocationField(this.value, 'update')">
                            <option value="">Select Type</option>
                            <option value="in-person">In-Person</option>
                            <option value="virtual">Virtual</option>
                        </select>
                    </div>
                    <div class="form-group" id="update_location_group">
                        <label for="update_location">Location <span class="required" id="update_location_required">*</span></label>
                        <input type="text" id="update_location" name="location" placeholder="Enter location">
                        <small class="form-help">Required for in-person workshops.</small>
                    </div>
                    <div class="form-group">
                        <label for="update_max_participants">Max Participants <span class="required">*</span></label>
                        <input type="number" id="update_max_participants" name="max_participants" placeholder="Enter maximum participants" required min="1">
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary" onclick="closeUpdateModal()">Cancel</button>
                        <button type="submit" class="btn-admin-primary">
                            <span class="btn-text">Update Workshop</span>
                            <span class="btn-loader" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i> Updating...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Attendees Modal -->
    <div class="modal" id="viewAttendeesModal" style="display: none;">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h2>Workshop Attendees</h2>
                <span class="close" onclick="closeAttendeesModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="attendeesWorkshopTitle" style="margin-bottom: 1.5rem; font-size: 1.125rem; font-weight: 600; color: var(--foreground);"></div>
                <div class="admin-table-container" style="max-height: 500px; overflow-y: auto;">
                    <table class="admin-table" id="attendeesTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer Name</th>
                                <th>Email</th>
                                <th>Registered At</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" style="text-align: center;">Loading attendees...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="form-actions" style="margin-top: 1.5rem;">
                    <button type="button" class="btn-admin-primary" onclick="closeAttendeesModal()">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Workshop Modal -->
    <div class="modal" id="viewWorkshopModal" style="display: none;">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2>Workshop Details</h2>
                <span class="close" onclick="closeViewModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="view-category-info">
                    <div class="info-row">
                        <span class="info-label">Workshop ID:</span>
                        <span class="info-value" id="view_workshop_id">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Title:</span>
                        <span class="info-value" id="view_workshop_title">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Description:</span>
                        <span class="info-value" id="view_workshop_desc">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Leader:</span>
                        <span class="info-value" id="view_workshop_leader">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Date:</span>
                        <span class="info-value" id="view_workshop_date">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Time:</span>
                        <span class="info-value" id="view_workshop_time">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Type:</span>
                        <span class="info-value" id="view_workshop_type">-</span>
                    </div>
                    <div class="info-row" id="view_location_row">
                        <span class="info-label">Location:</span>
                        <span class="info-value" id="view_location">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Max Participants:</span>
                        <span class="info-value" id="view_max_participants">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Created By:</span>
                        <span class="info-value" id="view_customer_name">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Image:</span>
                        <span class="info-value">
                            <img id="view_workshop_image" class="image-preview" alt="Workshop image" style="max-width: 300px; display: none; border-radius: 4px; margin-top: 10px;">
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
    <script src="../js/workshop.js"></script>
    <script>
        // Modal functions
        function openAddModal() {
            document.getElementById('addWorkshopModal').style.display = 'block';
            document.getElementById('addWorkshopForm').reset();
            document.getElementById('add_location_group').style.display = 'none';
            const preview = document.getElementById('add_image_preview');
            if (preview) preview.style.display = 'none';
        }

        function closeAddModal() {
            document.getElementById('addWorkshopModal').style.display = 'none';
        }

        function closeUpdateModal() {
            document.getElementById('updateWorkshopModal').style.display = 'none';
        }

        function closeViewModal() {
            document.getElementById('viewWorkshopModal').style.display = 'none';
        }

        function closeAttendeesModal() {
            document.getElementById('viewAttendeesModal').style.display = 'none';
        }

        function toggleLocationField(type, formType) {
            const locationGroup = document.getElementById(formType + '_location_group');
            const locationInput = document.getElementById(formType + '_location');
            const locationRequired = document.getElementById(formType + '_location_required');
            
            if (type === 'in-person') {
                locationGroup.style.display = 'block';
                locationInput.required = true;
                if (locationRequired) locationRequired.style.display = 'inline';
            } else {
                locationGroup.style.display = 'none';
                locationInput.required = false;
                locationInput.value = '';
                if (locationRequired) locationRequired.style.display = 'none';
            }
        }

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            const addModal = document.getElementById('addWorkshopModal');
            const updateModal = document.getElementById('updateWorkshopModal');
            const viewModal = document.getElementById('viewWorkshopModal');
            const attendeesModal = document.getElementById('viewAttendeesModal');
            
            if (event.target == addModal) {
                addModal.style.display = 'none';
            }
            if (event.target == updateModal) {
                updateModal.style.display = 'none';
            }
            if (event.target == viewModal) {
                viewModal.style.display = 'none';
            }
            if (event.target == attendeesModal) {
                attendeesModal.style.display = 'none';
            }
        });

        // Prevent modal from closing when clicking inside modal-content
        document.addEventListener('DOMContentLoaded', function() {
            const modals = ['addWorkshopModal', 'updateWorkshopModal', 'viewWorkshopModal', 'viewAttendeesModal'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (modal) {
                    const modalContent = modal.querySelector('.modal-content');
                    if (modalContent) {
                        modalContent.addEventListener('click', function(e) {
                            e.stopPropagation();
                        });
                    }
                }
            });
        });
    </script>
</body>
</html>


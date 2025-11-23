<?php
/**
 * Users Management Page
 * Admin page for managing users (View and Delete only)
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
    <title>Users - Wellness 360 Admin</title>
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
                <a href="users.php" class="nav-item active">
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
                        <h1 class="admin-title">Users Management</h1>
                        <p class="admin-subtitle">View and manage all platform users</p>
                    </div>
                </div>

                <div class="admin-table-container">
                    <div class="table-filters">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="userSearch" placeholder="Search users...">
                        </div>
                        <select class="filter-select" id="userFilter">
                            <option>All Users</option>
                            <option>Admins</option>
                            <option>Customers</option>
                        </select>
                    </div>

                    <table class="admin-table" id="usersTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Contact</th>
                                <th>Date Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" style="text-align: center;">Loading users...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- View User Modal -->
    <div class="modal" id="viewUserModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>User Details</h2>
                <span class="close" onclick="closeViewModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="view-category-info">
                    <div class="info-row">
                        <span class="info-label">User ID:</span>
                        <span class="info-value" id="view_user_id">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Name:</span>
                        <span class="info-value" id="view_user_name">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value" id="view_user_email">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Contact:</span>
                        <span class="info-value" id="view_user_contact">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Role:</span>
                        <span class="info-value" id="view_user_role">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Date Joined:</span>
                        <span class="info-value" id="view_date_joined">-</span>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-admin-primary" onclick="closeViewModal()">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/admin.js"></script>
    <script src="../js/user.js"></script>
    <script>
        // Close modal when clicking outside
        window.onclick = function(event) {
            const viewModal = document.getElementById('viewUserModal');
            if (event.target == viewModal) {
                viewModal.style.display = 'none';
            }
        }
    </script>
</body>
</html>

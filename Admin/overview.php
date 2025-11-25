<?php
/**
 * Admin Overview Page
 * Displays dashboard statistics and recent activity
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Classes/AdminModel.php';

// Check if user is admin
require_login();
if (!is_admin()) {
    redirect(PATH_HOME);
}

// Get dashboard data
$adminModel = new AdminModel();
$stats = $adminModel->getDashboardStats();
$recentActivity = $adminModel->getRecentActivity(4);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overview - Wellness 360 Admin</title>
    <link rel="stylesheet" href="../Css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
                <a href="overview.php" class="nav-item active">
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
            <h1 class="admin-title">Admin Dashboard</h1>
            <p class="admin-subtitle">Manage your Wellness 360 platform</p>
        </div>
        <div class="admin-badge">
            <i class="fas fa-users"></i>
            <span>Admin Access</span>
        </div>
    </div>

    <div class="dashboard-stats">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-value"><?php echo number_format($stats['totalUsers'] ?? 0); ?></h3>
                <p class="stat-label">Total Users</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-value"><?php echo number_format($stats['totalProducts'] ?? 0); ?></h3>
                <p class="stat-label">Total Products</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-store"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-value"><?php echo number_format($stats['activeVendors'] ?? 0); ?></h3>
                <p class="stat-label">Active Vendors</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-value"><?php echo number_format($stats['publishedArticles'] ?? 0); ?></h3>
                <p class="stat-label">Published Articles</p>
            </div>
        </div>
    </div>

    <div class="dashboard-widgets">
        <div class="widget">
            <div class="widget-header">
                <h3>Recent Activity</h3>
            </div>
            <div class="widget-content">
                <?php if (empty($recentActivity)): ?>
                <div class="activity-item">
                    <div class="activity-info">
                            <p class="activity-title">No recent activity</p>
                </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($recentActivity as $activity): ?>
                <div class="activity-item">
                    <div class="activity-icon">
                                <i class="fas fa-<?php echo htmlspecialchars($activity['icon']); ?>"></i>
                    </div>
                    <div class="activity-info">
                                <p class="activity-title"><?php echo htmlspecialchars($activity['title']); ?></p>
                                <p class="activity-time"><?php echo htmlspecialchars($activity['time']); ?></p>
                    </div>
                </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="widget">
            <div class="widget-header">
                <h3>Quick Actions</h3>
            </div>
            <div class="widget-content">
                <a href="users.php" class="quick-action-btn">
                    <i class="fas fa-users"></i>
                    <span>Manage Users</span>
                </a>
                <a href="products.php" class="quick-action-btn">
                    <i class="fas fa-box"></i>
                    <span>Manage Products</span>
                </a>
                <a href="vendors.php" class="quick-action-btn">
                    <i class="fas fa-store"></i>
                    <span>Manage Vendors</span>
                </a>
                <a href="articles.php" class="quick-action-btn">
                    <i class="fas fa-file-alt"></i>
                    <span>Manage Articles</span>
                </a>
            </div>
        </div>
    </div>
</div>
        </main>
    </div>
    <script src="../js/admin.js"></script>
</body>
</html>


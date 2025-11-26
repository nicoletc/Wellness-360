<?php
/**
 * Admin Contact Messages Page (View)
 * Displays and manages contact form messages
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/AdminMessagesController.php';

// Check if user is admin
require_login();
if (!is_admin()) {
    redirect(PATH_HOME);
}

$controller = new AdminMessagesController();

// Handle actions
$action = $_GET['action'] ?? '';
$message_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (in_array($action, ['update_status', 'delete']) && $message_id) {
    $result = $controller->handleAction($action, $message_id, $_POST);
    if ($result['status'] && isset($result['redirect'])) {
        header('Location: ' . $result['redirect']);
        exit;
    }
}

// Get filter - default to 'new' if no status specified
$status_filter = $_GET['status'] ?? 'new';

// Get page data from controller
$data = $controller->index($status_filter, $action, $message_id);

// Extract data
$messages = $data['messages'];
$counts = $data['counts'];
$status_filter = $data['status_filter'];
$selectedMessage = $data['selectedMessage'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages - Wellness 360 Admin</title>
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
                <a href="workshops.php" class="nav-item">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>Workshops</span>
                </a>
                <a href="messages.php?status=new" class="nav-item active">
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
                        <h1 class="admin-title">Contact Messages</h1>
                        <p class="admin-subtitle">Manage customer inquiries and support requests</p>
                    </div>
                    <div class="admin-badge">
                        <i class="fas fa-envelope"></i>
                        <span>Total: <?php echo $counts['total']; ?></span>
                    </div>
                </div>

                <!-- Filter Tabs -->
                <div class="filter-tabs">
                    <a href="messages.php?status=all" class="filter-tab <?php echo $status_filter === 'all' ? 'active' : ''; ?>">
                        All (<?php echo $counts['total']; ?>)
                    </a>
                    <a href="messages.php?status=new" class="filter-tab <?php echo $status_filter === 'new' ? 'active' : ''; ?>">
                        New (<?php echo $counts['new']; ?>)
                    </a>
                    <a href="messages.php?status=read" class="filter-tab <?php echo $status_filter === 'read' ? 'active' : ''; ?>">
                        Read (<?php echo $counts['read']; ?>)
                    </a>
                    <a href="messages.php?status=replied" class="filter-tab <?php echo $status_filter === 'replied' ? 'active' : ''; ?>">
                        Replied (<?php echo $counts['replied']; ?>)
                    </a>
                    <a href="messages.php?status=archived" class="filter-tab <?php echo $status_filter === 'archived' ? 'active' : ''; ?>">
                        Archived (<?php echo $counts['archived']; ?>)
                    </a>
                </div>

                <div class="messages-container">
                    <!-- Messages List -->
                    <div class="messages-list">
                        <?php if (empty($messages)): ?>
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <p>No messages found</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($messages as $msg): ?>
                                <div class="message-item <?php echo $msg['status']; ?> <?php echo $selectedMessage && $selectedMessage['message_id'] == $msg['message_id'] ? 'active' : ''; ?>" 
                                     onclick="window.location.href='messages.php?action=view&id=<?php echo $msg['message_id']; ?>&status=<?php echo $status_filter; ?>'">
                                    <div class="message-header">
                                        <div>
                                            <div class="message-subject"><?php echo htmlspecialchars($msg['subject']); ?></div>
                                            <div class="message-meta">
                                                <?php echo htmlspecialchars($msg['first_name'] . ' ' . $msg['last_name']); ?>
                                                <span style="margin: 0 0.5rem;">•</span>
                                                <?php echo date('M j, Y g:i A', strtotime($msg['created_at'])); ?>
                                            </div>
                                        </div>
                                        <span class="status-badge <?php echo $msg['status']; ?>"><?php echo ucfirst($msg['status']); ?></span>
                                    </div>
                                    <div class="message-preview"><?php echo htmlspecialchars(substr($msg['message'], 0, 100)); ?>...</div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Message Detail -->
                    <div class="message-detail">
                        <?php if ($selectedMessage): ?>
                            <div class="message-detail-header">
                                <div class="message-detail-title"><?php echo htmlspecialchars($selectedMessage['subject']); ?></div>
                                <div class="message-detail-meta">
                                    <span class="status-badge <?php echo $selectedMessage['status']; ?>"><?php echo strtoupper($selectedMessage['status']); ?></span>
                                    <span class="message-detail-date">
                                        <?php echo date('F j, Y \a\t g:i A', strtotime($selectedMessage['created_at'])); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="message-detail-info">
                                <div class="info-item">
                                    <span class="info-label">Name</span>
                                    <span class="info-value"><?php echo htmlspecialchars($selectedMessage['first_name'] . ' ' . $selectedMessage['last_name']); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Email</span>
                                    <span class="info-value">
                                        <a href="mailto:<?php echo htmlspecialchars($selectedMessage['email']); ?>">
                                            <?php echo htmlspecialchars($selectedMessage['email']); ?>
                                        </a>
                                    </span>
                                </div>
                                <?php if (!empty($selectedMessage['phone'])): ?>
                                <div class="info-item">
                                    <span class="info-label">Phone</span>
                                    <span class="info-value">
                                        <a href="tel:<?php echo htmlspecialchars($selectedMessage['phone']); ?>">
                                            <?php echo htmlspecialchars($selectedMessage['phone']); ?>
                                        </a>
                                    </span>
                                </div>
                                <?php else: ?>
                                <div class="info-item"></div>
                                <?php endif; ?>
                                <?php if ($selectedMessage['customer_id']): ?>
                                <div class="info-item">
                                    <span class="info-label">Customer</span>
                                    <span class="info-value">
                                        <a href="users.php?action=view&id=<?php echo $selectedMessage['customer_id']; ?>">
                                            <?php echo htmlspecialchars($selectedMessage['customer_name'] ?? 'Customer #' . $selectedMessage['customer_id']); ?>
                                        </a>
                                    </span>
                                </div>
                                <?php else: ?>
                                <div class="info-item"></div>
                                <?php endif; ?>
                            </div>

                            <div class="message-content"><?php echo nl2br(htmlspecialchars($selectedMessage['message'])); ?></div>

                            <div class="message-actions">
                                <form method="POST" action="messages.php?action=update_status&id=<?php echo $selectedMessage['message_id']; ?>&status=<?php echo $status_filter; ?>" style="display: inline;">
                                    <select name="status" onchange="this.form.submit()" class="form-control" style="display: inline-block; width: auto; margin-right: 1rem;">
                                        <option value="new" <?php echo $selectedMessage['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                                        <option value="read" <?php echo $selectedMessage['status'] === 'read' ? 'selected' : ''; ?>>Read</option>
                                        <option value="replied" <?php echo $selectedMessage['status'] === 'replied' ? 'selected' : ''; ?>>Replied</option>
                                        <option value="archived" <?php echo $selectedMessage['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                                    </select>
                                </form>
                                <a href="mailto:<?php echo htmlspecialchars($selectedMessage['email']); ?>?subject=Re: <?php echo urlencode($selectedMessage['subject']); ?>" class="btn btn-primary">
                                    <i class="fas fa-reply"></i> Reply
                                </a>
                                <a href="messages.php?action=delete&id=<?php echo $selectedMessage['message_id']; ?>&status=<?php echo $status_filter; ?>" 
                                   class="btn btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this message?');">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-envelope-open"></i>
                                <p>Select a message to view details</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
    // Show success/error messages
    <?php if (isset($_GET['updated'])): ?>
        Swal.fire({
            icon: 'success',
            title: 'Updated',
            text: 'Message status updated successfully.',
            timer: 2000,
            showConfirmButton: false
        });
    <?php endif; ?>
    
    <?php if (isset($_GET['deleted'])): ?>
        Swal.fire({
            icon: 'success',
            title: 'Deleted',
            text: 'Message deleted successfully.',
            timer: 2000,
            showConfirmButton: false
        });
    <?php endif; ?>
    </script>
</body>
</html>


<?php
require_once 'includes/auth.php';
require_once '../config/database.php';

$adminName = $_SESSION['admin_username'] ?? 'Admin';
$error   = "";
$success = "";

// Approve using stored procedure
if (isset($_GET['approve']) && is_numeric($_GET['approve'])) {
    $id   = (int)$_GET['approve'];
    $stmt = $conn->prepare("CALL ApprovePendingPost(?)");
    $stmt->bind_param("i", $id);
    $stmt->execute() ? $success = "Post approved and published!" : $error = "Failed to approve post.";
    $stmt->close();
    $conn->next_result();
}

// Reject
if (isset($_GET['reject']) && is_numeric($_GET['reject'])) {
    $id   = (int)$_GET['reject'];
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ? AND status = 'pending'");
    $stmt->bind_param("i", $id);
    $stmt->execute() ? $success = "Post rejected and removed." : $error = "Failed to reject post.";
    $stmt->close();
}

$posts = $conn->query("
    SELECT posts.*, categories.name AS category_name, users.username AS author_name
    FROM posts
    LEFT JOIN categories ON posts.category_id = categories.id
    LEFT JOIN users ON posts.author_id = users.id
    WHERE posts.status = 'pending'
    ORDER BY posts.created_at DESC
");

$pendingCount = $posts ? $posts->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Posts | MiniPress CMS</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .alert-error { background:#fee2e2;color:#b91c1c;padding:12px 14px;border-radius:10px;margin-bottom:16px;font-weight:600; }
        .alert-success { background:#dcfce7;color:#15803d;padding:12px 14px;border-radius:10px;margin-bottom:16px;font-weight:600; }
        .approve-btn { display:inline-flex;align-items:center;justify-content:center;background:#dcfce7;color:#15803d;padding:8px 14px;border-radius:8px;font-size:13px;font-weight:700; }
        .reject-btn  { display:inline-flex;align-items:center;justify-content:center;background:#fee2e2;color:#b91c1c;padding:8px 14px;border-radius:8px;font-size:13px;font-weight:700; }
        .preview-btn { display:inline-flex;align-items:center;justify-content:center;background:#eef2ff;color:#4f46e5;padding:8px 14px;border-radius:8px;font-size:13px;font-weight:700; }
        .author-tag  { font-size:13px;color:#667085; }
        .empty-pending { text-align:center;padding:40px;color:#667085;font-size:15px; }
    </style>
</head>
<body>
<div class="admin-page" id="adminPage">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar-brand">MiniPress</div>
        <nav class="admin-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="posts.php">Posts</a>
            <a href="pending-posts.php" class="active">⏳ Pending Posts</a>
            <a href="categories.php">Categories</a>
            <a href="pages.php">Pages</a>
            <a href="media.php">Media</a>
            <a href="users.php">Users</a>
            <a href="settings.php">Settings</a>
        </nav>
    </aside>

    <main class="admin-main">
        <header class="admin-topbar">
            <div class="menu-icon" id="sidebarToggle" style="cursor:pointer;">☰</div>
            <div class="topbar-search-wrap"><input type="text" placeholder="Search..."></div>
            <div class="topbar-user">
                <div class="topbar-user-text">
                    <strong><?php echo htmlspecialchars($adminName); ?></strong>
                    <span>Administrator</span>
                </div>
                <div class="topbar-avatar">A</div>
            </div>
        </header>

        <section class="admin-content">
            <div class="page-heading page-heading-inline">
                <div>
                    <h1>Pending Posts</h1>
                    <p>Review and approve user submitted posts</p>
                </div>
                <span class="add-post-btn">⏳ <?php echo $pendingCount; ?> Pending</span>
            </div>

            <?php if ($error): ?><div class="alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

            <div class="content-card" style="margin-top:22px;">
                <?php if ($posts && $posts->num_rows > 0): ?>
                <table class="content-table">
                    <thead>
                        <tr>
                            <th>TITLE</th>
                            <th>SUBMITTED BY</th>
                            <th>CATEGORY</th>
                            <th>DATE</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $posts->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                            <td><span class="author-tag">👤 <?php echo htmlspecialchars($row['author_name'] ?? 'Unknown'); ?></span></td>
                            <td><span class="category-pill"><?php echo htmlspecialchars($row['category_name'] ?? 'Uncategorized'); ?></span></td>
                            <td><?php echo date("M d, Y", strtotime($row['created_at'])); ?></td>
                            <td class="action-cell">
                                <a href="preview-post.php?id=<?php echo $row['id']; ?>" class="preview-btn">👁 Preview</a>
                                <a href="pending-posts.php?approve=<?php echo $row['id']; ?>" class="approve-btn" onclick="return confirm('Approve and publish this post?');">✅ Approve</a>
                                <a href="pending-posts.php?reject=<?php echo $row['id']; ?>" class="reject-btn" onclick="return confirm('Reject and delete this post?');">❌ Reject</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div class="empty-pending">🎉 No pending posts right now. All caught up!</div>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>

<script>
const toggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('adminSidebar');
toggle.addEventListener('click', () => sidebar.classList.toggle('sidebar-collapsed'));
</script>
</body>
</html>
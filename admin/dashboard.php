<?php
require_once 'includes/auth.php';
require_once '../config/database.php';

$adminName = $_SESSION['admin_username'] ?? 'Admin';

$totalPosts = 0;
$publishedPosts = 0;
$draftPosts = 0;
$totalUsers = 0;

$q1 = $conn->query("SELECT COUNT(*) AS total FROM posts");
if ($q1 && $row = $q1->fetch_assoc()) {
    $totalPosts = (int)$row['total'];
}

$q2 = $conn->query("SELECT COUNT(*) AS total FROM posts WHERE status = 'published'");
if ($q2 && $row = $q2->fetch_assoc()) {
    $publishedPosts = (int)$row['total'];
}

$q3 = $conn->query("SELECT COUNT(*) AS total FROM posts WHERE status = 'draft'");
if ($q3 && $row = $q3->fetch_assoc()) {
    $draftPosts = (int)$row['total'];
}

$q4 = $conn->query("SELECT COUNT(*) AS total FROM users");
if ($q4 && $row = $q4->fetch_assoc()) {
    $totalUsers = (int)$row['total'];
}

$recentPosts = $conn->query("SELECT title, status, created_at FROM posts ORDER BY created_at DESC LIMIT 4");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | MiniPress CMS</title>
    <link rel="stylesheet" href="../assets/css/admin.css?v=103">
</head>
<body>
<div class="admin-page">
    <aside class="admin-sidebar">
        <div class="admin-sidebar-brand">MiniPress</div>

        <nav class="admin-nav">
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="posts.php">Posts</a>
            <a href="categories.php">Categories</a>
            <a href="pages.php">Pages</a>
            <a href="media.php">Media</a>
            <a href="users.php">Users</a>
            <a href="settings.php">Settings</a>
        </nav>
    </aside>

    <main class="admin-main">
        <header class="admin-topbar">
            <div class="menu-icon">☰</div>

            <div class="topbar-search-wrap">
                <input type="text" placeholder="Search...">
            </div>

            <div class="topbar-user">
                <div class="topbar-user-text">
                    <strong><?php echo htmlspecialchars($adminName); ?></strong>
                    <span>Administrator</span>
                </div>
                <div class="topbar-avatar">A</div>
            </div>
        </header>

        <section class="admin-content">
            <div class="page-heading">
                <h1>Dashboard</h1>
                <p>Welcome back! Here's what's happening with your site.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-title stat-blue">Total Posts</div>
                    <div class="stat-value"><?php echo $totalPosts; ?></div>
                    <div class="stat-sub">Live post count</div>
                </div>

                <div class="stat-card">
                    <div class="stat-title stat-green">Published</div>
                    <div class="stat-value"><?php echo $publishedPosts; ?></div>
                    <div class="stat-sub">Visible on site</div>
                </div>

                <div class="stat-card">
                    <div class="stat-title stat-orange">Drafts</div>
                    <div class="stat-value"><?php echo $draftPosts; ?></div>
                    <div class="stat-sub">Not published yet</div>
                </div>

                <div class="stat-card">
                    <div class="stat-title stat-purple">Total Users</div>
                    <div class="stat-value"><?php echo $totalUsers; ?></div>
                    <div class="stat-sub">Admin accounts</div>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="content-card">
                    <div class="content-card-header">
                        <h3>Recent Posts</h3>
                        <a href="posts.php">View All Posts →</a>
                    </div>

                    <table class="content-table">
                        <thead>
                            <tr>
                                <th>TITLE</th>
                                <th>STATUS</th>
                                <th>DATE</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recentPosts && $recentPosts->num_rows > 0): ?>
                                <?php while ($post = $recentPosts->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($post['title']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $post['status'] === 'published' ? 'badge-green' : 'badge-orange'; ?>">
                                                <?php echo ucfirst(htmlspecialchars($post['status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date("M d, Y", strtotime($post['created_at'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3">No posts found yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="content-card quick-actions-card">
                    <h3>Quick Actions</h3>
                    <a href="add-post.php" class="quick-btn quick-primary">+ New Post</a>
                    <a href="pages.php" class="quick-btn quick-light">+ New Page</a>
                    <a href="media.php" class="quick-btn quick-success">⇪ Upload Media</a>
                    <a href="../logout.php" class="quick-btn quick-danger">Logout</a>
                </div>
            </div>
        </section>
    </main>
</div>
</body>
</html>
<?php
require_once 'includes/auth.php';
require_once '../config/database.php';

$adminName = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin';

$totalPosts = 0;
$publishedPosts = 0;
$draftPosts = 0;
$totalPages = 0;

$postResult = $conn->query("SELECT COUNT(*) AS total FROM posts");
if ($postResult && $row = $postResult->fetch_assoc()) {
    $totalPosts = $row['total'];
}

$publishedResult = $conn->query("SELECT COUNT(*) AS total FROM posts WHERE status = 'published'");
if ($publishedResult && $row = $publishedResult->fetch_assoc()) {
    $publishedPosts = $row['total'];
}

$draftResult = $conn->query("SELECT COUNT(*) AS total FROM posts WHERE status = 'draft'");
if ($draftResult && $row = $draftResult->fetch_assoc()) {
    $draftPosts = $row['total'];
}

$pageResult = $conn->query("SELECT COUNT(*) AS total FROM pages");
if ($pageResult && $row = $pageResult->fetch_assoc()) {
    $totalPages = $row['total'];
}

$recentPosts = $conn->query("SELECT title, status, created_at FROM posts ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | MiniPress CMS</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<div class="admin-layout">
    <aside class="sidebar">
        <div class="sidebar-brand">MiniPress</div>

        <nav class="sidebar-menu">
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="posts.php">Posts</a>
            <a href="pages.php">Pages</a>
            <a href="categories.php">Categories</a>
            <a href="tags.php">Tags</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1>Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($adminName); ?>!</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card stat-blue">
                <h2><?php echo $totalPosts; ?></h2>
                <p>Total Posts</p>
            </div>

            <div class="stat-card stat-green">
                <h2><?php echo $publishedPosts; ?></h2>
                <p>Published</p>
            </div>

            <div class="stat-card stat-yellow">
                <h2><?php echo $draftPosts; ?></h2>
                <p>Drafts</p>
            </div>

            <div class="stat-card stat-purple">
                <h2><?php echo $totalPages; ?></h2>
                <p>Pages</p>
            </div>
        </div>

        <div class="table-card">
            <div class="table-card-header">
                <h3>Recent Posts</h3>
                <a href="posts.php" class="view-btn">View All Posts</a>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recentPosts && $recentPosts->num_rows > 0): ?>
                        <?php while ($post = $recentPosts->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($post['title']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $post['status'] === 'published' ? 'published' : 'draft'; ?>">
                                        <?php echo ucfirst($post['status']); ?>
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
    </main>
</div>

</body>
</html>
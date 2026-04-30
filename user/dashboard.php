<?php
require_once 'auth.php';
require_once '../config/database.php';

$username = $_SESSION['user_username'] ?? 'User';

$posts = $conn->query("
    SELECT posts.*, categories.name AS category_name
    FROM posts
    LEFT JOIN categories ON posts.category_id = categories.id
    WHERE posts.status = 'published'
    ORDER BY posts.is_pinned DESC, posts.published_at DESC
");

$categories = $conn->query("
    SELECT categories.*, COUNT(posts.id) AS post_count
    FROM categories
    LEFT JOIN posts ON posts.category_id = categories.id AND posts.status = 'published'
    GROUP BY categories.id ORDER BY post_count DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | MiniPress</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="admin-page" id="adminPage">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar-brand">MiniPress</div>
        <nav class="admin-nav">
            <a href="dashboard.php" class="active">📰 Posts</a>
            <a href="profile.php">👤 My Profile</a>
            <a href="../logout.php">🚪 Logout</a>
        </nav>
    </aside>

    <main class="admin-main">
        <header class="admin-topbar">
            <div class="menu-icon" id="sidebarToggle" style="cursor:pointer;">☰</div>
            <div class="topbar-search-wrap">
                <input type="text" id="searchInput" placeholder="Search posts...">
            </div>
            <div class="topbar-user">
                <div class="topbar-user-text">
                    <strong><?php echo htmlspecialchars($username); ?></strong>
                    <span>User</span>
                </div>
                <div class="topbar-avatar"><?php echo strtoupper($username[0]); ?></div>
            </div>
        </header>

        <section class="admin-content">
            <div class="page-heading">
                <h1>Latest Posts</h1>
                <p>Browse and read published articles</p>
            </div>

            <div class="content-card" style="margin-top: 22px;">
                <table class="content-table" id="postsTable">
                    <thead>
                        <tr>
                            <th>TITLE</th>
                            <th>CATEGORY</th>
                            <th>DATE</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($posts && $posts->num_rows > 0): ?>
                            <?php while ($post = $posts->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if ($post['is_pinned']): ?>
                                        <span style="color:#d97706; font-size:12px;">📌 </span>
                                    <?php endif; ?>
                                    <strong><?php echo htmlspecialchars($post['title']); ?></strong>
                                </td>
                                <td>
                                    <span class="category-pill">
                                        <?php echo htmlspecialchars($post['category_name'] ?? 'Uncategorized'); ?>
                                    </span>
                                </td>
                                <td><?php echo date("M d, Y", strtotime($post['published_at'] ?? $post['created_at'])); ?></td>
                                <td>
                                    <a href="post.php?id=<?php echo $post['id']; ?>" class="add-post-btn" style="font-size:13px; padding: 8px 16px;">Read →</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4">No posts available yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

<script>
const toggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('adminSidebar');
toggle.addEventListener('click', () => sidebar.classList.toggle('sidebar-collapsed'));

document.getElementById('searchInput').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#postsTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
</body>
</html>
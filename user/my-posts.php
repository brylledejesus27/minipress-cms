<?php
require_once 'auth.php';
require_once '../config/database.php';

$userId   = $_SESSION['user_id'];
$username = $_SESSION['user_username'] ?? 'User';

$posts = $conn->query("
    SELECT posts.*, categories.name AS category_name
    FROM posts
    LEFT JOIN categories ON posts.category_id = categories.id
    WHERE posts.author_id = $userId
    ORDER BY posts.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Posts | MiniPress</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .badge-pending { background: #fffbeb; color: #92400e; }
        .badge-published { background: #dcfce7; color: #15803d; }
        .badge-draft { background: #ffedd5; color: #c2410c; }
    </style>
</head>
<body>
<div class="admin-page" id="adminPage">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar-brand">MiniPress</div>
        <nav class="admin-nav">
            <a href="dashboard.php">📰 Posts</a>
            <a href="create-post.php">✏️ Write Post</a>
            <a href="my-posts.php" class="active">📋 My Posts</a>
            <a href="profile.php">👤 My Profile</a>
            <a href="../logout.php">🚪 Logout</a>
        </nav>
    </aside>

    <main class="admin-main">
        <header class="admin-topbar">
            <div class="menu-icon" id="sidebarToggle" style="cursor:pointer;">☰</div>
            <div class="topbar-search-wrap"><input type="text" placeholder="Search..."></div>
            <div class="topbar-user">
                <div class="topbar-user-text">
                    <strong><?php echo htmlspecialchars($username); ?></strong>
                    <span>User</span>
                </div>
                <div class="topbar-avatar"><?php echo strtoupper($username[0]); ?></div>
            </div>
        </header>

        <section class="admin-content">
            <div class="page-heading page-heading-inline">
                <div>
                    <h1>My Posts</h1>
                    <p>Track the status of your submitted posts</p>
                </div>
                <a href="create-post.php" class="add-post-btn">✏️ Write New Post</a>
            </div>

            <div class="content-card" style="margin-top: 22px;">
                <table class="content-table">
                    <thead>
                        <tr>
                            <th>TITLE</th>
                            <th>CATEGORY</th>
                            <th>STATUS</th>
                            <th>DATE SUBMITTED</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($posts && $posts->num_rows > 0): ?>
                            <?php while ($row = $posts->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                                <td>
                                    <span class="category-pill">
                                        <?php echo htmlspecialchars($row['category_name'] ?? 'Uncategorized'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $row['status']; ?>">
                                        <?php
                                            $icons = ['pending' => '⏳', 'published' => '✅', 'draft' => '📝'];
                                            echo ($icons[$row['status']] ?? '') . ' ' . ucfirst($row['status']);
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo date("M d, Y", strtotime($row['created_at'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="text-align:center; color:#667085;">You haven't submitted any posts yet. <a href="create-post.php" style="color:#5a4efc; font-weight:700;">Write one now!</a></td></tr>
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
</script>
</body>
</html>
<?php
require_once 'includes/auth.php';
require_once '../config/database.php';

$adminName = $_SESSION['admin_username'] ?? 'Admin';

$filter = $_GET['filter'] ?? 'all';

$allCount     = 0;
$publishedCount = 0;
$draftCount   = 0;
$pendingCount = 0;

$c1 = $conn->query("SELECT COUNT(*) AS total FROM posts");
if ($c1 && $row = $c1->fetch_assoc()) $allCount = (int)$row['total'];

$c2 = $conn->query("SELECT COUNT(*) AS total FROM posts WHERE status = 'published'");
if ($c2 && $row = $c2->fetch_assoc()) $publishedCount = (int)$row['total'];

$c3 = $conn->query("SELECT COUNT(*) AS total FROM posts WHERE status = 'draft'");
if ($c3 && $row = $c3->fetch_assoc()) $draftCount = (int)$row['total'];

$c4 = $conn->query("SELECT COUNT(*) AS total FROM posts WHERE status = 'pending'");
if ($c4 && $row = $c4->fetch_assoc()) $pendingCount = (int)$row['total'];

$whereClause = '';
if ($filter === 'published') $whereClause = "WHERE posts.status = 'published'";
elseif ($filter === 'drafts') $whereClause = "WHERE posts.status = 'draft'";
elseif ($filter === 'pending') $whereClause = "WHERE posts.status = 'pending'";

$posts = $conn->query("
    SELECT posts.id, posts.title, posts.status, posts.created_at, categories.name AS category_name
    FROM posts
    LEFT JOIN categories ON posts.category_id = categories.id
    $whereClause
    ORDER BY posts.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posts | MiniPress CMS</title>
    <link rel="stylesheet" href="../assets/css/admin.css?v=105">
    <style>
        .badge-pending {
            background: #fee2e2;
            color: #b91c1c;
        }
    </style>
</head>
<body>
<div class="admin-page" id="adminPage">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar-brand">MiniPress</div>
        <nav class="admin-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="posts.php" class="active">Posts</a>
            <a href="pending-posts.php">⏳ Pending Posts</a>
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
            <div class="page-heading page-heading-inline">
                <div>
                    <h1>Posts</h1>
                    <p>Manage your blog posts</p>
                </div>
                <a href="add-post.php" class="add-post-btn">+ Add New Post</a>
            </div>

            <div class="content-card">
                <div class="post-tabs">
                    <a href="posts.php?filter=all" class="tab <?php echo $filter === 'all' ? 'active' : ''; ?>">All (<?php echo $allCount; ?>)</a>
                    <a href="posts.php?filter=published" class="tab <?php echo $filter === 'published' ? 'active' : ''; ?>">Published (<?php echo $publishedCount; ?>)</a>
                    <a href="posts.php?filter=drafts" class="tab <?php echo $filter === 'drafts' ? 'active' : ''; ?>">Drafts (<?php echo $draftCount; ?>)</a>
                    <a href="posts.php?filter=pending" class="tab <?php echo $filter === 'pending' ? 'active' : ''; ?>">Pending (<?php echo $pendingCount; ?>)</a>
                </div>

                <table class="content-table posts-table">
                    <thead>
                        <tr>
                            <th>TITLE</th>
                            <th>CATEGORY</th>
                            <th>STATUS</th>
                            <th>DATE</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($posts && $posts->num_rows > 0): ?>
                            <?php while ($row = $posts->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <a href="edit-post.php?id=<?php echo $row['id']; ?>" class="post-title-link">
                                            <?php echo htmlspecialchars($row['title']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="category-pill">
                                            <?php echo htmlspecialchars($row['category_name'] ?? 'Uncategorized'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php
                                            if ($row['status'] === 'published') echo 'badge-green';
                                            elseif ($row['status'] === 'pending') echo 'badge-pending';
                                            else echo 'badge-orange';
                                        ?>">
                                            <?php echo ucfirst(htmlspecialchars($row['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date("M d, Y", strtotime($row['created_at'])); ?></td>
                                    <td class="action-cell">
                                        <a href="edit-post.php?id=<?php echo $row['id']; ?>" class="icon-btn">✎</a>
                                        <a href="delete-post.php?id=<?php echo $row['id']; ?>" class="icon-btn delete-btn" onclick="return confirm('Delete this post?');">🗑</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align:center; color:#667085;">No posts found.</td></tr>
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
    toggle.addEventListener('click', () => {
        sidebar.classList.toggle('sidebar-collapsed');
    });
</script>
</body>
</html>
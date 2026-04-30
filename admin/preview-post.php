<?php
require_once 'includes/auth.php';
require_once '../config/database.php';

$adminName = $_SESSION['admin_username'] ?? 'Admin';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header("Location: pending-posts.php"); exit(); }

$stmt = $conn->prepare("
    SELECT posts.*, categories.name AS category_name, users.username AS author_name
    FROM posts
    LEFT JOIN categories ON posts.category_id = categories.id
    LEFT JOIN users ON posts.author_id = users.id
    WHERE posts.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if (!$result || $result->num_rows === 0) { header("Location: pending-posts.php"); exit(); }
$post = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Preview Post | MiniPress</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .post-title { font-size: 32px; font-weight: 700; margin-bottom: 12px; }
        .post-meta { font-size: 14px; color: #667085; margin-bottom: 24px; display: flex; gap: 16px; flex-wrap: wrap; }
        .post-body { font-size: 16px; line-height: 1.9; color: #374151; white-space: pre-line; }
        .post-divider { border: none; border-top: 1px solid #e5e7eb; margin: 20px 0; }
        .back-link { color: #5a4efc; font-weight: 700; font-size: 14px; display: inline-block; margin-bottom: 20px; }
        .action-bar { display: flex; gap: 12px; margin-top: 28px; }
        .approve-btn {
            padding: 12px 22px; border-radius: 12px;
            background: #dcfce7; color: #15803d; font-weight: 700;
        }
        .reject-btn {
            padding: 12px 22px; border-radius: 12px;
            background: #fee2e2; color: #b91c1c; font-weight: 700;
        }
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
            <a href="pending-posts.php" class="back-link">← Back to Pending Posts</a>

            <div class="content-card">
                <?php if ($post['category_name']): ?>
                    <span class="category-pill"><?php echo htmlspecialchars($post['category_name']); ?></span>
                    <br><br>
                <?php endif; ?>

                <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>

                <div class="post-meta">
                    <span>👤 Submitted by: <strong><?php echo htmlspecialchars($post['author_name'] ?? 'Unknown'); ?></strong></span>
                    <span>📅 <?php echo date("F d, Y", strtotime($post['created_at'])); ?></span>
                    <span class="badge badge-orange">⏳ Pending Approval</span>
                </div>

                <hr class="post-divider">

                <div class="post-body">
                    <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                </div>

                <div class="action-bar">
                    <a href="pending-posts.php?approve=<?php echo $post['id']; ?>"
                       class="approve-btn"
                       onclick="return confirm('Approve and publish this post?');">✅ Approve & Publish</a>
                    <a href="pending-posts.php?reject=<?php echo $post['id']; ?>"
                       class="reject-btn"
                       onclick="return confirm('Reject and delete this post?');">❌ Reject Post</a>
                </div>
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
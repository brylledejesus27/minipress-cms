<?php
require_once 'auth.php';
require_once '../config/database.php';

$username = $_SESSION['user_username'] ?? 'User';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header("Location: dashboard.php"); exit(); }

$stmt = $conn->prepare("
    SELECT posts.*, categories.name AS category_name
    FROM posts
    LEFT JOIN categories ON posts.category_id = categories.id
    WHERE posts.id = ? AND posts.status = 'published'
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if (!$result || $result->num_rows === 0) { header("Location: dashboard.php"); exit(); }
$post = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> | MiniPress</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .post-content-wrap { max-width: 780px; }
        .post-title { font-size: 36px; font-weight: 700; line-height: 1.3; margin-bottom: 14px; }
        .post-meta { display: flex; gap: 14px; align-items: center; margin-bottom: 28px; flex-wrap: wrap; }
        .post-meta span { font-size: 14px; color: #667085; }
        .post-body { font-size: 16px; line-height: 1.9; color: #374151; white-space: pre-line; }
        .back-link { color: #5a4efc; font-weight: 700; font-size: 14px; display: inline-block; margin-bottom: 20px; }
        .post-divider { border: none; border-top: 1px solid #e5e7eb; margin: 28px 0; }
    </style>
</head>
<body>
<div class="admin-page" id="adminPage">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar-brand">MiniPress</div>
        <nav class="admin-nav">
            <a href="dashboard.php">📰 Posts</a>
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
            <a href="dashboard.php" class="back-link">← Back to Posts</a>

            <div class="content-card post-content-wrap">
                <?php if ($post['category_name']): ?>
                    <span class="category-pill"><?php echo htmlspecialchars($post['category_name']); ?></span>
                    <br><br>
                <?php endif; ?>

                <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>

                <div class="post-meta">
                    <span>📅 <?php echo date("F d, Y", strtotime($post['published_at'] ?? $post['created_at'])); ?></span>
                    <?php if ($post['is_pinned']): ?>
                        <span>📌 Pinned Post</span>
                    <?php endif; ?>
                </div>

                <hr class="post-divider">

                <div class="post-body">
                    <?php echo nl2br(htmlspecialchars($post['content'])); ?>
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
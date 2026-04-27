<?php
require_once 'includes/auth.php';
require_once '../config/database.php';

$adminName = $_SESSION['admin_username'] ?? 'Admin';
$error = "";
$success = "";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header("Location: posts.php"); exit(); }

$categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $catId   = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $status  = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
    $isPinned = isset($_POST['is_pinned']) ? 1 : 0;
    $publishedAt = $status === 'published' ? date('Y-m-d H:i:s') : null;

    if ($title === '' || $content === '') {
        $error = "Title and content are required.";
    } else {
        $stmt = $conn->prepare("UPDATE posts SET title=?, content=?, category_id=?, status=?, is_pinned=?, published_at=? WHERE id=?");
        $stmt->bind_param("ssisssi", $title, $content, $catId, $status, $isPinned, $publishedAt, $id);
        if ($stmt->execute()) {
            $success = "Post updated successfully!";
        } else {
            $error = "Failed to update post.";
        }
        $stmt->close();
    }
}

// Fetch post
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if (!$result || $result->num_rows === 0) { header("Location: posts.php"); exit(); }
$post = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post | MiniPress CMS</title>
    <link rel="stylesheet" href="../assets/css/admin.css?v=104">
    <style>
        .post-form-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .form-card { background: #fff; border-radius: 18px; padding: 22px; box-shadow: 0 4px 14px rgba(16,24,40,0.06); }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 700; color: #111827; }
        .form-group input[type="text"], .form-group textarea, .form-group select {
            width: 100%; border: 1px solid #dfe4ee; border-radius: 12px;
            padding: 14px; font-size: 14px; outline: none;
        }
        .form-group textarea { min-height: 280px; resize: vertical; }
        .save-btn {
            display: inline-flex; align-items: center; justify-content: center;
            width: 100%; border: none; border-radius: 12px; padding: 14px 18px;
            background: linear-gradient(90deg, #5a4efc 0%, #6b5dfc 100%);
            color: #fff; font-weight: 700; font-size: 15px; cursor: pointer;
        }
        .alert-error { background: #fee2e2; color: #b91c1c; padding: 12px 14px; border-radius: 10px; margin-bottom: 16px; }
        .alert-success { background: #dcfce7; color: #15803d; padding: 12px 14px; border-radius: 10px; margin-bottom: 16px; }
        .checkbox-row { display: flex; align-items: center; gap: 10px; }
        .back-link { color: #5a4efc; font-weight: 700; font-size: 14px; margin-bottom: 14px; display: inline-block; }
        @media (max-width: 900px) { .post-form-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="admin-page" id="adminPage">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar-brand">MiniPress</div>
        <nav class="admin-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="posts.php" class="active">Posts</a>
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
            <a href="posts.php" class="back-link">← Back to Posts</a>
            <div class="page-heading page-heading-inline">
                <div>
                    <h1>Edit Post</h1>
                    <p>Update your post content and settings</p>
                </div>
            </div>

            <?php if ($error): ?><div class="alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

            <form method="POST">
                <div class="post-form-grid">
                    <div class="form-card">
                        <div class="form-group">
                            <label>Post Title</label>
                            <input type="text" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Content</label>
                            <textarea name="content" required><?php echo htmlspecialchars($post['content']); ?></textarea>
                        </div>
                    </div>

                    <div class="form-card">
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category_id">
                                <option value="">Select category</option>
                                <?php if ($categories && $categories->num_rows > 0): ?>
                                    <?php while ($cat = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo $post['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status">
                                <option value="draft" <?php echo $post['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                <option value="published" <?php echo $post['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Options</label>
                            <div class="checkbox-row">
                                <input type="checkbox" id="is_pinned" name="is_pinned" <?php echo $post['is_pinned'] ? 'checked' : ''; ?>>
                                <label for="is_pinned">Pin this post</label>
                            </div>
                        </div>
                        <button type="submit" class="save-btn">Update Post</button>
                    </div>
                </div>
            </form>
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
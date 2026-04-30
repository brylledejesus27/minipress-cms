<?php
require_once 'auth.php';
require_once '../config/database.php';

$userId   = $_SESSION['user_id'];
$username = $_SESSION['user_username'] ?? 'User';
$error    = "";
$success  = "";

$categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $catId   = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;

    if ($title === '' || $content === '') {
        $error = "Title and content are required.";
    } else {
        // Generate slug
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $slug = $slug . '-' . time();

        $stmt = $conn->prepare("INSERT INTO posts (author_id, title, slug, content, category_id, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("isssi", $userId, $title, $slug, $content, $catId);

        if ($stmt->execute()) {
            $success = "Post submitted! Waiting for admin approval.";
        } else {
            $error = "Failed to submit post. Please try again.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Write Post | MiniPress</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .post-form-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .form-card { background: #fff; border-radius: 18px; padding: 22px; box-shadow: 0 4px 14px rgba(16,24,40,0.06); }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 700; color: #111827; font-size: 14px; }
        .form-group input[type="text"],
        .form-group textarea,
        .form-group select {
            width: 100%; border: 1px solid #dfe4ee;
            border-radius: 12px; padding: 14px; font-size: 14px; outline: none;
            background: #f9fafb; transition: all 0.2s;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: #6b5dfc; background: #fff;
            box-shadow: 0 0 0 3px rgba(107,93,252,0.15);
        }
        .form-group textarea { min-height: 320px; resize: vertical; }
        .save-btn {
            width: 100%; border: none; border-radius: 12px; padding: 14px;
            background: linear-gradient(90deg, #5a4efc, #6b5dfc);
            color: #fff; font-weight: 700; font-size: 15px; cursor: pointer;
        }
        .alert-error { background: #fee2e2; color: #b91c1c; padding: 12px 14px; border-radius: 10px; margin-bottom: 16px; font-weight: 600; }
        .alert-success { background: #dcfce7; color: #15803d; padding: 12px 14px; border-radius: 10px; margin-bottom: 16px; font-weight: 600; }
        .pending-info {
            background: #fffbeb; border: 1px solid #fde68a;
            border-radius: 12px; padding: 14px; margin-bottom: 18px;
            font-size: 14px; color: #92400e;
        }
        @media (max-width: 900px) { .post-form-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="admin-page" id="adminPage">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar-brand">MiniPress</div>
        <nav class="admin-nav">
            <a href="dashboard.php">📰 Posts</a>
            <a href="create-post.php" class="active">✏️ Write Post</a>
            <a href="my-posts.php">📋 My Posts</a>
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
                    <h1>Write a Post</h1>
                    <p>Submit your post for admin approval</p>
                </div>
            </div>

            <?php if ($error): ?><div class="alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

            <div class="pending-info">
                ⏳ Your post will be reviewed by an admin before it goes live.
            </div>

            <form method="POST">
                <div class="post-form-grid">
                    <div class="form-card">
                        <div class="form-group">
                            <label>Post Title</label>
                            <input type="text" name="title" placeholder="Enter your post title..." required>
                        </div>
                        <div class="form-group">
                            <label>Content</label>
                            <textarea name="content" placeholder="Write your post content here..." required></textarea>
                        </div>
                    </div>

                    <div class="form-card">
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category_id">
                                <option value="">Select a category</option>
                                <?php if ($categories && $categories->num_rows > 0): ?>
                                    <?php while ($cat = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo $cat['id']; ?>">
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="pending-info" style="margin-top: 10px;">
                            <strong>📌 Note:</strong><br>
                            After submitting, your post status will be <strong>Pending</strong> until an admin approves it.
                        </div>

                        <br>
                        <button type="submit" class="save-btn">📨 Submit for Approval</button>
                    </div>
                </div>
            </form>
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
<?php
require_once 'includes/auth.php';
require_once '../config/database.php';

$adminName = $_SESSION['admin_username'] ?? 'Admin';
$error = "";
$success = "";

function makeSlug($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text ?: 'post-' . time();
}

$categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $status = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
    $isPinned = isset($_POST['is_pinned']) ? 1 : 0;

    if ($title === '' || $content === '') {
        $error = "Title and content are required.";
    } else {
        $slug = makeSlug($title);

        $check = $conn->prepare("SELECT id FROM posts WHERE slug = ?");
        $check->bind_param("s", $slug);
        $check->execute();
        $checkResult = $check->get_result();

        if ($checkResult && $checkResult->num_rows > 0) {
            $slug .= '-' . time();
        }
        $check->close();

        $publishedAt = $status === 'published' ? date('Y-m-d H:i:s') : null;

        $stmt = $conn->prepare("
            INSERT INTO posts (title, slug, content, category_id, featured_image, status, is_pinned, published_at)
            VALUES (?, ?, ?, ?, NULL, ?, ?, ?)
        ");

        $stmt->bind_param(
            "sssisis",
            $title,
            $slug,
            $content,
            $categoryId,
            $status,
            $isPinned,
            $publishedAt
        );

        if ($stmt->execute()) {
            header("Location: posts.php");
            exit();
        } else {
            $error = "Failed to save post.";
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
    <title>Add New Post | MiniPress CMS</title>
    <link rel="stylesheet" href="../assets/css/admin.css?v=102">
    <style>
        .post-form-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }
        .form-card {
            background: #ffffff;
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 4px 14px rgba(16, 24, 40, 0.06);
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
            color: #111827;
        }
        .form-group input[type="text"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            border: 1px solid #dfe4ee;
            border-radius: 12px;
            padding: 14px;
            font-size: 14px;
            outline: none;
        }
        .form-group textarea {
            min-height: 280px;
            resize: vertical;
        }
        .save-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            border: none;
            border-radius: 12px;
            padding: 14px 18px;
            background: linear-gradient(90deg, #5a4efc 0%, #6b5dfc 100%);
            color: #fff;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
        }
        .alert-error {
            background: #fee2e2;
            color: #b91c1c;
            padding: 12px 14px;
            border-radius: 10px;
            margin-bottom: 16px;
        }
        .checkbox-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        @media (max-width: 900px) {
            .post-form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="admin-page">
    <aside class="admin-sidebar">
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
            <div class="page-heading page-heading-inline">
                <div>
                    <h1>Add New Post</h1>
                    <p>Create a new post for MiniPress</p>
                </div>
            </div>

            <?php if ($error !== ""): ?>
                <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="post-form-grid">
                    <div class="form-card">
                        <div class="form-group">
                            <label for="title">Post Title</label>
                            <input type="text" id="title" name="title" placeholder="Enter post title" required>
                        </div>

                        <div class="form-group">
                            <label for="content">Content</label>
                            <textarea id="content" name="content" placeholder="Write your content here..." required></textarea>
                        </div>
                    </div>

                    <div class="form-card">
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select name="category_id" id="category_id">
                                <option value="">Select category</option>
                                <?php if ($categories && $categories->num_rows > 0): ?>
                                    <?php while ($category = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo $category['id']; ?>">
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Options</label>
                            <div class="checkbox-row">
                                <input type="checkbox" id="is_pinned" name="is_pinned">
                                <label for="is_pinned">Pin this post</label>
                            </div>
                        </div>

                        <button type="submit" class="save-btn">Save Post</button>
                    </div>
                </div>
            </form>
        </section>
    </main>
</div>
</body>
</html>
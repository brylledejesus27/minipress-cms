<?php
require_once 'includes/auth.php';
require_once '../config/database.php';

if (isset($_POST['add'])) {
    $title = trim($_POST['title']);
    $slug = strtolower(str_replace(' ', '-', $title));
    $content = $_POST['content'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("INSERT INTO pages (title, slug, content, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $title, $slug, $content, $status);
    $stmt->execute();

    header("Location: pages.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM pages WHERE id=$id");

    header("Location: pages.php");
    exit();
}

if (isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $title = trim($_POST['title']);
    $slug = strtolower(str_replace(' ', '-', $title));
    $content = $_POST['content'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE pages SET title=?, slug=?, content=?, status=? WHERE id=?");
    $stmt->bind_param("ssssi", $title, $slug, $content, $status, $id);
    $stmt->execute();

    header("Location: pages.php");
    exit();
}

$pages = $conn->query("SELECT * FROM pages ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Pages</title>
<link rel="stylesheet" href="../assets/css/admin.css">

<style>
.pages-form .form-group{margin-bottom:18px;}
.pages-form label{display:block;font-size:14px;font-weight:600;margin-bottom:6px;color:#374151;}
.pages-form input,
.pages-form textarea,
.pages-form select{
width:100%;
padding:14px 16px;
border-radius:14px;
border:1px solid #e5e7eb;
background:#f9fafb;
font-size:14px;
transition:.25s;
outline:none;
}
.pages-form textarea{min-height:160px;resize:vertical;}
.pages-form input:focus,
.pages-form textarea:focus,
.pages-form select:focus{
background:#fff;
border-color:#6b5dfc;
box-shadow:0 0 0 3px rgba(107,93,252,.15);
}
</style>

</head>

<body>

<div class="admin-page">

<aside class="admin-sidebar">
<div class="admin-sidebar-brand">MiniPress</div>

<nav class="admin-nav">
<a href="dashboard.php">Dashboard</a>
<a href="posts.php">Posts</a>
<a href="categories.php">Categories</a>
<a href="pages.php" class="active">Pages</a>
<a href="media.php">Media</a>
<a href="users.php">Users</a>
<a href="settings.php">Settings</a>
</nav>
</aside>

<main class="admin-main">
<div class="admin-content">

<h1>Pages</h1>
<p>Manage your pages</p>

<div class="content-card pages-form">
<form method="POST">

<div class="form-group">
<label>Page Title</label>
<input type="text" name="title" required>
</div>

<div class="form-group">
<label>Content</label>
<textarea name="content"></textarea>
</div>

<div class="form-group">
<label>Status</label>
<select name="status">
<option value="draft">Draft</option>
<option value="published">Published</option>
</select>
</div>

<button class="add-post-btn" name="add">+ Add Page</button>

</form>
</div>

<br>

<div class="content-card">
<table class="content-table">
<tr>
<th>Title</th>
<th>Slug</th>
<th>Status</th>
<th>Actions</th>
</tr>

<?php while($row = $pages->fetch_assoc()): ?>
<tr>
<td><?php echo htmlspecialchars($row['title']); ?></td>
<td><?php echo $row['slug']; ?></td>
<td>
<span class="badge <?php echo $row['status']=='published' ? 'badge-green':'badge-orange'; ?>">
<?php echo $row['status']; ?>
</span>
</td>
<td class="action-cell">
<a href="pages.php?edit=<?php echo $row['id']; ?>" class="icon-btn">✎</a>
<a href="pages.php?delete=<?php echo $row['id']; ?>" class="icon-btn delete-btn">🗑</a>
</td>
</tr>
<?php endwhile; ?>
</table>
</div>

<?php if (isset($_GET['edit']) && is_numeric($_GET['edit'])): ?>

<?php
$id = (int)$_GET['edit'];
$result = $conn->query("SELECT * FROM pages WHERE id = $id");

if ($result && $result->num_rows > 0):
$edit = $result->fetch_assoc();
?>

<br>

<div class="content-card pages-form">
<form method="POST">

<input type="hidden" name="id" value="<?php echo $edit['id']; ?>">

<div class="form-group">
<label>Title</label>
<input type="text" name="title" value="<?php echo htmlspecialchars($edit['title']); ?>" required>
</div>

<div class="form-group">
<label>Content</label>
<textarea name="content"><?php echo htmlspecialchars($edit['content']); ?></textarea>
</div>

<div class="form-group">
<label>Status</label>
<select name="status">
<option value="draft" <?php if($edit['status']=='draft') echo 'selected'; ?>>Draft</option>
<option value="published" <?php if($edit['status']=='published') echo 'selected'; ?>>Published</option>
</select>
</div>

<button class="add-post-btn" name="update">Update</button>

</form>
</div>

<?php else: ?>
<p style="color:red;">Page not found.</p>
<?php endif; ?>

<?php endif; ?>

</div>
</main>
</div>

</body>
</html>
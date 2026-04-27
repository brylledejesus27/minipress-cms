<?php
require_once 'includes/auth.php';
require_once '../config/database.php';

$adminName = $_SESSION['admin_username'] ?? 'Admin';

$editMode = false;
$editData = null;

if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $result = $conn->query("SELECT * FROM pages WHERE id = $id");

    if ($result && $result->num_rows > 0) {
        $editMode = true;
        $editData = $result->fetch_assoc();
    }
}

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

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM pages WHERE id=$id");

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
<link rel="stylesheet" href="../assets/css/admin.css?v=101">

<style>
.pages-form .form-group{
margin-bottom:18px;
}

.pages-form label{
display:block;
font-size:14px;
font-weight:600;
margin-bottom:8px;
color:#374151;
}

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

.pages-form textarea{
min-height:180px;
resize:vertical;
}

.pages-form input:focus,
.pages-form textarea:focus,
.pages-form select:focus{
background:#fff;
border-color:#6b5dfc;
box-shadow:0 0 0 3px rgba(107,93,252,.15);
}

.page-heading{
margin-bottom:24px;
}

.page-heading h1,
.page-heading-inline h1{
font-size:44px;
font-weight:700;
color:#111827;
margin-bottom:6px;
}

.page-heading p,
.page-heading-inline p{
font-size:15px;
color:#667085;
}

.flex-actions{
display:flex;
justify-content:space-between;
align-items:center;
gap:14px;
margin-top:8px;
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
<a href="categories.php">Categories</a>
<a href="pages.php" class="active">Pages</a>
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
<h1>Pages</h1>
<p>Manage your website pages</p>
</div>
</div>

<div class="content-card pages-form" style="margin-bottom:20px;">
<form method="POST">

<?php if($editMode): ?>
<input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
<?php endif; ?>

<div class="form-group">
<label>Page Title</label>
<input type="text" name="title" value="<?php echo $editMode ? htmlspecialchars($editData['title']) : ''; ?>" required>
</div>

<div class="form-group">
<label>Content</label>
<textarea name="content"><?php echo $editMode ? htmlspecialchars($editData['content']) : ''; ?></textarea>
</div>

<div class="form-group">
<label>Status</label>
<select name="status">
<option value="draft" <?php if($editMode && $editData['status']=='draft') echo 'selected'; ?>>Draft</option>
<option value="published" <?php if($editMode && $editData['status']=='published') echo 'selected'; ?>>Published</option>
<?php if(!$editMode): ?>
<option value="published">Published</option>
<?php endif; ?>
</select>
</div>

<div class="flex-actions">
<button class="add-post-btn" name="<?php echo $editMode ? 'update' : 'add'; ?>">
<?php echo $editMode ? 'Update Page' : '+ Add Page'; ?>
</button>

<a href="media.php" class="add-post-btn">Open Media</a>
</div>

</form>
</div>

<div class="content-card">

<table class="content-table">
<thead>
<tr>
<th>TITLE</th>
<th>SLUG</th>
<th>STATUS</th>
<th>ACTIONS</th>
</tr>
</thead>

<tbody>
<?php if ($pages && $pages->num_rows > 0): ?>
<?php while ($row = $pages->fetch_assoc()): ?>
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
<a href="pages.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete page?')" class="icon-btn delete-btn">🗑</a>
</td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="4">No pages found.</td></tr>
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
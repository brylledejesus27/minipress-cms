<?php
require_once 'includes/auth.php';
require_once '../config/database.php';

$adminName = $_SESSION['admin_username'] ?? 'Admin';

$editMode = false;
$editData = null;

if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $result = $conn->query("SELECT * FROM categories WHERE id = $id");

    if ($result && $result->num_rows > 0) {
        $editMode = true;
        $editData = $result->fetch_assoc();
    }
}

if (isset($_POST['add'])) {
    $name = trim($_POST['name']);
    $slug = strtolower(str_replace(' ', '-', $name));

    $stmt = $conn->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $slug);
    $stmt->execute();

    header("Location: categories.php");
    exit();
}

if (isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $slug = strtolower(str_replace(' ', '-', $name));

    $stmt = $conn->prepare("UPDATE categories SET name=?, slug=? WHERE id=?");
    $stmt->bind_param("ssi", $name, $slug, $id);
    $stmt->execute();

    header("Location: categories.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM categories WHERE id=$id");

    header("Location: categories.php");
    exit();
}

$categories = $conn->query("
    SELECT c.*, COUNT(p.id) AS post_count
    FROM categories c
    LEFT JOIN posts p ON p.category_id = c.id
    GROUP BY c.id
    ORDER BY c.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Categories</title>
<link rel="stylesheet" href="../assets/css/admin.css?v=101">

<style>
.pages-form .form-group{margin-bottom:18px;}
.pages-form label{display:block;font-size:14px;font-weight:600;margin-bottom:6px;color:#374151;}
.pages-form input{
width:100%;
padding:14px 16px;
border-radius:14px;
border:1px solid #e5e7eb;
background:#f9fafb;
font-size:14px;
transition:.25s;
outline:none;
}
.pages-form input:focus{
background:#fff;
border-color:#6b5dfc;
box-shadow:0 0 0 3px rgba(107,93,252,.15);
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
<a href="categories.php" class="active">Categories</a>
<a href="pages.php">Pages</a>
<a href="#">Media</a>
<a href="#">Users</a>
<a href="#">Settings</a>
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
<h1>Categories</h1>
<p>Manage your categories</p>
</div>
</div>

<div class="content-card pages-form" style="margin-bottom:20px;">
<form method="POST">

<?php if($editMode): ?>
<input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
<?php endif; ?>

<div class="form-group">
<label>Category Name</label>
<input type="text" name="name"
value="<?php echo $editMode ? htmlspecialchars($editData['name']) : ''; ?>"
required>
</div>

<button class="add-post-btn" name="<?php echo $editMode ? 'update' : 'add'; ?>">
<?php echo $editMode ? 'Update Category' : '+ Add New Category'; ?>
</button>

</form>
</div>

<div class="content-card">

<table class="content-table">
<thead>
<tr>
<th>NAME</th>
<th>SLUG</th>
<th>POSTS</th>
<th>ACTIONS</th>
</tr>
</thead>

<tbody>
<?php if ($categories && $categories->num_rows > 0): ?>
<?php while ($row = $categories->fetch_assoc()): ?>
<tr>
<td><?php echo htmlspecialchars($row['name']); ?></td>
<td><?php echo $row['slug']; ?></td>
<td><?php echo $row['post_count']; ?></td>
<td class="action-cell">
<a href="categories.php?edit=<?php echo $row['id']; ?>" class="icon-btn">✎</a>
<a href="categories.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete category?')" class="icon-btn delete-btn">🗑</a>
</td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="4">No categories found.</td></tr>
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
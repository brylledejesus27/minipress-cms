<?php
require_once 'includes/auth.php';
require_once '../config/database.php';

if (isset($_POST['add'])) {
    $name = trim($_POST['name']);
    $slug = strtolower(str_replace(' ', '-', $name));

    $stmt = $conn->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $slug);
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

$categories = $conn->query("
    SELECT c.*, COUNT(p.id) AS post_count
    FROM categories c
    LEFT JOIN posts p ON p.category_id = c.id
    GROUP BY c.id
    ORDER BY c.id DESC
");

$adminName = $_SESSION['admin_username'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Categories | MiniPress CMS</title>
<link rel="stylesheet" href="../assets/css/admin.css?v=101">
</head>

<body>
<div class="admin-page">

<aside class="admin-sidebar">
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
<h1>Categories</h1>
<p>Manage your categories</p>
</div>
</div>

<div class="content-card" style="margin-bottom:20px;">
<form method="POST">
<input type="text" name="name" placeholder="Category name" required>
<button class="add-post-btn" name="add">+ Add New Category</button>
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
<tr>
<td colspan="4">No categories found.</td>
</tr>
<?php endif; ?>
</tbody>
</table>
</div>

<?php if (isset($_GET['edit'])):
$id = (int)$_GET['edit'];
$edit = $conn->query("SELECT * FROM categories WHERE id=$id")->fetch_assoc();
?>

<div class="content-card" style="margin-top:20px;">
<h3>Edit Category</h3>

<form method="POST">
<input type="hidden" name="id" value="<?php echo $edit['id']; ?>">
<input type="text" name="name" value="<?php echo htmlspecialchars($edit['name']); ?>" required>
<button class="add-post-btn" name="update">Update</button>
</form>
</div>

<?php endif; ?>

</section>
</main>
</div>
</body>
</html>
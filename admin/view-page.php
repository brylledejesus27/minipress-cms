<?php
require_once 'includes/auth.php';
require_once '../config/database.php';

$adminName = $_SESSION['admin_username'] ?? 'Admin';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid page.");
}

$id = (int)$_GET['id'];

$result = $conn->query("SELECT * FROM pages WHERE id = $id");

if (!$result || $result->num_rows === 0) {
    die("Page not found.");
}

$page = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($page['title']); ?></title>
<link rel="stylesheet" href="../assets/css/admin.css">

<style>
.view-container{
max-width:900px;
margin:auto;
}

.view-title{
font-size:32px;
font-weight:700;
margin-bottom:10px;
}

.view-meta{
color:#6b7280;
margin-bottom:20px;
}

.view-content{
background:#fff;
padding:20px;
border-radius:12px;
line-height:1.6;
border:1px solid #e5e7eb;
white-space:pre-wrap;
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

<section class="admin-content view-container">

<div class="page-heading">
<h1><?php echo htmlspecialchars($page['title']); ?></h1>
<p>Status: <?php echo ucfirst($page['status']); ?></p>
</div>

<div class="view-content">
<?php echo htmlspecialchars($page['content']); ?>
</div>

<br>

<a href="pages.php" class="add-post-btn">← Back to Pages</a>

</section>

</main>
</div>

<script>
const toggle = document.getElementById('sidebarToggle');
const sidebar = document.querySelector('.admin-sidebar');
toggle.addEventListener('click', () => {
    sidebar.classList.toggle('sidebar-collapsed');
});
</script>

</body>
</html>
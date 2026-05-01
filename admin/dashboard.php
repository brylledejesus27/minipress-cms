<?php
require_once 'includes/auth.php';
require_once '../config/database.php';

$adminName = $_SESSION['admin_username'] ?? 'Admin';

$totalPosts     = 0;
$publishedPosts = 0;
$draftPosts     = 0;
$totalUsers     = 0;
$pendingPosts   = 0;

// Using GetDashboardStats stored procedure
$result = $conn->query("CALL GetDashboardStats()");
if ($result && $row = $result->fetch_assoc()) {
    $totalPosts     = (int)$row['total_posts'];
    $publishedPosts = (int)$row['published_posts'];
    $draftPosts     = (int)$row['draft_posts'];
    $pendingPosts   = (int)$row['pending_posts'];
    $totalUsers     = (int)$row['total_users'];
    $result->free();
}
// Clear stored procedure results before next query
while ($conn->more_results() && $conn->next_result()) {}

$recentPosts = $conn->query("SELECT title, status, created_at FROM posts ORDER BY created_at DESC LIMIT 4");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | MiniPress CMS</title>
    <link rel="stylesheet" href="../assets/css/admin.css?v=107">
    <style>
        .stat-red { color: #ef4444; }
        .stats-grid-5 {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 18px;
            margin-top: 24px;
            margin-bottom: 24px;
        }
        .stats-grid-5 .stat-card:nth-child(5)::before {
            background: linear-gradient(90deg, #ef4444, #f87171);
        }
        .stats-grid-5 .stat-card:nth-child(5) .stat-value {
            background: linear-gradient(135deg, #ef4444, #f87171);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            filter: drop-shadow(0 0 8px rgba(239,68,68,0.3));
        }
        @media (max-width: 1400px) { .stats-grid-5 { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 900px)  { .stats-grid-5 { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 640px)  { .stats-grid-5 { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="admin-page" id="adminPage">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar-brand">MiniPress</div>
        <nav class="admin-nav">
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="posts.php">Posts</a>
            <a href="pending-posts.php">⏳ Pending Posts
                <?php if ($pendingPosts > 0): ?>
                    <span style="background:#ef4444;color:#fff;font-size:11px;font-weight:700;padding:2px 7px;border-radius:999px;margin-left:6px;">
                        <?php echo $pendingPosts; ?>
                    </span>
                <?php endif; ?>
            </a>
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
            <div class="page-heading">
                <h1>Dashboard</h1>
                <p>Welcome back! Here's what's happening with your site.</p>
            </div>

            <div class="stats-grid-5">
                <div class="stat-card">
                    <div class="stat-title stat-blue">TOTAL POSTS</div>
                    <div class="stat-value"><?php echo $totalPosts; ?></div>
                    <div class="stat-sub">Live post count</div>
                </div>
                <div class="stat-card">
                    <div class="stat-title stat-green">PUBLISHED</div>
                    <div class="stat-value"><?php echo $publishedPosts; ?></div>
                    <div class="stat-sub">Visible on site</div>
                </div>
                <div class="stat-card">
                    <div class="stat-title stat-orange">DRAFTS</div>
                    <div class="stat-value"><?php echo $draftPosts; ?></div>
                    <div class="stat-sub">Not published yet</div>
                </div>
                <div class="stat-card">
                    <div class="stat-title stat-purple">TOTAL USERS</div>
                    <div class="stat-value"><?php echo $totalUsers; ?></div>
                    <div class="stat-sub">Registered users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-title stat-red">PENDING</div>
                    <div class="stat-value"><?php echo $pendingPosts; ?></div>
                    <div class="stat-sub">Awaiting approval</div>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="content-card">
                    <div class="content-card-header">
                        <h3>Recent Posts</h3>
                        <a href="posts.php">View All Posts →</a>
                    </div>
                    <table class="content-table">
                        <thead>
                            <tr>
                                <th>TITLE</th>
                                <th>STATUS</th>
                                <th>DATE</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recentPosts && $recentPosts->num_rows > 0): ?>
                                <?php while ($post = $recentPosts->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($post['title']); ?></td>
                                        <td>
                                            <span class="badge <?php
                                                if ($post['status'] === 'published') echo 'badge-green';
                                                elseif ($post['status'] === 'pending') echo 'badge-pending';
                                                else echo 'badge-orange';
                                            ?>">
                                                <?php echo ucfirst(htmlspecialchars($post['status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date("M d, Y", strtotime($post['created_at'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="3">No posts found yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="content-card quick-actions-card">
                    <h3>Quick Actions</h3>
                    <a href="add-post.php" class="quick-btn quick-primary">+ New Post</a>
                    <a href="pending-posts.php" class="quick-btn quick-danger">⏳ Review Pending
                        <?php if ($pendingPosts > 0): ?>(<?php echo $pendingPosts; ?>)<?php endif; ?>
                    </a>
                    <a href="pages.php" class="quick-btn quick-light">+ New Page</a>
                    <a href="media.php" class="quick-btn quick-success">⇪ Upload Media</a>
                    <a href="../logout.php" class="quick-btn quick-light">Logout</a>
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
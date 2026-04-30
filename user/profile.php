<?php
require_once 'auth.php';
require_once '../config/database.php';

$userId   = $_SESSION['user_id'];
$username = $_SESSION['user_username'] ?? 'User';
$error    = "";
$success  = "";

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = trim($_POST['username'] ?? '');
    $newPassword = trim($_POST['password'] ?? '');
    $confirm     = trim($_POST['confirm_password'] ?? '');

    if ($newUsername === '') {
        $error = "Username is required.";
    } elseif ($newPassword !== '' && $newPassword !== $confirm) {
        $error = "Passwords do not match.";
    } elseif ($newPassword !== '' && strlen($newPassword) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $check->bind_param("si", $newUsername, $userId);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = "Username already taken.";
        } else {
            if ($newPassword !== '') {
                $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
                $stmt->bind_param("ssi", $newUsername, $hashed, $userId);
            } else {
                $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
                $stmt->bind_param("si", $newUsername, $userId);
            }

            if ($stmt->execute()) {
                $_SESSION['user_username'] = $newUsername;
                $username = $newUsername;
                $success  = "Profile updated successfully!";
            } else {
                $error = "Failed to update profile.";
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | MiniPress</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .profile-card { max-width: 500px; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-weight: 700; font-size: 14px; margin-bottom: 8px; color: #374151; }
        .form-group input {
            width: 100%; padding: 14px 16px; border: 1px solid #e5e7eb;
            border-radius: 12px; font-size: 15px; outline: none; background: #f9fafb;
        }
        .form-group input:focus { border-color: #6b5dfc; background: #fff; box-shadow: 0 0 0 3px rgba(107,93,252,0.15); }
        .save-btn {
            width: 100%; padding: 14px; border: none; border-radius: 12px;
            background: linear-gradient(90deg, #5a4efc, #6b5dfc);
            color: #fff; font-weight: 700; font-size: 15px; cursor: pointer;
        }
        .alert-error { background: #fee2e2; color: #b91c1c; padding: 12px 14px; border-radius: 10px; margin-bottom: 16px; font-weight: 600; }
        .alert-success { background: #dcfce7; color: #15803d; padding: 12px 14px; border-radius: 10px; margin-bottom: 16px; font-weight: 600; }
        .avatar-circle {
            width: 72px; height: 72px; border-radius: 50%;
            background: linear-gradient(135deg, #5a4efc, #6b5dfc);
            color: #fff; font-size: 28px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 20px;
        }
        .hint { font-size: 13px; color: #9ca3af; margin-top: 4px; }
    </style>
</head>
<body>
<div class="admin-page" id="adminPage">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar-brand">MiniPress</div>
        <nav class="admin-nav">
            <a href="dashboard.php">📰 Posts</a>
            <a href="profile.php" class="active">👤 My Profile</a>
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
            <div class="page-heading">
                <h1>My Profile</h1>
                <p>Update your account details</p>
            </div>

            <div class="content-card profile-card" style="margin-top: 22px;">
                <div class="avatar-circle"><?php echo strtoupper($username[0]); ?></div>

                <?php if ($error): ?><div class="alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
                <?php if ($success): ?><div class="alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="password" placeholder="Leave blank to keep current">
                        <p class="hint">Minimum 6 characters</p>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" placeholder="Repeat new password">
                    </div>
                    <button type="submit" class="save-btn">Save Changes</button>
                </form>
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
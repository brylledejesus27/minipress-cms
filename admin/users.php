<?php
require_once 'includes/auth.php';
require_once '../config/database.php';

$adminName = $_SESSION['admin_username'] ?? 'Admin';
$error = "";
$success = "";

function uploadProfileImage() {
    if (empty($_FILES['profile_image']['name'])) {
        return null;
    }

    $uploadDir = "../uploads/profile/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = basename($_FILES["profile_image"]["name"]);
    $fileType = $_FILES["profile_image"]["type"];
    $fileTmp = $_FILES["profile_image"]["tmp_name"];

    $allowedTypes = ["image/jpeg", "image/png", "image/gif"];

    if (!in_array($fileType, $allowedTypes)) {
        return "INVALID_TYPE";
    }

    $newFileName = time() . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "_", $fileName);
    $targetFile = $uploadDir . $newFileName;

    if (move_uploaded_file($fileTmp, $targetFile)) {
        return "uploads/profile/" . $newFileName;
    }

    return null;
}

if (isset($_POST['add'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = "Username and password are required.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $checkResult = $check->get_result();

        if ($checkResult && $checkResult->num_rows > 0) {
            $error = "Username already exists.";
        } else {
            $profileImage = uploadProfileImage();

            if ($profileImage === "INVALID_TYPE") {
                $error = "Only JPG, PNG, and GIF images are allowed.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $conn->prepare("INSERT INTO users (username, password, profile_image) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $username, $hashedPassword, $profileImage);

                if ($stmt->execute()) {
                    $success = "User added successfully.";
                } else {
                    $error = "Failed to add user.";
                }

                $stmt->close();
            }
        }

        $check->close();
    }
}

if (isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '') {
        $error = "Username is required.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $check->bind_param("si", $username, $id);
        $check->execute();
        $checkResult = $check->get_result();

        if ($checkResult && $checkResult->num_rows > 0) {
            $error = "Username already exists.";
        } else {
            $profileImage = uploadProfileImage();

            if ($profileImage === "INVALID_TYPE") {
                $error = "Only JPG, PNG, and GIF images are allowed.";
            } else {
                if ($password !== '' && $profileImage !== null) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                    $stmt = $conn->prepare("UPDATE users SET username = ?, password = ?, profile_image = ? WHERE id = ?");
                    $stmt->bind_param("sssi", $username, $hashedPassword, $profileImage, $id);

                } elseif ($password !== '') {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                    $stmt = $conn->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
                    $stmt->bind_param("ssi", $username, $hashedPassword, $id);

                } elseif ($profileImage !== null) {
                    $stmt = $conn->prepare("UPDATE users SET username = ?, profile_image = ? WHERE id = ?");
                    $stmt->bind_param("ssi", $username, $profileImage, $id);

                } else {
                    $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
                    $stmt->bind_param("si", $username, $id);
                }

                if ($stmt->execute()) {
                    if ($id === (int)$_SESSION['admin_id']) {
                        $_SESSION['admin_username'] = $username;
                    }

                    header("Location: users.php");
                    exit();
                } else {
                    $error = "Failed to update user.";
                }

                $stmt->close();
            }
        }

        $check->close();
    }
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    if ($id === (int)$_SESSION['admin_id']) {
        $error = "You cannot delete your own account.";
    } else {
        $stmt = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $imageResult = $stmt->get_result();

        if ($imageResult && $imageResult->num_rows > 0) {
            $imageRow = $imageResult->fetch_assoc();

            if (!empty($imageRow['profile_image'])) {
                $imagePath = "../" . $imageRow['profile_image'];

                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
        }

        $stmt->close();

        $deleteStmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $deleteStmt->bind_param("i", $id);
        $deleteStmt->execute();
        $deleteStmt->close();

        header("Location: users.php");
        exit();
    }
}

$editUser = null;

if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editId = (int)$_GET['edit'];

    $stmt = $conn->prepare("SELECT id, username, profile_image FROM users WHERE id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editResult = $stmt->get_result();

    if ($editResult && $editResult->num_rows > 0) {
        $editUser = $editResult->fetch_assoc();
    }

    $stmt->close();
}

$users = $conn->query("SELECT id, username, profile_image, created_at FROM users ORDER BY id DESC");

$totalUsers = 0;
$countQuery = $conn->query("SELECT COUNT(*) AS total FROM users");

if ($countQuery && $row = $countQuery->fetch_assoc()) {
    $totalUsers = (int)$row['total'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Users | MiniPress CMS</title>
<link rel="stylesheet" href="../assets/css/admin.css?v=108">

<style>
.users-form .form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 14px;
}

.users-form input {
    width: 100%;
    padding: 14px 16px;
    border-radius: 14px;
    border: 1px solid #e5e7eb;
    background: #f9fafb;
    font-size: 14px;
    outline: none;
}

.users-form input:focus {
    background: #fff;
    border-color: #6b5dfc;
    box-shadow: 0 0 0 3px rgba(107,93,252,.15);
}

.alert-success {
    background: #dcfce7;
    color: #166534;
    padding: 12px 14px;
    border-radius: 12px;
    margin-bottom: 16px;
    font-weight: 600;
}

.alert-error {
    background: #fee2e2;
    color: #b91c1c;
    padding: 12px 14px;
    border-radius: 12px;
    margin-bottom: 16px;
    font-weight: 600;
}

.cancel-btn {
    display: inline-block;
    margin-left: 8px;
    padding: 12px 18px;
    border-radius: 12px;
    background: #e5e7eb;
    color: #374151;
    text-decoration: none;
    font-weight: 700;
}

.profile-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e5e7eb;
}

.profile-placeholder {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #eef2ff;
    color: #4f46e5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}

.edit-preview {
    margin-bottom: 15px;
}

@media (max-width: 900px) {
    .users-form .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>
</head>

<body>
<div class="admin-page">

    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar-brand">MiniPress</div>

        <nav class="admin-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="posts.php">Posts</a>
            <a href="categories.php">Categories</a>
            <a href="pages.php">Pages</a>
            <a href="media.php">Media</a>
            <a href="users.php" class="active">Users</a>
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
                    <h1>Users</h1>
                    <p>Manage admin accounts</p>
                </div>
                <span class="add-post-btn">Total Users: <?php echo $totalUsers; ?></span>
            </div>

            <?php if ($success !== ""): ?>
                <div class="alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if ($error !== ""): ?>
                <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="content-card users-form" style="margin-bottom:20px;">
                <?php if ($editUser): ?>
                    <h3>Edit User</h3>

                    <div class="edit-preview">
                        <?php if (!empty($editUser['profile_image'])): ?>
                            <img src="../<?php echo htmlspecialchars($editUser['profile_image']); ?>" class="profile-avatar">
                        <?php else: ?>
                            <div class="profile-placeholder">👤</div>
                        <?php endif; ?>
                    </div>

                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?php echo $editUser['id']; ?>">

                        <div class="form-grid">
                            <input 
                                type="text" 
                                name="username" 
                                value="<?php echo htmlspecialchars($editUser['username']); ?>" 
                                required
                            >

                            <input 
                                type="password" 
                                name="password" 
                                placeholder="New password optional"
                            >

                            <input 
                                type="file" 
                                name="profile_image"
                                accept="image/*"
                            >
                        </div>

                        <br>

                        <button class="add-post-btn" name="update">Update User</button>
                        <a href="users.php" class="cancel-btn">Cancel</a>
                    </form>
                <?php else: ?>
                    <h3>Add New User</h3>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-grid">
                            <input type="text" name="username" placeholder="Username" required>

                            <input type="password" name="password" placeholder="Password" required>

                            <input type="file" name="profile_image" accept="image/*">
                        </div>

                        <br>

                        <button class="add-post-btn" name="add">+ Add New User</button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="content-card">
                <table class="content-table">
                    <thead>
                        <tr>
                            <th>PROFILE</th>
                            <th>USERNAME</th>
                            <th>DATE CREATED</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if ($users && $users->num_rows > 0): ?>
                        <?php while ($row = $users->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($row['profile_image'])): ?>
                                        <img src="../<?php echo htmlspecialchars($row['profile_image']); ?>" class="profile-avatar">
                                    <?php else: ?>
                                        <div class="profile-placeholder">👤</div>
                                    <?php endif; ?>
                                </td>

                                <td><?php echo htmlspecialchars($row['username']); ?></td>

                                <td>
                                    <?php echo date("M d, Y", strtotime($row['created_at'])); ?>
                                </td>

                                <td class="action-cell">
                                    <a href="users.php?edit=<?php echo $row['id']; ?>" class="icon-btn">✎</a>

                                    <?php if ((int)$row['id'] !== (int)$_SESSION['admin_id']): ?>
                                        <a href="users.php?delete=<?php echo $row['id']; ?>"
                                           class="icon-btn delete-btn"
                                           onclick="return confirm('Delete this user?');">
                                           🗑
                                        </a>
                                    <?php else: ?>
                                        <span style="color:#6b7280;font-size:13px;">Current User</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No users found.</td>
                        </tr>
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
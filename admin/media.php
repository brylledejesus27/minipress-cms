<?php
require_once 'includes/auth.php';
require_once '../config/database.php';

$adminName = $_SESSION['admin_username'] ?? 'Admin';
$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["media_file"])) {
    $uploadDir = "../uploads/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = basename($_FILES["media_file"]["name"]);
    $fileTmp = $_FILES["media_file"]["tmp_name"];
    $fileType = $_FILES["media_file"]["type"];
    $fileSize = $_FILES["media_file"]["size"];

    $allowedTypes = ["image/jpeg", "image/png", "image/gif", "application/pdf"];

    if (!in_array($fileType, $allowedTypes)) {
        $error = "Only JPG, PNG, GIF, and PDF files are allowed.";
    } elseif ($fileSize > 5000000) {
        $error = "File is too large. Maximum size is 5MB.";
    } else {
        $newFileName = time() . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "_", $fileName);
        $filePath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmp, $filePath)) {
            $dbPath = "uploads/" . $newFileName;

            $stmt = $conn->prepare("INSERT INTO media (file_name, file_path, file_type) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $fileName, $dbPath, $fileType);

            if ($stmt->execute()) {
                $success = "Media uploaded successfully.";
            } else {
                $error = "Failed to save media to database.";
            }

            $stmt->close();
        } else {
            $error = "Failed to upload file.";
        }
    }
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $stmt = $conn->prepare("SELECT file_path FROM media WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $fileResult = $stmt->get_result();

    if ($fileResult && $fileResult->num_rows > 0) {
        $file = $fileResult->fetch_assoc();
        $realPath = "../" . $file['file_path'];

        if (file_exists($realPath)) {
            unlink($realPath);
        }

        $deleteStmt = $conn->prepare("DELETE FROM media WHERE id = ?");
        $deleteStmt->bind_param("i", $id);
        $deleteStmt->execute();
        $deleteStmt->close();
    }

    $stmt->close();

    header("Location: media.php");
    exit();
}

$media = $conn->query("SELECT * FROM media ORDER BY uploaded_at DESC");
$totalMedia = 0;

$countQuery = $conn->query("SELECT COUNT(*) AS total FROM media");
if ($countQuery && $row = $countQuery->fetch_assoc()) {
    $totalMedia = (int)$row['total'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Media Library | MiniPress CMS</title>
<link rel="stylesheet" href="../assets/css/admin.css?v=104">

<style>
.media-upload-card {
    margin-bottom: 22px;
}

.media-upload-form {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
}

.media-upload-form input[type="file"] {
    flex: 1;
    min-width: 240px;
    padding: 13px;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    background: #f9fafb;
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

.media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
    gap: 18px;
}

.media-card {
    background: #ffffff;
    border: 1px solid #eef0f4;
    border-radius: 18px;
    padding: 14px;
    box-shadow: 0 4px 14px rgba(16, 24, 40, 0.06);
}

.media-preview {
    width: 100%;
    height: 140px;
    border-radius: 14px;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.media-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.file-icon {
    font-size: 48px;
}

.media-name {
    margin-top: 12px;
    font-size: 14px;
    font-weight: 700;
    color: #111827;
    word-break: break-word;
}

.media-date {
    font-size: 12px;
    color: #6b7280;
    margin-top: 4px;
}

.media-actions {
    display: flex;
    gap: 8px;
    margin-top: 12px;
}

.media-actions a {
    flex: 1;
    text-align: center;
    text-decoration: none;
}

.view-btn {
    background: #eef2ff;
    color: #4f46e5;
    padding: 9px 10px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
}

.delete-media-btn {
    background: #fee2e2;
    color: #b91c1c;
    padding: 9px 10px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
}

.empty-media {
    text-align: center;
    padding: 40px;
    color: #6b7280;
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
            <a href="media.php" class="active">Media</a>
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
                    <h1>Media Library</h1>
                    <p>Upload and manage your images and files</p>
                </div>
                <span class="add-post-btn">Total Media: <?php echo $totalMedia; ?></span>
            </div>

            <?php if ($success !== ""): ?>
                <div class="alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if ($error !== ""): ?>
                <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="content-card media-upload-card">
                <form method="POST" enctype="multipart/form-data" class="media-upload-form">
                    <input type="file" name="media_file" required>
                    <button type="submit" class="add-post-btn">⇪ Upload Media</button>
                </form>
            </div>

            <div class="media-grid">
                <?php if ($media && $media->num_rows > 0): ?>
                    <?php while ($row = $media->fetch_assoc()): ?>
                        <div class="media-card">
                            <div class="media-preview">
                                <?php if (str_starts_with($row['file_type'], 'image/')): ?>
                                    <img src="../<?php echo htmlspecialchars($row['file_path']); ?>" alt="Media">
                                <?php else: ?>
                                    <div class="file-icon">📄</div>
                                <?php endif; ?>
                            </div>

                            <div class="media-name">
                                <?php echo htmlspecialchars($row['file_name']); ?>
                            </div>

                            <div class="media-date">
                                Uploaded: <?php echo date("M d, Y", strtotime($row['uploaded_at'])); ?>
                            </div>

                            <div class="media-actions">
                                <a class="view-btn" href="../<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank">View</a>
                                <a class="delete-media-btn"
                                   href="media.php?delete=<?php echo $row['id']; ?>"
                                   onclick="return confirm('Delete this media file?');">
                                   Delete
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="content-card empty-media">
                        No media uploaded yet.
                    </div>
                <?php endif; ?>
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
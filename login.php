<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: admin/dashboard.php"); exit();
    } else {
        header("Location: user/dashboard.php"); exit();
    }
}

require_once 'config/database.php';

$error = "";
$role  = $_POST['role'] ?? $_GET['role'] ?? 'user';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = $_POST['role'] ?? 'user';

    if ($username === '' || $password === '') {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND role = ?");
        $stmt->bind_param("ss", $username, $role);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id']       = $user['id'];
                $_SESSION['user_role']     = $user['role'];
                $_SESSION['user_username'] = $user['username'];

                // Keep admin session keys working too
                if ($user['role'] === 'admin') {
                    $_SESSION['admin_id']       = $user['id'];
                    $_SESSION['admin_username'] = $user['username'];
                    header("Location: admin/dashboard.php"); exit();
                } else {
                    header("Location: user/dashboard.php"); exit();
                }
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Invalid username or password.";
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
    <title>Login | MiniPress</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background: #f5f7fb; }

        .login-wrap { width: 100%; max-width: 440px; padding: 24px; }

        .login-brand {
            font-size: 32px; font-weight: 700; color: #111827;
            text-align: center; margin-bottom: 6px;
        }

        .login-sub { text-align: center; color: #667085; font-size: 15px; margin-bottom: 28px; }

        .role-tabs {
            display: flex; background: #f3f4f6;
            border-radius: 14px; padding: 5px;
            margin-bottom: 24px; gap: 4px;
        }

        .role-tab {
            flex: 1; text-align: center; padding: 12px;
            border-radius: 10px; cursor: pointer;
            font-weight: 700; font-size: 15px;
            border: none; background: transparent;
            color: #667085; transition: 0.2s;
        }

        .role-tab.active {
            background: #fff;
            color: #111827;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .login-card {
            background: #fff; border-radius: 20px;
            padding: 32px; box-shadow: 0 4px 24px rgba(16,24,40,0.08);
        }

        .form-group { margin-bottom: 18px; }

        .form-group label {
            display: block; font-weight: 700;
            font-size: 14px; margin-bottom: 8px; color: #374151;
        }

        .form-group input {
            width: 100%; padding: 14px 16px;
            border: 1px solid #e5e7eb; border-radius: 12px;
            font-size: 15px; outline: none; background: #f9fafb;
            transition: all 0.2s;
        }

        .form-group input:focus {
            border-color: #6b5dfc; background: #fff;
            box-shadow: 0 0 0 3px rgba(107,93,252,0.15);
        }

        .login-btn {
            width: 100%; padding: 15px;
            background: linear-gradient(90deg, #5a4efc, #6b5dfc);
            color: #fff; font-weight: 700; font-size: 16px;
            border: none; border-radius: 12px; cursor: pointer;
            margin-top: 4px; transition: opacity 0.2s;
        }

        .login-btn:hover { opacity: 0.9; }

        .alert-error {
            background: #fee2e2; color: #b91c1c;
            padding: 12px 14px; border-radius: 10px;
            margin-bottom: 18px; font-size: 14px; font-weight: 600;
        }

        .register-link {
            text-align: center; margin-top: 18px;
            font-size: 14px; color: #667085;
        }

        .register-link a { color: #5a4efc; font-weight: 700; }

        .role-indicator {
            text-align: center; margin-bottom: 18px;
            font-size: 13px; color: #667085;
        }
    </style>
</head>
<body>
<div class="login-wrap">
    <div class="login-brand">MiniPress</div>
    <p class="login-sub">Sign in to your account</p>

    <!-- Role Tabs -->
    <div class="role-tabs">
        <button class="role-tab <?php echo $role === 'user' ? 'active' : ''; ?>"
                onclick="setRole('user')" type="button">👤 User</button>
        <button class="role-tab <?php echo $role === 'admin' ? 'active' : ''; ?>"
                onclick="setRole('admin')" type="button">🔐 Admin</button>
    </div>

    <div class="login-card">
        <?php if ($error): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="role" id="roleInput" value="<?php echo htmlspecialchars($role); ?>">

            <p class="role-indicator" id="roleHint">
                <?php echo $role === 'admin' ? '🔐 Logging in as Administrator' : '👤 Logging in as User'; ?>
            </p>

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Enter your username" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="login-btn">Sign In</button>
        </form>

        <div class="register-link" id="registerLink" <?php echo $role === 'admin' ? 'style="display:none"' : ''; ?>>
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>
</div>

<script>
function setRole(role) {
    document.getElementById('roleInput').value = role;
    document.getElementById('roleHint').textContent =
        role === 'admin' ? '🔐 Logging in as Administrator' : '👤 Logging in as User';

    document.querySelectorAll('.role-tab').forEach(t => t.classList.remove('active'));
    event.target.classList.add('active');

    document.getElementById('registerLink').style.display = role === 'admin' ? 'none' : '';
}
</script>
</body>
</html>
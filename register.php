<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: user/dashboard.php"); exit();
}

require_once 'config/database.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm_password'] ?? '');

    if ($username === '' || $password === '' || $confirm === '') {
        $error = "All fields are required.";
    } elseif (strlen($username) < 3) {
        $error = "Username must be at least 3 characters.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $checkResult = $check->get_result();

        if ($checkResult && $checkResult->num_rows > 0) {
            $error = "Username already taken.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
            $stmt->bind_param("ss", $username, $hashed);

            if ($stmt->execute()) {
                $success = "Account created! You can now login.";
            } else {
                $error = "Registration failed. Please try again.";
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
    <title>Register | MiniPress</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background: #f5f7fb; }
        .login-wrap { width: 100%; max-width: 440px; padding: 24px; }
        .login-brand { font-size: 32px; font-weight: 700; color: #111827; text-align: center; margin-bottom: 6px; }
        .login-sub { text-align: center; color: #667085; font-size: 15px; margin-bottom: 28px; }
        .login-card { background: #fff; border-radius: 20px; padding: 32px; box-shadow: 0 4px 24px rgba(16,24,40,0.08); }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-weight: 700; font-size: 14px; margin-bottom: 8px; color: #374151; }
        .form-group input {
            width: 100%; padding: 14px 16px; border: 1px solid #e5e7eb;
            border-radius: 12px; font-size: 15px; outline: none; background: #f9fafb; transition: all 0.2s;
        }
        .form-group input:focus { border-color: #6b5dfc; background: #fff; box-shadow: 0 0 0 3px rgba(107,93,252,0.15); }
        .login-btn {
            width: 100%; padding: 15px;
            background: linear-gradient(90deg, #5a4efc, #6b5dfc);
            color: #fff; font-weight: 700; font-size: 16px;
            border: none; border-radius: 12px; cursor: pointer; margin-top: 4px;
        }
        .login-btn:hover { opacity: 0.9; }
        .alert-error { background: #fee2e2; color: #b91c1c; padding: 12px 14px; border-radius: 10px; margin-bottom: 18px; font-size: 14px; font-weight: 600; }
        .alert-success { background: #dcfce7; color: #15803d; padding: 12px 14px; border-radius: 10px; margin-bottom: 18px; font-size: 14px; font-weight: 600; }
        .register-link { text-align: center; margin-top: 18px; font-size: 14px; color: #667085; }
        .register-link a { color: #5a4efc; font-weight: 700; }
    </style>
</head>
<body>
<div class="login-wrap">
    <div class="login-brand">MiniPress</div>
    <p class="login-sub">Create your account</p>

    <div class="login-card">
        <?php if ($error): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Choose a username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="At least 6 characters" required>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" placeholder="Repeat your password" required>
            </div>
            <button type="submit" class="login-btn">Create Account</button>
        </form>

        <div class="register-link">
            Already have an account? <a href="login.php">Sign in here</a>
        </div>
    </div>
</div>
</body>
</html>
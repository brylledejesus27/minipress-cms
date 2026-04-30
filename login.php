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
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #07142b;
            overflow: hidden;
            position: relative;
        }

        /* ── Animated background canvas ── */
        #bgCanvas {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            z-index: 0;
        }

        /* ── Floating orbs ── */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.18;
            animation: floatOrb 10s ease-in-out infinite;
            z-index: 0;
        }

        .orb-1 {
            width: 500px; height: 500px;
            background: #5a4efc;
            top: -100px; left: -100px;
            animation-delay: 0s;
        }

        .orb-2 {
            width: 400px; height: 400px;
            background: #6b5dfc;
            bottom: -80px; right: -80px;
            animation-delay: 3s;
        }

        .orb-3 {
            width: 300px; height: 300px;
            background: #3b82f6;
            top: 50%; left: 50%;
            animation-delay: 6s;
        }

        @keyframes floatOrb {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33%       { transform: translate(30px, -30px) scale(1.05); }
            66%       { transform: translate(-20px, 20px) scale(0.95); }
        }

        /* ── Grid overlay ── */
        .grid-overlay {
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            z-index: 0;
        }

        /* ── Login card ── */
        .login-wrap {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 460px;
            padding: 24px;
        }

        .login-brand {
            text-align: center;
            margin-bottom: 8px;
        }

        .login-brand-text {
            font-size: 36px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -1px;
        }

        .login-brand-dot {
            color: #5a4efc;
        }

        .login-sub {
            text-align: center;
            color: #8899bb;
            font-size: 15px;
            margin-bottom: 28px;
        }

        /* ── Role tabs ── */
        .role-tabs {
            display: flex;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 14px;
            padding: 5px;
            margin-bottom: 24px;
            gap: 4px;
        }

        .role-tab {
            flex: 1;
            text-align: center;
            padding: 12px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 700;
            font-size: 15px;
            border: none;
            background: transparent;
            color: #667085;
            transition: all 0.25s;
        }

        .role-tab.active {
            background: linear-gradient(135deg, #5a4efc, #6b5dfc);
            color: #fff;
            box-shadow: 0 4px 15px rgba(90, 78, 252, 0.4);
        }

        .role-tab:hover:not(.active) {
            background: rgba(255,255,255,0.07);
            color: #fff;
        }

        /* ── Card ── */
        .login-card {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 36px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.4);
        }

        .form-group { margin-bottom: 20px; }

        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 8px;
            color: #99aabb;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            font-size: 15px;
            outline: none;
            background: rgba(255,255,255,0.06);
            color: #ffffff;
            transition: all 0.2s;
        }

        .form-group input::placeholder { color: #556077; }

        .form-group input:focus {
            border-color: #5a4efc;
            background: rgba(90, 78, 252, 0.08);
            box-shadow: 0 0 0 3px rgba(90, 78, 252, 0.2);
        }

        .role-indicator {
            text-align: center;
            margin-bottom: 20px;
            font-size: 13px;
            color: #667085;
        }

        .login-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #5a4efc, #6b5dfc);
            color: #fff;
            font-weight: 700;
            font-size: 16px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            margin-top: 4px;
            transition: all 0.2s;
            box-shadow: 0 4px 20px rgba(90, 78, 252, 0.4);
            letter-spacing: 0.3px;
        }

        .login-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 25px rgba(90, 78, 252, 0.5);
        }

        .login-btn:active { transform: translateY(0); }

        .alert-error {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #667085;
        }

        .register-link a {
            color: #7c6ffc;
            font-weight: 700;
            text-decoration: none;
            transition: color 0.2s;
        }

        .register-link a:hover { color: #a89dff; }

        /* ── Floating particles ── */
        .particles {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            width: 2px;
            height: 2px;
            background: rgba(90, 78, 252, 0.6);
            border-radius: 50%;
            animation: floatParticle linear infinite;
        }

        @keyframes floatParticle {
            0%   { transform: translateY(100vh) scale(0); opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 1; }
            100% { transform: translateY(-10vh) scale(1); opacity: 0; }
        }
    </style>
</head>
<body>

<!-- Animated background -->
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>
<div class="grid-overlay"></div>

<!-- Particles -->
<div class="particles" id="particles"></div>

<div class="login-wrap">
    <div class="login-brand">
        <span class="login-brand-text">Mini<span class="login-brand-dot">Press</span></span>
    </div>
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
            <div class="alert-error">⚠️ <?php echo htmlspecialchars($error); ?></div>
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

            <button type="submit" class="login-btn">Sign In →</button>
        </form>

        <div class="register-link" id="registerLink" <?php echo $role === 'admin' ? 'style="display:none"' : ''; ?>>
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>
</div>

<script>
    // Role switcher
    function setRole(role) {
        document.getElementById('roleInput').value = role;
        document.getElementById('roleHint').textContent =
            role === 'admin' ? '🔐 Logging in as Administrator' : '👤 Logging in as User';
        document.querySelectorAll('.role-tab').forEach(t => t.classList.remove('active'));
        event.target.classList.add('active');
        document.getElementById('registerLink').style.display = role === 'admin' ? 'none' : '';
    }

    // Generate floating particles
    const container = document.getElementById('particles');
    for (let i = 0; i < 40; i++) {
        const p = document.createElement('div');
        p.classList.add('particle');
        p.style.left = Math.random() * 100 + 'vw';
        p.style.width = (Math.random() * 3 + 1) + 'px';
        p.style.height = p.style.width;
        p.style.animationDuration = (Math.random() * 15 + 8) + 's';
        p.style.animationDelay = (Math.random() * 10) + 's';
        p.style.opacity = Math.random() * 0.6 + 0.2;
        container.appendChild(p);
    }
</script>
</body>
</html>
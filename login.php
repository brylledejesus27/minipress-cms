<?php
session_start();
require_once 'config/database.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: admin/dashboard.php");
    exit();
}

$error = "";
$username = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === "" || $password === "") {
        $error = "Please enter both username and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];

                header("Location: admin/dashboard.php");
                exit();
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
    <title>Login | MiniPress CMS</title>
    <link rel="stylesheet" href="assets/css/style.css?v=101">
</head>
<body class="login-page">
    <div class="login-shell">
        <div class="login-card">
            <section class="login-left-panel">
                <div class="login-left-overlay"></div>
                <div class="login-left-content">
                    <div class="login-brand-icon">M</div>
                    <h1>MiniPress CMS</h1>
                    <p class="login-brand-sub">Manage your content with simplicity</p>

                    <div class="login-feature-list">
                        <div class="login-feature-item">
                            <div class="login-feature-box">✓</div>
                            <div>
                                <h3>Secure &amp; Fast</h3>
                                <p>Your data is protected.</p>
                            </div>
                        </div>

                        <div class="login-feature-item">
                            <div class="login-feature-box">✓</div>
                            <div>
                                <h3>Easy to Use</h3>
                                <p>Clean and intuitive interface.</p>
                            </div>
                        </div>

                        <div class="login-feature-item">
                            <div class="login-feature-box">✓</div>
                            <div>
                                <h3>Powerful Features</h3>
                                <p>Everything you need to manage content.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="login-right-panel">
                <div class="login-form-wrap">
                    <div class="login-welcome">WELCOME BACK!</div>
                    <h2>Sign in to your account</h2>
                    <p class="login-desc">Enter your credentials to access the dashboard.</p>

                    <?php if ($error !== ""): ?>
                        <div class="login-error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="login-form-group">
                            <label for="username">Username</label>
                            <input
                                id="username"
                                type="text"
                                name="username"
                                placeholder="Enter username"
                                value="<?php echo htmlspecialchars($username); ?>"
                                required
                            >
                        </div>

                        <div class="login-form-group">
                            <div class="password-row">
                                <label for="password">Password</label>
                                <a href="#" class="forgot-password">Forgot Password?</a>
                            </div>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                placeholder="Enter password"
                                required
                            >
                        </div>

                        <div class="remember-row">
                            <label>
                                <input type="checkbox" disabled>
                                <span>Remember Me</span>
                            </label>
                        </div>

                        <button type="submit" class="login-submit-btn">Login</button>

                        <div class="login-divider"><span>or</span></div>

                        <div class="secure-login-note">Secure Login</div>
                    </form>
                </div>
            </section>
        </div>
    </div>
</body>
</html>
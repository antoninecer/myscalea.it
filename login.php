<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'inc/connect.php';

// Logout
if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    $_SESSION['logout_success'] = "✅ You have been logged out.";
    echo "<script>window.location.href = '/'</script>";
    exit;
}

// Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    $stmt = $pdo->prepare('SELECT u.id, u.username, u.email, u.password_hash, u.is_verified, r.name AS role
                           FROM users u
                           LEFT JOIN user_roles r ON u.user_role_id = r.id
                           WHERE u.username = ?');
    $stmt->execute([$_POST['username']]);
    $user = $stmt->fetch();

    if ($user && password_verify($_POST['password'], $user['password_hash'])) {
        if (!$user['is_verified']) {
            $error = "⚠️ Your account is not verified. Please check your email.";
        } else {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ];
            echo "<script>window.location.href = '/'</script>";
            exit;
        }
    } else {
        $error = "❌ Invalid login credentials.";
    }
}

// Output login button or user info
if (!isset($_SESSION['user'])) {
    // Success message after logout
    if (isset($_SESSION['logout_success'])) {
        echo "<p style='color:green;'>" . $_SESSION['logout_success'] . "</p>";
        unset($_SESSION['logout_success']);
    }

    echo '<button onclick="document.getElementById(\'loginModal\').style.display = \'block\'; setTimeout(function(){ document.getElementById(\'loginUsername\').focus(); }, 100);">Log In</button>';
    echo '<div id="loginModal" style="display:none; position:fixed; top:20%; left:50%; transform:translateX(-50%); background:#fff; border:1px solid #ccc; padding:20px; z-index:1000; box-shadow:0 0 10px rgba(0,0,0,0.5);">
            <form method="POST">
              <h3>Login</h3>';
    if (!empty($error)) {
        echo "<p style='color:red;'>$error</p>";
    }
    echo '      <input type="text" id="loginUsername" name="username" placeholder="Username" required><br><br>
              <input type="password" name="password" placeholder="Password" required><br><br>
              <button type="submit" name="login_submit">Log In</button>
              <button type="button" onclick="document.getElementById(\'loginModal\').style.display=\'none\'">Cancel</button>
            </form>
          </div>';
} else {
    echo '<div style="margin: 20px 0;">
            Logged in as <strong>' . htmlspecialchars($_SESSION['user']['username']) . '</strong>
            (<em>' . htmlspecialchars($_SESSION['user']['role']) . '</em>) –
            <a href="?logout=1">Log Out</a>
          </div>';
}


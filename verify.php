<?php
session_start();
require_once 'inc/connect.php';
?>

<?php include 'header.php'; ?>
<?php include 'menu.php'; ?>

<header class="main-header">
  <h1>Account Verification</h1>
</header>

<section class="section">
<?php
$token = $_GET['token'] ?? '';

if (!$token) {
    echo "<p style='color:red; font-weight:bold;'>❌ Missing verification token.</p>";
} else {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE verification_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        if ($user['is_verified']) {
            echo "<p style='color:green; font-weight:bold;'>✅ Your account is already verified.</p>";
        } else {
            $update = $pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
            $update->execute([$user['id']]);
            echo "<p style='color:green; font-weight:bold;'>✅ Your account is now verified. You can log in.</p>";
        }
    } else {
        echo "<p style='color:red; font-weight:bold;'>❌ Invalid or expired verification token.</p>";
    }
}
?>

<div style="margin-top: 20px; text-align: center;">
  <a href="/" class="cta-button" style="font-size: 1.1em; padding: 12px 24px;">🏠 Back to Homepage</a>
</div>
</section>

<?php include 'footer.php'; ?>


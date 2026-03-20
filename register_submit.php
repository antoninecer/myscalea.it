<?php
session_start();
require_once 'inc/connect.php';

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Kontrola vyplnění
if (!$username || !$email || !$password) {
    $_SESSION['register_error'] = "❌ Please fill in all fields.";
    echo "<script>window.location.href = '/';</script>";
    exit;
}

// Kontrola unikátnosti
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
$stmt->execute([$username, $email]);
if ($stmt->fetchColumn() > 0) {
    $_SESSION['register_error'] = "❌ Username or email already exists.";
    echo "<script>window.location.href = '/';</script>";
    exit;
}

// Vytvoření uživatele
$hash = password_hash($password, PASSWORD_DEFAULT);
$token = bin2hex(random_bytes(32));

$stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, user_role_id, is_verified, verification_token) VALUES (?, ?, ?, 3, 0, ?)");
$stmt->execute([$username, $email, $hash, $token]);

// Odeslání ověřovacího e-mailu
$link = "https://" . $_SERVER['HTTP_HOST'] . "/verify.php?token=" . urlencode($token);
$subject = "Confirm your account on MyScalea.it";
$body = "Hi $username,<br><br>Thank you for registering on <strong>MyScalea.it</strong>.<br>
Please confirm your account by clicking the link below:<br><br>
<a href=\"$link\">$link</a><br><br>
If you did not register, please ignore this email.";

if (sendEmail($email, $username, $subject, $body)) {
    $_SESSION['register_success'] = true;
} else {
    $_SESSION['register_error'] = "⚠️ Registration saved, but email could not be sent.";
}

// Přesměrování zpět s JS (aby zůstalo HTML funkční)
echo "<script>window.location.href = '/?register=success';</script>";
exit;


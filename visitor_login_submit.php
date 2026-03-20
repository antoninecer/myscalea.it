<?php
session_start();
require 'inc/connect.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    exit('Missing email or password.');
}

$stmt = $pdo->prepare("SELECT id, name, password_hash, verified, loyalty_status FROM visitors WHERE email = ?");
$stmt->execute([$email]);
$visitor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$visitor) {
    exit('No account found with this email.');
}

if (!$visitor['verified']) {
    exit('Your email address is not verified. Please check your inbox.');
}

if (!password_verify($password, $visitor['password_hash'])) {
    exit('Incorrect password.');
}

$_SESSION['visitor_id'] = $visitor['id'];
$_SESSION['visitor_name'] = $visitor['name'];
$_SESSION['visitor_loyalty'] = $visitor['loyalty_status'];

header('Location: index.php');
exit;


<?php
require 'inc/connect.php';

$token = $_GET['token'] ?? '';
if (!$token) {
    exit('Missing verification token.');
}

$stmt = $pdo->prepare("SELECT id FROM visitors WHERE verification_token = ? AND verified = 0");
$stmt->execute([$token]);
$visitor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$visitor) {
    exit('Invalid or already used token.');
}

$stmt = $pdo->prepare("UPDATE visitors SET verified = 1, verification_token = NULL WHERE id = ?");
$stmt->execute([$visitor['id']]);

echo "Your account has been successfully verified. You can now log in.";


<?php
require 'inc/connect.php';

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$password = $_POST['password'] ?? '';

if (!$name || !$email || !$password) {
    exit('Missing required fields.');
}

// Check if visitor already exists
$stmt = $pdo->prepare("SELECT id FROM visitors WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    exit('An account with this email already exists.');
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$token = bin2hex(random_bytes(32));

$stmt = $pdo->prepare("INSERT INTO visitors (name, email, phone, password_hash, verification_token, verified, visit_count, loyalty_status, last_visit)
                       VALUES (?, ?, ?, ?, ?, 0, 0, 'new', NULL)");
$stmt->execute([$name, $email, $phone, $hash, $token]);

$verifyUrl = "https://myscalea.it/visitor_verify.php?token=$token";
$subject = "Verify your visitor account";
$htmlBody = "<p>Hello <strong>$name</strong>,</p><p>Please confirm your registration by clicking the link below:</p><p><a href='$verifyUrl'>$verifyUrl</a></p><p>Thank you!</p>";

$sent = sendEmail($email, $name, $subject, $htmlBody);

if ($sent) {
    echo "Registration successful. Please check your email to verify your account.";
} else {
    echo "Failed to send verification email. Please try again later.";
}

<?php
session_start();
require_once 'inc/connect.php';
require_once 'client_request_validate.php';

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$requirementsJson = trim($_POST['requirements'] ?? '');

// Spustit validaci
$errors = validateClientRequest($name, $email, $phone, $password, $requirementsJson);

if (!empty($errors)) {
    foreach ($errors as $err) {
        echo "<p style='color:red;'>$err</p>";
    }
    exit;
}

// Kontrola unikátnosti e-mailu
$stmt = $pdo->prepare("SELECT COUNT(*) FROM clients WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetchColumn() > 0) {
    echo "<p style='color:red;'>❌ Klient s tímto e-mailem již existuje.</p>";
    exit;
}

// Hash hesla + token pro ověření
$hash = password_hash($password, PASSWORD_DEFAULT);
$token = bin2hex(random_bytes(32));

// Uložení klienta
$stmt = $pdo->prepare("INSERT INTO clients (name, email, password_hash, phone, requirements, verification_token) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([$name, $email, $hash, $phone, $requirementsJson, $token]);

// Šablona e-mailu
$templatePath = __DIR__ . '/client_new_mail.txt';
$template = file_get_contents($templatePath);

$requirementsPretty = json_encode(json_decode($requirementsJson), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
$verificationLink = "https://" . $_SERVER['HTTP_HOST'] . "/client_request_verify.php?token=" . urlencode($token);

$emailBody = str_replace(
    ['{{name}}', '{{requirements}}', '{{verification_link}}'],
    [$name, $requirementsPretty, $verificationLink],
    $template
);

// Odeslání e-mailu
$subject = "Potvrzení žádosti – MyScalea.it";
$sent = sendEmail($email, $name, $subject, nl2br($emailBody), $emailBody);

if ($sent) {
    echo "<p style='color:green;'>✅ Děkujeme! Ověřovací e-mail byl odeslán na $email.</p>";
} else {
    echo "<p style='color:red;'>⚠️ Nepodařilo se odeslat ověřovací e-mail.</p>";
}
echo "<p><a href='/'>🏠 Zpět na hlavní stránku</a></p>";


?>

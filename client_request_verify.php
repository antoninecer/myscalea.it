<?php
require_once 'inc/connect.php';

$token = $_GET['token'] ?? '';

if (!$token) {
    echo "<p style='color:red;'>❌ Chybí ověřovací token.</p>";
    exit;
}

// Najít klienta podle tokenu
$stmt = $pdo->prepare("SELECT * FROM clients WHERE verification_token = ?");
$stmt->execute([$token]);
$client = $stmt->fetch();

if ($client) {
    if ($client['verified']) {
        echo "<p style='color:green;'>✅ Požadavek již byl ověřen.</p>";
    } else {
        // Označit jako ověřeného
        $update = $pdo->prepare("UPDATE clients SET verified = 1, verification_token = NULL WHERE id = ?");
        $update->execute([$client['id']]);

        echo "<p style='color:green;'>✅ Děkujeme, váš požadavek byl ověřen.</p>";

        // Vytvořit záznam v tabulce users, pokud neexistuje
        $email = $client['email'];
        $passwordHash = $client['password_hash'];

        $checkUser = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $checkUser->execute([$email]);
        if ($checkUser->fetchColumn() == 0) {
            $insertUser = $pdo->prepare("
                INSERT INTO users (username, email, password_hash, user_role_id, is_verified)
                VALUES (?, ?, ?, 3, 1)
            ");
            $insertUser->execute([
                $email,         // username = email (opravdu!)
                $email,
                $passwordHash
            ]);
        }
    }
} else {
    echo "<p style='color:red;'>❌ Neplatný nebo expirovaný token.</p>";
}
?>

<p><a href="/">🏠 Zpět na hlavní stránku</a></p>


<?php
session_start();
require_once 'inc/connect.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM clients WHERE email = ? AND verified = 1");
$stmt->execute([$email]);
$client = $stmt->fetch();

if ($client && password_verify($password, $client['password_hash'])) {
    $_SESSION['client'] = [
        'id' => $client['id'],
        'name' => $client['name'],
        'email' => $client['email'],
    ];
    header("Location: moje-cesta.php");
    exit;
} else {
    echo "<p style='color:red;'>❌ Neplatný e-mail nebo heslo.</p>";
}
?>

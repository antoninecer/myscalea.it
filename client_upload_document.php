<?php
session_start();
require_once 'inc/connect.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'manager'])) {
    http_response_code(403);
    echo "Access denied.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['client_id']) && isset($_FILES['document'])) {
    $client_id = (int)$_POST['client_id'];
    $user_id = (int)$_SESSION['user']['id'];

    // Uložit soubor
    $emailStmt = $pdo->prepare("SELECT email FROM clients WHERE id = ? LIMIT 1");
    $emailStmt->execute([$client_id]);
    $client = $emailStmt->fetch(PDO::FETCH_ASSOC);

    if (!$client) {
        echo "Client not found.";
        exit;
    }

    $email_clean = preg_replace('/[^a-zA-Z]/', '', $client['email']);
    $upload_dir = __DIR__ . "/clients/" . $email_clean;
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $filename = basename($_FILES['document']['name']);
    $target_path = $upload_dir . '/' . $filename;
    if (move_uploaded_file($_FILES['document']['tmp_name'], $target_path)) {

        // Zapsat nový krok do client_lifecycle - krok 4 (Smlouva uzavřena)
        $stmt = $pdo->prepare("INSERT INTO client_lifecycle (client_id, step_id, changed_by) VALUES (?, 4, ?) ON DUPLICATE KEY UPDATE step_id = 4, changed_by = ?, changed_at = CURRENT_TIMESTAMP");
        $stmt->execute([$client_id, $user_id, $user_id]);

        echo "✅ Soubor uložen a stav klienta aktualizován.";
    } else {
        echo "❌ Nepodařilo se nahrát soubor.";
    }
} else {
    echo "❌ Chybějící parametry.";
}
?>

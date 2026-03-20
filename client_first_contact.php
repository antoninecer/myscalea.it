<?php
session_start();
require_once 'inc/connect.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'manager'])) {
    echo "<p style='color:red;'>❌ Access denied.</p>";
    exit;
}

$client_id = $_GET['client_id'] ?? null;
if (!$client_id) {
    echo "<p style='color:red;'>❌ Missing client ID.</p>";
    exit;
}

// Načíst klienta
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
$stmt->execute([$client_id]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$client) {
    echo "<p style='color:red;'>❌ Client not found.</p>";
    exit;
}

// Zpracování formuláře
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $purpose = $_POST['purpose'] ?? '';
    $rental = $_POST['rental'] ?? '';
    $rental_form = $_POST['rental_form'] ?? '';
    $bedrooms = $_POST['bedrooms'] ?? '';
    $bathrooms = $_POST['bathrooms'] ?? '';
    $balcony = $_POST['balcony'] ?? '';
    $occupants = $_POST['occupants'] ?? '';
    $parking = $_POST['parking'] ?? '';
    $floor_range = $_POST['floor_range'] ?? '';
    $elevator_from = $_POST['elevator_from'] ?? '';
    $distance_sea = $_POST['distance_sea'] ?? '';
    $distance_center = $_POST['distance_center'] ?? '';
    $min_area = $_POST['min_area'] ?? '';
    $financing = $_POST['financing'] ?? '';
    $advisor = $_POST['advisor'] ?? '';
    $price_note = $_POST['price_note'] ?? '';
    $viewing = $_POST['viewing'] ?? '';

    $note = "Účel: $purpose\n";
    $note .= "Pronájem: $rental\n";
    if ($rental === 'ano') {
        $note .= "Forma pronájmu: $rental_form\n";
    }
    $note .= "Ložnice: $bedrooms\n";
    $note .= "Koupelny: $bathrooms\n";
    $note .= "Balkon / terasa: $balcony\n";
    $note .= "Počet osob / dětí: $occupants\n";
    $note .= "Parkování: $parking\n";
    $note .= "Rozsah pater: $floor_range\n";
    $note .= "Výtah od patra: $elevator_from\n";
    $note .= "Vzdálenost od moře: $distance_sea\n";
    $note .= "Vzdálenost od centra: $distance_center\n";
    $note .= "Minimální plocha: $min_area m²\n";
    $note .= "Způsob financování: $financing\n";
    $note .= "Finanční poradce: $advisor\n";
    $note .= "Cenový strop: $price_note\n";
    $note .= "Forma prohlídek: $viewing";

    $stmt = $pdo->prepare("INSERT INTO client_logs (client_id, action, note) VALUES (?, 'První kontakt', ?)");
    $stmt->execute([$client_id, $note]);

    echo "<p style='color:green;'>✅ Uloženo. Klient byl kontaktován.</p>";
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>První kontakt – <?= htmlspecialchars($client['name']) ?></title>
    <style>
        body { font-family: sans-serif; max-width: 700px; margin: auto; padding: 20px; }
        label { font-weight: bold; display: block; margin-top: 10px; }
        input, select, textarea { width: 100%; padding: 5px; margin-top: 5px; }
    </style>
</head>
<body>
<h2>První kontakt s klientem</h2>
<p><strong>Jméno:</strong> <?= htmlspecialchars($client['name']) ?><br>
<strong>Email:</strong> <?= htmlspecialchars($client['email']) ?><br>
<strong>Telefon:</strong> <?= htmlspecialchars($client['phone']) ?></p>

<form method="POST">
    <label>Účel koupě:</label>
    <select name="purpose">
        <option value="bydlení">Bydlení</option>
        <option value="rekreace">Rekreace</option>
        <option value="investice">Investice</option>
        <option value="kombinace">Kombinace výše uvedených</option>
    </select>

    <label>Chce pronajímat?</label>
    <select name="rental">
        <option value="ne">Ne</option>
        <option value="ano">Ano</option>
    </select>

    <label>Forma pronájmu:</label>
    <select name="rental_form">
        <option value="klasická">Klasická přes portál</option>
        <option value="anonymní">Přes nás anonymně</option>
    </select>

    <label>Ložnice:</label>
    <select name="bedrooms">
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
        <option value="4">4</option>
    </select>

    <label>Koupelny:</label>
    <select name="bathrooms">
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
    </select>

    <label>Balkon / terasa:</label>
    <select name="balcony">
        <option value="0">0</option>
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
    </select>

    <label>Počet osob / dětí:</label>
    <input name="occupants" type="text">

    <label>Parkování:</label>
    <select name="parking">
        <option value="ne">Ne</option>
        <option value="ano">Ano</option>
    </select>

    <label>Rozsah pater (např. 0–3):</label>
    <input name="floor_range" type="text">

    <label>Od jakého patra požaduje výtah:</label>
    <input name="elevator_from" type="text">

    <label>Vzdálenost od moře:</label>
    <input name="distance_sea" type="text">

    <label>Vzdálenost od centra:</label>
    <input name="distance_center" type="text">

    <label>Minimální plocha apartmánu (m²):</label>
    <input name="min_area" type="number">
    <small>Poznámka: V roce 2025 se ceny v oblasti pohybují kolem 1000 EUR/m².</small>

    <label>Financování:</label>
    <select name="financing">
        <option value="vlastní">Vlastní</option>
        <option value="hypotéka">Hypotéka</option>
    </select>

    <label>Nabídnout poradce?</label>
    <select name="advisor">
        <option value="ne">Ne</option>
        <option value="ano">Ano</option>
    </select>

    <label>Upřesnění cenového stropu:</label>
    <textarea name="price_note"></textarea>
    <small>Poznámka: U bytu za cca 45 000 EUR bývá odměna RK + notář cca 6 000 EUR. Čím vyšší rozpočet, tím kvalitnější výběr.</small>

    <label>Forma prohlídek:</label>
    <select name="viewing">
        <option value="osobní">Osobní návštěva</option>
        <option value="dálková">Na dálku – náš technik</option>
    </select>

    <button type="submit" style="margin-top:15px;">Uložit první kontakt</button>
</form>

<p><a href="client_reports.php">← Zpět na seznam klientů</a></p>
</body>
</html>

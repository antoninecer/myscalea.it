<?php
session_start();
require_once 'inc/connect.php';

echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'agent' || !$_SESSION['user']['agency_id']) {
    exit("Access denied");
}

$agency_id = $_SESSION['user']['agency_id'];
$agent_id = $_SESSION['user']['id'];

// Získání názvu agentury
$stmt = $pdo->prepare("SELECT agency_name FROM agencies WHERE id = ?");
$stmt->execute([$agency_id]);
$agency = $stmt->fetch();
$safeAgencyName = preg_replace('/[^a-zA-Z0-9]/', '', $agency['agency_name']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);

    // Vlozime do tabulky ads
    $stmt1 = $pdo->prepare("INSERT INTO ads (agency_id, agent_id, title, description) VALUES (?, ?, ?, ?)");
    $stmt1->execute([$agency_id, $agent_id, $title, $description]);
    $ad_id = $pdo->lastInsertId();

    // Vlozime do ad_details
    $stmt2 = $pdo->prepare("INSERT INTO ad_details (ad_id, price) VALUES (?, ?)");
    $stmt2->execute([$ad_id, $price]);

    // Zaznamenáme historii ceny
    $stmt3 = $pdo->prepare("INSERT INTO ad_price_history (ad_id, price) VALUES (?, ?)");
    $stmt3->execute([$ad_id, $price]);

    // Najdeme ID galerie agentury v Piwigo
    $rootCat = $pdo->prepare("SELECT id FROM piwigo_categories WHERE name = ?");
    $rootCat->execute([$safeAgencyName]);
    $agencyGalleryId = $rootCat->fetchColumn();

    if ($agencyGalleryId) {
        $subName = "Ad_$ad_id";
        $uppercats = "$agencyGalleryId";

        // Vytvorime subkategorii pro inzerat
        $insertCat = $pdo->prepare("INSERT INTO piwigo_categories 
            (name, id_uppercat, comment, `rank`, status, site_id, visible, uppercats, commentable, global_rank) 
            VALUES (?, ?, '', 1, 'public', 1, 'true', ?, 'true', ?)");
        $insertCat->execute([$subName, $agencyGalleryId, $uppercats, $uppercats]);
    }

    header("Location: https://gallery.myscalea.it/");
    exit;
}
?>

<?php include 'header.php'; ?>
<?php include 'menu.php'; ?>

<header class="main-header">
  <h1>➕ Add New Listing</h1>
  <p>Submit a new property listing for your agency</p>
</header>

<section class="section">
  <form method="POST" style="max-width:600px;margin:auto;">
    <label>Title:</label><br>
    <input type="text" name="title" required style="width:100%;"><br><br>

    <label>Description:</label><br>
    <textarea name="description" rows="5" style="width:100%;" required></textarea><br><br>

    <label>Price (EUR):</label><br>
    <input type="number" name="price" step="0.01" required style="width:100%;"><br><br>

    <button type="submit" class="cta-button">💾 Save and go to gallery</button>
  </form>
</section>

<?php include 'footer.php'; ?>

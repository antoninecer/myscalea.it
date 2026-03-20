<?php
session_start();
require_once 'inc/connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'agent' || !$_SESSION['user']['agency_id']) {
    echo "<p style='color:red; text-align:center;'>Access denied. Redirecting...</p>";
    echo "<meta http-equiv='refresh' content='5;url=index.php'>";
    exit;
}

$agency_id = $_SESSION['user']['agency_id'];
$stmt = $pdo->prepare("SELECT * FROM agencies WHERE id = ?");
$stmt->execute([$agency_id]);
$agency = $stmt->fetch();

if (!$agency) {
    echo "<p style='color:red; text-align:center;'>Agency not found.</p>";
    exit;
}

$safeName = preg_replace('/[^a-zA-Z0-9]/', '', $agency['agency_name']);
$folder = "agencies/$safeName";
$signedPdf = "$folder/ramcova_smlouva_{$safeName}_signed.pdf";
$prefilledPdf = "$folder/ramcova_smlouva_{$safeName}_prefirmato.pdf";

// === GALERIE: automatická registrace agentury do Piwigo ===
$galleryLogin = strtolower($safeName);
$galleryEmail = $agency['representative_email'];
$galleryPassword = generatePiwigoPassword($agency['agency_name'], $agency['id']);

// Zkontroluj existenci Piwigo uživatele
$checkUser = $pdo->prepare("SELECT id FROM piwigo_users WHERE username = ?");
$checkUser->execute([$galleryLogin]);
$piwigoUserId = $checkUser->fetchColumn();

if (!$piwigoUserId) {
    $insertUser = $pdo->prepare("INSERT INTO piwigo_users (username, password, mail_address) VALUES (?, ?, ?)");
    $insertUser->execute([
        $galleryLogin,
        password_hash($galleryPassword, PASSWORD_DEFAULT),
        $galleryEmail
    ]);
    $piwigoUserId = $pdo->lastInsertId();
}

$checkInfo = $pdo->prepare("SELECT user_id FROM piwigo_user_infos WHERE user_id = ?");
$checkInfo->execute([$piwigoUserId]);
if (!$checkInfo->fetch()) {
    $insertInfo = $pdo->prepare("INSERT INTO piwigo_user_infos (
        user_id, nb_image_page, status, language, expand,
        show_nb_comments, show_nb_hits, recent_period, theme,
        registration_date, enabled_high, level,
        activation_key, activation_key_expire,
        last_visit, last_visit_from_history, lastmodified, preferences
    ) VALUES (
        ?, 15, 'normal', 'en_US', 'false',
        'false', 'false', 7, 'modus',
        NOW(), 'true', 0,
        NULL, NULL,
        NOW(), 'false', NOW(), NULL
    )");
    $insertInfo->execute([$piwigoUserId]);
}

// === Galerie kategorie ===
$checkCat = $pdo->prepare("SELECT id FROM piwigo_categories WHERE name = ?");
$checkCat->execute([$safeName]);
if (!$checkCat->fetch()) {
    $rootCat = $pdo->prepare("SELECT id FROM piwigo_categories WHERE name = 'Agencies' LIMIT 1");
    $rootCat->execute();
    $parent = $rootCat->fetchColumn();

    if ($parent) {
        $insertCat = $pdo->prepare("INSERT INTO piwigo_categories (name, id_uppercat, comment, `rank`, status, site_id, visible, uppercats, commentable, global_rank)
            VALUES (?, ?, '', 1, 'public', 1, 'true', '', 'true', '')");
        $insertCat->execute([$safeName, $parent]);
        $newId = $pdo->lastInsertId();

        $uppercats = "$parent,$newId";
        $global_rank = "1.$newId";

        $updateCat = $pdo->prepare("UPDATE piwigo_categories SET uppercats = ?, global_rank = ? WHERE id = ?");
        $updateCat->execute([$uppercats, $global_rank, $newId]);
    }
}

// === Upload podepsané smlouvy ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['signed_contract']) && is_uploaded_file($_FILES['signed_contract']['tmp_name'])) {
    if (!is_dir($folder)) mkdir($folder, 0777, true);
    move_uploaded_file($_FILES['signed_contract']['tmp_name'], $signedPdf);
    $update = $pdo->prepare("UPDATE agencies SET contract_signed = 1 WHERE id = ?");
    $update->execute([$agency_id]);
    header("Location: agency_page.php?uploaded=1");
    exit;
}
?>

<?php include 'header.php'; ?>
<?php include 'menu.php'; ?>

<header class="main-header">
  <h1>🏢 <?= htmlspecialchars($agency['agency_name']) ?></h1>
  <p>
    Welcome to your agency dashboard<br>
    <?php if (file_exists($signedPdf)): ?>
      <span style="color:green;">✅ Signed contract on file</span>
    <?php elseif (file_exists($prefilledPdf)): ?>
      <span style="color:orange;">⏳ Awaiting signed contract upload</span>
    <?php else: ?>
      <span style="color:red;">❌ No contract available</span>
    <?php endif; ?>
  </p>
</header>

<section class="section">
  <h2>📄 Cooperation Contract</h2>
  <?php if (isset($_GET['uploaded'])): ?>
    <p style="color:green;">✅ Contract uploaded successfully.</p>
  <?php endif; ?>

  <?php if (file_exists($signedPdf)): ?>
    <p>Your signed contract:</p>
    <a href="<?= $signedPdf ?>" target="_blank" class="cta-button">📄 View Signed Contract</a>
  <?php elseif (file_exists($prefilledPdf)): ?>
    <p>Download and sign the prefilled contract:</p>
    <a href="<?= $prefilledPdf ?>" target="_blank" class="cta-button">📄 Download Prefilled Contract</a>
    <form method="POST" enctype="multipart/form-data" style="margin-top:20px;">
      <label>Upload signed PDF:</label><br>
      <input type="file" name="signed_contract" accept="application/pdf" required><br><br>
      <button type="submit" class="cta-button">⬆️ Upload Signed Contract</button>
    </form>
  <?php else: ?>
    <p style="color:orange;">⚠️ Prefilled contract is not yet available.</p>
  <?php endif; ?>
</section>

<section class="section">
  <h2>🖼️ Photo Gallery</h2>
  <p>Your gallery in Piwigo has been initialized under the category "<strong><?= $safeName ?></strong>".</p>

  <form id="autoLogin" action="https://gallery.myscalea.it/identification.php" method="post" target="_blank">
    <input type="hidden" name="username" value="<?= htmlspecialchars($galleryLogin) ?>">
    <input type="hidden" name="password" value="<?= htmlspecialchars($galleryPassword) ?>">
    <input type="hidden" name="login" value="Login">
    <button type="submit" class="cta-button">🌐 Open Gallery</button>
  </form>
</section>

<section class="section">
  <h2>📰 Your POSTS</h2>
  <?php $_POST['user_id'] = $_SESSION['user']['id']; include 'blog_view.php'; ?>
</section>

<?php include 'footer.php'; ?>

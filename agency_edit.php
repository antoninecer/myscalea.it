i<?php
session_start();
require_once 'inc/connect.php';

// Přístup jen pro adminy
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['role'] !== 'manager')) {
    exit('Access denied.');
}

// Ověření existence ID agentury
$agency_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$agency_id) {
    exit('Missing agency ID.');
}

// Načtení údajů agentury
$stmt = $pdo->prepare("SELECT * FROM agencies WHERE id = ?");
$stmt->execute([$agency_id]);
$agency = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$agency) {
    exit('Agency not found.');
}

// Uložení změn
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $agency_name = trim($_POST['agency_name']);
    $agency_address = trim($_POST['agency_address']);
    $agency_city = trim($_POST['agency_city']);
    $agency_zip = trim($_POST['agency_zip']);
    $agency_country = trim($_POST['agency_country']);
    $agency_codice_fiscale = trim($_POST['agency_codice_fiscale']);
    $representative_name = trim($_POST['representative_name']);
    $representative_email = trim($_POST['representative_email']);
    $phone = trim($_POST['phone']);
    $commission_sale_percent = floatval($_POST['commission_sale_percent']);
    $commission_fullservice_percent = floatval($_POST['commission_fullservice_percent']);
    $commission_rent_percent = floatval($_POST['commission_rent_percent']);
    $contract_signed = isset($_POST['contract_signed']) ? 1 : 0;

    $update = $pdo->prepare("UPDATE agencies SET 
        agency_name = ?, 
        agency_address = ?, 
        agency_city = ?, 
        agency_zip = ?, 
        agency_country = ?, 
        agency_codice_fiscale = ?, 
        representative_name = ?, 
        representative_email = ?, 
        phone = ?, 
        commission_sale_percent = ?, 
        commission_fullservice_percent = ?, 
        commission_rent_percent = ?, 
        contract_signed = ? 
        WHERE id = ?");
    $update->execute([
        $agency_name,
        $agency_address,
        $agency_city,
        $agency_zip,
        $agency_country,
        $agency_codice_fiscale,
        $representative_name,
        $representative_email,
        $phone,
        $commission_sale_percent,
        $commission_fullservice_percent,
        $commission_rent_percent,
        $contract_signed,
        $agency_id
    ]);

    echo "<p style='color:green; text-align:center;'>✔️ Changes saved successfully!</p>";

    // Reload dat
    $stmt = $pdo->prepare("SELECT * FROM agencies WHERE id = ?");
    $stmt->execute([$agency_id]);
    $agency = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<?php include 'header.php'; ?>
<?php include 'menu.php'; ?>

<header class="main-header">
  <h1>✏️ Edit Agency</h1>
  <p>Editing details of: <?= htmlspecialchars($agency['agency_name']) ?></p>
</header>

<section class="section">
  <form method="POST" style="max-width:700px;margin:auto;font-family:sans-serif;">

    <label>🏢 Agency Name:</label><br>
    <input type="text" name="agency_name" value="<?= htmlspecialchars($agency['agency_name']) ?>" required style="width:100%;"><br><br>

    <label>📍 Address:</label><br>
    <input type="text" name="agency_address" value="<?= htmlspecialchars($agency['agency_address']) ?>" required style="width:100%;"><br><br>

    <label>🏙️ City:</label><br>
    <input type="text" name="agency_city" value="<?= htmlspecialchars($agency['agency_city']) ?>" required style="width:100%;"><br><br>

    <label>🏷️ ZIP Code:</label><br>
    <input type="text" name="agency_zip" value="<?= htmlspecialchars($agency['agency_zip']) ?>" required style="width:100%;"><br><br>

    <label>🌍 Country:</label><br>
    <input type="text" name="agency_country" value="<?= htmlspecialchars($agency['agency_country']) ?>" required style="width:100%;"><br><br>

    <label>🆔 Codice Fiscale / VAT:</label><br>
    <input type="text" name="agency_codice_fiscale" value="<?= htmlspecialchars($agency['agency_codice_fiscale']) ?>" required style="width:100%;"><br><br>

    <label>👤 Representative Name:</label><br>
    <input type="text" name="representative_name" value="<?= htmlspecialchars($agency['representative_name']) ?>" required style="width:100%;"><br><br>

    <label>📧 Representative Email:</label><br>
    <input type="email" name="representative_email" value="<?= htmlspecialchars($agency['representative_email']) ?>" required style="width:100%;"><br><br>

    <label>📞 Phone Number:</label><br>
    <input type="text" name="phone" value="<?= htmlspecialchars($agency['phone']) ?>" style="width:100%;"><br><br>

    <label>💰 Commission on Sale (%):</label><br>
    <input type="number" name="commission_sale_percent" step="0.01" value="<?= htmlspecialchars($agency['commission_sale_percent']) ?>" required style="width:100%;"><br><br>

    <label>🔵 Commission for Full Service (%):</label><br>
    <input type="number" name="commission_fullservice_percent" step="0.01" value="<?= htmlspecialchars($agency['commission_fullservice_percent']) ?>" required style="width:100%;"><br><br>

    <label>💸 Commission on Rent (%):</label><br>
    <input type="number" name="commission_rent_percent" step="0.01" value="<?= htmlspecialchars($agency['commission_rent_percent']) ?>" style="width:100%;"><br><br>

    <label>📝 Contract Signed:</label><br>
    <input type="checkbox" name="contract_signed" <?= $agency['contract_signed'] ? 'checked' : '' ?>><br><br>

    <div style="text-align:center; margin-top:20px;">
      <button type="submit" class="cta-button" style="padding:10px 24px;">💾 Save Changes</button>
    </div>

  </form>

  <p style="text-align:center; margin-top:30px;">
    <a href="agency_list.php" class="cta-button" style="padding:10px 24px;">⬅️ Back to Agencies List</a>
  </p>
</section>

<?php include 'footer.php'; ?>


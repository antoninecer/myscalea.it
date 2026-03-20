<?php
session_start();
require_once 'inc/connect.php';

// Přístup jen pro adminy
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    exit('Access denied.');
}

// Uložení nové agentury
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

    $insert = $pdo->prepare("INSERT INTO agencies 
        (agency_name, agency_address, agency_city, agency_zip, agency_country, agency_codice_fiscale, representative_name, representative_email, phone, commission_sale_percent, commission_fullservice_percent, commission_rent_percent, contract_signed) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insert->execute([
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
        $contract_signed
    ]);

    echo "<p style='color:green; text-align:center;'>✔️ New agency successfully added!</p>";
}
?>

<?php include 'header.php'; ?>
<?php include 'menu.php'; ?>

<header class="main-header">
  <h1>➕ Add New Agency</h1>
  <p>Fill in the details to register a new partner real estate agency.</p>
</header>

<section class="section">
  <form method="POST" style="max-width:700px;margin:auto;font-family:sans-serif;">

    <label>🏢 Agency Name:</label><br>
    <input type="text" name="agency_name" required style="width:100%;"><br><br>

    <label>📍 Address:</label><br>
    <input type="text" name="agency_address" required style="width:100%;"><br><br>

    <label>🏙️ City:</label><br>
    <input type="text" name="agency_city" required style="width:100%;"><br><br>

    <label>🏷️ ZIP Code:</label><br>
    <input type="text" name="agency_zip" required style="width:100%;"><br><br>

    <label>🌍 Country:</label><br>
    <input type="text" name="agency_country" value="Italy" required style="width:100%;"><br><br>

    <label>🆔 Codice Fiscale / VAT:</label><br>
    <input type="text" name="agency_codice_fiscale" required style="width:100%;"><br><br>

    <label>👤 Representative Name:</label><br>
    <input type="text" name="representative_name" required style="width:100%;"><br><br>

    <label>📧 Representative Email:</label><br>
    <input type="email" name="representative_email" required style="width:100%;"><br><br>

    <label>📞 Phone Number:</label><br>
    <input type="text" name="phone" style="width:100%;"><br><br>

    <label>💰 Commission on Sale (%):</label><br>
    <input type="number" name="commission_sale_percent" step="0.01" value="2.00" required style="width:100%;"><br><br>

    <label>🔵 Commission for Full Service (%):</label><br>
    <input type="number" name="commission_fullservice_percent" step="0.01" value="3.00" required style="width:100%;"><br><br>

    <label>💸 Commission on Rent (%):</label><br>
    <input type="number" name="commission_rent_percent" step="0.01" value="0.00" style="width:100%;"><br><br>

    <label>📝 Contract Signed:</label><br>
    <input type="checkbox" name="contract_signed"><br><br>

    <div style="text-align:center; margin-top:20px;">
      <button type="submit" class="cta-button" style="padding:10px 24px;">➕ Add Agency</button>
    </div>

  </form>

  <p style="text-align:center; margin-top:30px;">
    <a href="agency_list.php" class="cta-button" style="padding:10px 24px;">⬅️ Back to Agencies List</a>
  </p>
</section>

<?php include 'footer.php'; ?>


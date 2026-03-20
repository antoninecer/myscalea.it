<?php
session_start();
require_once 'inc/connect.php';

// Přístup jen pro adminy a managery
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['role'] !== 'manager')) {
    echo "<p style='color:red; text-align:center;'>Access denied. Redirecting...</p>";
    echo "<script>setTimeout(() => window.location.href = 'index.php', 3000);</script>";
    exit;
}

// Načtení všech agentur
$stmt = $pdo->query("SELECT * FROM agencies ORDER BY agency_name ASC");
$agencies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'header.php'; ?>
<?php include 'menu.php'; ?>

<header class="main-header">
  <h1>🏢 Real Estate Agencies</h1>
  <p>Overview of all registered partner agencies</p>
</header>

<section class="section" style="text-align: center;">
  <a href="agency_add.php" class="cta-button" style="margin-bottom:20px; display:inline-block;">➕ Add New Agency</a>
</section>

<section class="section">
  <table style="width:100%; border-collapse:collapse; font-size: 0.95em;">
    <thead>
      <tr style="background:#f5f5f5;">
        <th style="border-bottom:1px solid #ccc; text-align:left; padding:8px;">🏢 Agency Name</th>
        <th style="border-bottom:1px solid #ccc; text-align:left; padding:8px;">🏙️ City</th>
        <th style="border-bottom:1px solid #ccc; text-align:left; padding:8px;">👤 Representative</th>
        <th style="border-bottom:1px solid #ccc; text-align:left; padding:8px;">📞 Phone</th>
        <th style="border-bottom:1px solid #ccc; text-align:left; padding:8px;">📧 Email</th>
        <th style="border-bottom:1px solid #ccc; text-align:left; padding:8px;">💰 Sale %</th>
        <th style="border-bottom:1px solid #ccc; text-align:left; padding:8px;">🔵 Full Service %</th>
        <th style="border-bottom:1px solid #ccc; text-align:left; padding:8px;">📝 Contract</th>
        <th style="border-bottom:1px solid #ccc; text-align:left; padding:8px;">⚙️ Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($agencies as $agency): 
        $safeName = preg_replace('/[^a-zA-Z0-9]/', '', $agency['agency_name']);
        $folder = "agencies/$safeName";
        $signed = "$folder/ramcova_smlouva_{$safeName}_signed.pdf";
        $prefilled = "$folder/ramcova_smlouva_{$safeName}_prefirmato.pdf";

        $contractHtml = '<a href="generate_agency_contract.php?id=' . $agency['id'] . '" class="cta-button" style="padding:6px 12px;">📝 Generate Contract</a>';
        if (file_exists($signed)) {
            $contractHtml = '<a href="' . $signed . '" target="_blank" class="cta-button" style="padding:6px 12px;">📄 View Signed Contract</a>';
        } elseif (file_exists($prefilled)) {
            $contractHtml = '<a href="' . $prefilled . '" target="_blank" class="cta-button" style="padding:6px 12px;">📄 View Prefilled Contract</a>';
        }
      ?>
        <tr>
          <td style="padding:8px;"><?= htmlspecialchars($agency['agency_name']) ?></td>
          <td style="padding:8px;"><?= htmlspecialchars($agency['agency_city']) ?></td>
          <td style="padding:8px;"><?= htmlspecialchars($agency['representative_name']) ?></td>
          <td style="padding:8px;"><?= htmlspecialchars($agency['phone']) ?></td>
          <td style="padding:8px;">
            <?php if ($agency['representative_email']): ?>
              <a href="mailto:<?= htmlspecialchars($agency['representative_email']) ?>"><?= htmlspecialchars($agency['representative_email']) ?></a>
            <?php else: ?>
              —
            <?php endif; ?>
          </td>
          <td style="padding:8px;"><?= number_format($agency['commission_sale_percent'], 2) ?>%</td>
          <td style="padding:8px;"><?= number_format($agency['commission_fullservice_percent'], 2) ?>%</td>
          <td style="padding:8px;">
            <?= $agency['contract_signed'] ? '✔️ Yes' : '❌ No' ?><br>
            <div style="margin-top:6px;"> <?= $contractHtml ?> </div>
          </td>
          <td style="padding:8px;">
            <div style="display:flex; flex-direction: column; gap:6px;">
              <a href="agency_edit.php?id=<?= $agency['id'] ?>" class="cta-button" style="padding:6px 12px;">✏️ Edit</a>
              <a href="agency_assign.php?id=<?= $agency['id'] ?>" class="cta-button" style="padding:6px 12px;">👤 Assign User</a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>

<?php include 'footer.php'; ?>


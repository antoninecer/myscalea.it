<?php
session_start();
require_once 'inc/connect.php';

// Přístup pouze pro adminy, managery a agenty
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'manager', 'agent'])) {
    exit('❌ Access denied.');
}

// Načtení klientů s poslední akcí
$sql = "
    SELECT c.*, 
           (SELECT action FROM client_logs cl WHERE cl.client_id = c.id ORDER BY cl.created_at DESC LIMIT 1) AS last_action,
           (SELECT note FROM client_logs cl WHERE cl.client_id = c.id ORDER BY cl.created_at DESC LIMIT 1) AS last_note,
           (SELECT created_at FROM client_logs cl WHERE cl.client_id = c.id ORDER BY cl.created_at DESC LIMIT 1) AS last_update
    FROM clients c
    WHERE c.verified = 1
    ORDER BY last_update DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'header.php'; ?>
<?php include 'menu.php'; ?>

<header class="main-header">
  <h1>👥 Client Reports</h1>
  <p>Overview of active clients and their latest status</p>
</header>

<section class="section">
  <table style="width:100%; border-collapse:collapse; font-family:sans-serif; font-size:0.95em;">
    <thead>
      <tr style="background:#f5f5f5;">
        <th style="padding:8px; border-bottom:1px solid #ccc;">👤 Client</th>
        <th style="padding:8px; border-bottom:1px solid #ccc;">📋 Requirements</th>
        <th style="padding:8px; border-bottom:1px solid #ccc;">📈 Current Status</th>
        <th style="padding:8px; border-bottom:1px solid #ccc;">📝 Last Note</th>
        <th style="padding:8px; border-bottom:1px solid #ccc;">📅 Last Update & Documents</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($clients as $client): 
        $req = json_decode($client['requirements'], true);
      ?>
        <tr>
          <td style="padding:8px;">
            <strong><?= htmlspecialchars($client['name']) ?></strong><br>
            <a href="mailto:<?= htmlspecialchars($client['email']) ?>"><?= htmlspecialchars($client['email']) ?></a><br>
            <?= htmlspecialchars($client['phone']) ?><br>
            <small>Created: <?= date('d.m.Y H:i', strtotime($client['created_at'])) ?></small>
          </td>
          <td style="padding:8px;">
            <?= htmlspecialchars($req['location']) ?>, <?= htmlspecialchars($req['rooms']) ?><br>
            <?= htmlspecialchars($req['area']) ?> m², <?= number_format($req['budget'], 0, ',', ' ') ?> EUR
            <?php if (!empty($req['note'])): ?><br><em><?= htmlspecialchars($req['note']) ?></em><?php endif; ?>
          </td>
          <td style="padding:8px;">
            <?= htmlspecialchars($client['last_action'] ?? 'New request') ?><br>
            <?php if (in_array($_SESSION['user']['role'], ['admin', 'manager']) && empty($client['last_action'])): ?>
              <a href="client_first_contact.php?client_id=<?= $client['id'] ?>" class="cta-button" style="margin-top:5px; display:inline-block;">📞 First Contact</a>
            <?php endif; ?>
          </td>
          <td style="padding:8px;"><?= htmlspecialchars($client['last_note'] ?? '-') ?></td>
          <td style="padding:8px;">
            <?= $client['last_update'] ? date('d.m.Y H:i', strtotime($client['last_update'])) : '-' ?><br>
            <?php
              $dir_name = preg_replace('/[^a-zA-Z]/', '', $client['email']);
              $dir_path = __DIR__ . "/clients/$dir_name";
              $web_path = "clients/$dir_name";

              if (is_dir($dir_path)) {
                  $files = scandir($dir_path);
                  foreach ($files as $file) {
                      if ($file !== '.' && $file !== '..') {
                          echo "<a href='$web_path/$file' class='file-link' target='_blank' style='display:block; margin-top:3px;'>📎 " . htmlspecialchars($file) . "</a>";
                      }
                  }
              }
            ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>

<section class="section" style="text-align:center; margin-top:30px;">
  <a href="/" class="cta-button" style="padding:10px 24px;">🏠 Back to Homepage</a>
</section>

<?php include 'footer.php'; ?>


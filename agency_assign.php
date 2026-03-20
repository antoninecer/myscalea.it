<?php
session_start();
require_once 'inc/connect.php';

// Přístup jen pro adminy
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    exit('Access denied.');
}

$agency_id = $_GET['id'] ?? null;
if (!$agency_id) {
    exit('No agency selected.');
}

// Při odeslání formuláře
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id']);
    $update = $pdo->prepare("UPDATE users SET agency_id = ? WHERE id = ?");
    $update->execute([$agency_id, $user_id]);
    header('Location: agency_list.php');
    exit;
}

// Načteme všechny ugenty bez přiřazené agentury
$stmt = $pdo->query("SELECT * FROM users WHERE agency_id IS NULL AND user_role_id = 4 ORDER BY username ASC"); // role 4 = agentura
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Načteme info o agentuře
$stmtAgency = $pdo->prepare("SELECT * FROM agencies WHERE id = ?");
$stmtAgency->execute([$agency_id]);
$agency = $stmtAgency->fetch();
if (!$agency) {
    exit('Agency not found.');
}
?>

<?php include 'header.php'; ?>
<?php include 'menu.php'; ?>

<header class="main-header">
  <h1>👥 Assign Agency</h1>
  <p>Assign agency <strong><?= htmlspecialchars($agency['agency_name']) ?></strong> to a user</p>
</header>

<section class="section">
  <form method="POST" style="max-width:500px; margin:auto;">
    <label>Select User:</label><br>
    <select name="user_id" required style="width:100%; padding:8px; margin-top:10px;">
      <option value="">-- Select User --</option>
      <?php foreach ($users as $user): ?>
        <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?> (<?= htmlspecialchars($user['email']) ?>)</option>
      <?php endforeach; ?>
    </select><br><br>
    <button type="submit" class="cta-button">✅ Assign Agency</button>
  </form>

  <p style="text-align:center; margin-top:20px;">
    <a href="agency_list.php" class="cta-button">⬅️ Back to Agencies List</a>
  </p>
</section>

<?php include 'footer.php'; ?>


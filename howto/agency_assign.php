<?php
session_start();
require_once 'inc/connect.php';

// Zkontrolujeme, jestli je admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    exit('Access denied.');
}

// Získáme seznam agentur
$stmt = $pdo->query("SELECT id, agency_name FROM agencies ORDER BY agency_name ASC");
$agencies = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Získáme seznam uživatelů
$stmt = $pdo->query("SELECT id, username, email FROM users ORDER BY username ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pokud je odesláno přiřazení
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['agency_id'])) {
    $user_id = intval($_POST['user_id']);
    $agency_id = intval($_POST['agency_id']);

    $update = $pdo->prepare("UPDATE users SET agency_id = ? WHERE id = ?");
    $update->execute([$agency_id, $user_id]);

    echo "<p style='color:green;'>✔️ Successfully assigned agency to user.</p>";
}
?>

<?php include 'header.php'; ?>
<?php include 'menu.php'; ?>

<section class="section">
  <h2>Assign Agency to User</h2>

  <form method="POST" style="max-width:600px;margin:auto;">
    <label for="user_id">Select User:</label><br>
    <select name="user_id" id="user_id" required style="width:100%; margin-bottom:20px;">
      <?php foreach ($users as $user): ?>
        <option value="<?= htmlspecialchars($user['id']) ?>"><?= htmlspecialchars($user['username']) ?> (<?= htmlspecialchars($user['email']) ?>)</option>
      <?php endforeach; ?>
    </select><br>

    <label for="agency_id">Select Agency:</label><br>
    <select name="agency_id" id="agency_id" required style="width:100%; margin-bottom:20px;">
      <?php foreach ($agencies as $agency): ?>
        <option value="<?= htmlspecialchars($agency['id']) ?>"><?= htmlspecialchars($agency['agency_name']) ?></option>
      <?php endforeach; ?>
    </select><br>

    <button type="submit" class="cta-button">Assign Agency</button>
  </form>
</section>

<?php include 'footer.php'; ?>


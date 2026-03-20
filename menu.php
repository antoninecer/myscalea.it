<?php
require_once 'inc/connect.php';

if (isset($_SESSION['user']['id'])) {
    $stmt = $pdo->prepare("
        SELECT r.name AS role_name, u.*
        FROM users u
        LEFT JOIN user_roles r ON u.user_role_id = r.id
        WHERE u.id = ?
        LIMIT 1
    ");
    $stmt->execute([$_SESSION['user']['id']]);
    $userFull = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($userFull) {
        $_SESSION['user'] = $userFull;
        $_SESSION['user']['role'] = $userFull['role_name']; // zpětná kompatibilita
    }
}

$currentRole = $_SESSION['user']['role_name'] ?? '';
#print_r($_SESSION);
?>

<div id="burger"><i class="fas fa-bars"></i></div>
<div id="sideMenu">
  <h3>Menu</h3>
  <a href="/">🏠 Main page</a>
  <a href="/my_way.php">👣 My Way</a>
  <a href="/map/">🗺️ Map</a>
  <a href="/about.php">🍇 About this project</a>
  <a href="/scalea_info.php">🍇 Scalea & Cedars</a>
  <br>
  <a href="/usefulpages.php">💡 Useful Pages</a>
  <br>
  <?php if ($currentRole): ?>
    <a href="/blog_post.php">✍️ New post</a>
  <?php endif; ?>

  <?php if ($currentRole === 'admin'): ?>
    <a href="/add_place.php">➕ Add place</a>
    <a href="/user_manage.php">👤 User management</a>
    <a href="/agency_list.php">🏢 Agencies</a>
  <?php endif; ?>

  <?php if (in_array($currentRole, ['admin', 'agent'])): ?>
    <a href="/client_reports.php">📊 Clients reports</a>
  <?php endif; ?>

  <?php if (in_array($currentRole, ['admin', 'manager'])): ?>
    <a href="/client_guide.php">🔎 Client guide</a>
  <?php endif; ?>

  <?php if ($currentRole === 'agent' && !empty($_SESSION['user']['agency_id'])): ?>
    <a href="/agency_page.php">🏢 My Agency</a>
  <?php endif; ?>

  <hr>
  <?php include 'login.php'; ?>
  <hr>
  <div style="padding: 5px 10px;">
    🌍 <small>Select language:</small>
    <div id="google_translate_element" style="font-size: 0.9em;"></div>
  </div>
</div>


<?php
require_once __DIR__ . '/inc/connect.php';

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
        $_SESSION['user']['role'] = $userFull['role_name']; // backwards compatibility
    }
}

$currentRole = $_SESSION['user']['role_name'] ?? ($_SESSION['user']['role'] ?? '');
$isLoggedIn = !empty($currentRole);
$username = $_SESSION['user']['username'] ?? $_SESSION['user']['email'] ?? '';
?>

<nav class="top-nav" id="topNav">
  <div class="top-nav-inner">
    <a class="top-nav-brand" href="/" aria-label="MyScalea.it home">
      <span class="top-nav-logo">M</span>
      <span>
        <strong>MyScalea.it</strong>
        <small>Scalea local</small>
      </span>
    </a>

    <button class="top-nav-toggle" id="topNavToggle" type="button" aria-expanded="false" aria-controls="topNavMenu">
      <span class="top-nav-toggle-icon">☰</span>
      <span>Menu</span>
    </button>

    <div class="top-nav-menu" id="topNavMenu">
      <div class="top-nav-links">
        <a href="/">Home</a>
        <a href="/map/">Map</a>
        <a href="/scalea_info.php">Scalea & Cedars</a>
        <a href="/usefulpages.php">Useful pages</a>
        <a href="/about.php">About</a>
        <a href="/my_way.php">My Way</a>

        <?php if ($isLoggedIn): ?>
          <a href="/blog_post.php">New post</a>
        <?php endif; ?>

        <?php if ($currentRole === 'agent' && !empty($_SESSION['user']['agency_id'])): ?>
          <a href="/agency_page.php">My Agency</a>
        <?php endif; ?>

        <?php if (in_array($currentRole, ['admin', 'manager'], true)): ?>
          <a href="/client_guide.php">Client guide</a>
        <?php endif; ?>

        <?php if (in_array($currentRole, ['admin', 'agent'], true)): ?>
          <a href="/client_reports.php">Reports</a>
        <?php endif; ?>

        <?php if ($currentRole === 'admin'): ?>
          <a href="/add_place.php">Add place</a>
          <a href="/agency_list.php">Agencies</a>
          <a href="/user_manage.php">Users</a>
        <?php endif; ?>
      </div>

      <div class="top-nav-tools">
        <div class="top-nav-login">
          <?php include __DIR__ . '/login.php'; ?>
        </div>
      </div>
    </div>
  </div>
</nav>

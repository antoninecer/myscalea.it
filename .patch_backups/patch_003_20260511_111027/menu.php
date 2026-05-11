<?php
require_once __DIR__ . '/inc/connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
        $_SESSION['user']['role'] = $userFull['role_name'] ?? ($_SESSION['user']['role'] ?? '');
    }
}

$currentRole = $_SESSION['user']['role_name'] ?? $_SESSION['user']['role'] ?? '';
$username = $_SESSION['user']['username'] ?? '';

function menu_active(string $path): string {
    $current = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    if ($path === '/') {
        return $current === '/' || $current === '/index.php' ? ' active' : '';
    }
    return str_starts_with($current, $path) ? ' active' : '';
}

function menu_link(string $href, string $label, string $icon = ''): void {
    $active = menu_active($href);
    echo '<a class="menu-link' . $active . '" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">';
    if ($icon !== '') {
        echo '<span class="menu-icon">' . $icon . '</span>';
    }
    echo '<span>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span>';
    echo '</a>';
}
?>

<button id="burger"
        class="menu-toggle"
        type="button"
        aria-label="Open menu"
        aria-controls="sideMenu"
        aria-expanded="false">
  <i class="fas fa-bars" aria-hidden="true"></i>
  <span class="menu-toggle-text">Menu</span>
</button>

<div id="menuOverlay" class="menu-overlay" hidden></div>

<nav id="sideMenu" class="side-menu" aria-label="Main menu" aria-hidden="true">
  <div class="menu-panel-header">
    <a class="menu-brand" href="/">
      <span class="menu-brand-title">MyScalea.it</span>
      <span class="menu-brand-subtitle">Scalea local guide</span>
    </a>

    <button id="menuClose"
            class="menu-close"
            type="button"
            aria-label="Close menu">
      ×
    </button>
  </div>

  <?php if ($username): ?>
    <div class="menu-user-card">
      <div class="menu-user-label">Logged in</div>
      <div class="menu-user-name"><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></div>
      <?php if ($currentRole): ?>
        <div class="menu-user-role"><?= htmlspecialchars($currentRole, ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <div class="menu-section">
    <div class="menu-section-title">Explore</div>
    <?php
      menu_link('/', 'Main page', '🏠');
      menu_link('/map/', 'Verified Scalea map', '🗺️');
      menu_link('/scalea_info.php', 'Scalea & Cedars', '🍋');
      menu_link('/usefulpages.php', 'Useful pages', '💡');
      menu_link('/about.php', 'About this project', 'ℹ️');
    ?>
  </div>

  <div class="menu-section">
    <div class="menu-section-title">Community</div>
    <?php
      menu_link('/my_way.php', 'My Way', '👣');
      if ($currentRole) {
          menu_link('/blog_post.php', 'New post', '✍️');
      }
    ?>
  </div>

  <?php if (in_array($currentRole, ['admin', 'manager', 'agent'], true)): ?>
    <div class="menu-section">
      <div class="menu-section-title">Work area</div>

      <?php if ($currentRole === 'agent' && !empty($_SESSION['user']['agency_id'])): ?>
        <?php menu_link('/agency_page.php', 'My Agency', '🏢'); ?>
      <?php endif; ?>

      <?php if (in_array($currentRole, ['admin', 'agent'], true)): ?>
        <?php menu_link('/client_reports.php', 'Client reports', '📊'); ?>
      <?php endif; ?>

      <?php if (in_array($currentRole, ['admin', 'manager'], true)): ?>
        <?php menu_link('/client_guide.php', 'Client guide', '🔎'); ?>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <?php if ($currentRole === 'admin'): ?>
    <div class="menu-section">
      <div class="menu-section-title">Admin</div>
      <?php
        menu_link('/add_place.php', 'Add place', '➕');
        menu_link('/user_manage.php', 'User management', '👤');
        menu_link('/agency_list.php', 'Agencies', '🏢');
      ?>
    </div>
  <?php endif; ?>

  <div class="menu-section menu-account-section">
    <div class="menu-section-title">Account</div>
    <div class="menu-login-box">
      <?php include __DIR__ . '/login.php'; ?>
    </div>
  </div>

  <div class="menu-section menu-language-section">
    <div class="menu-section-title">Language</div>
    <div id="google_translate_element"></div>
  </div>
</nav>

<main id="main-content" class="page-content">

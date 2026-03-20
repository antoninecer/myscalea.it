<?php
require_once 'inc/connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$allowed = false;

if (isset($_SESSION['user'])) {
    $role = $_SESSION['user']['role'];

    if (in_array($role, ['admin', 'manager', 'agent'])) {
        $allowed = true;
    } elseif ($role === 'user') {
        // Ověř, zda uživatel (user) existuje jako klient podle e-mailu
        $email = $_SESSION['user']['email'] ?? '';
        $stmt = $pdo->prepare("SELECT id FROM clients WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $allowed = true;
        } else {
          echo "<div style='text-align:center; margin:2rem;'>
          <p style='color:#c00; font-weight:bold;'>This section is restricted to verified clients and project administrators.</p>
          <p>If you are already registered as a client (your login is your email address), please log in below. If not, you can create your client profile to gain full access.</p>
          <div style='margin-top:1rem;'>
            <a href='login.php' class='cta-button' style='margin-right: 10px;'>🔑 Log In</a>
            <a href='client_request.php' class='cta-button'>📋 Register as Client</a>
          </div>
        </div>";
            return;
        }
    }
}

if (!$allowed) {
  echo "<div style='text-align:center; margin:2rem;'>
  <p style='color:#c00; font-weight:bold;'>This section is restricted to verified clients and project administrators.</p>
  <p>If you are already registered as a client (your login is your email address), please log in below. If not, you can create your client profile to gain full access.</p>
  <div style='margin-top:1rem;'>
    <a href='login.php' class='cta-button' style='margin-right: 10px;'>🔑 Log In</a>
    <a href='client_request.php' class='cta-button'>📋 Register as Client</a>
  </div>
</div>";
    return;
}

// Získání agentů
$agentStmt = $pdo->prepare("SELECT id FROM users WHERE user_role_id = (SELECT id FROM user_roles WHERE name = 'agent')");
$agentStmt->execute();
$agentIds = $agentStmt->fetchAll(PDO::FETCH_COLUMN);

if (empty($agentIds)) {
    echo "<p>No agents found.</p>";
    return;
}

$in = str_repeat('?,', count($agentIds) - 1) . '?';
$query = "SELECT * FROM blog_posts WHERE user_id IN ($in) AND published = 1 AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 10";
$stmt = $pdo->prepare($query);
$stmt->execute($agentIds);
$posts = $stmt->fetchAll();

?>

<style>
.blog-container {
  max-width: 800px;
  margin: 2rem auto;
  padding: 1rem;
  background: #fdfdfd;
  border: 1px solid #ccc;
  border-radius: 10px;
  font-family: sans-serif;
}
.blog-post {
  margin-bottom: 20px;
  padding-bottom: 10px;
  border-bottom: 1px dashed #bbb;
}
</style>

<section class="blog-container">
  <h2>🧑‍💼 Agent Blog Posts</h2>
  <?php foreach ($posts as $post): ?>
    <div class="blog-post">
      <h3><?= htmlspecialchars($post['title']) ?></h3>
      <p><?= makeLinks(mb_substr($post['content'], 0, 500)) ?>...</p>
      <small>📅 <?= date('Y-m-d H:i', strtotime($post['created_at'])) ?></small>
    </div>
  <?php endforeach; ?>
  <?php if (empty($posts)): ?>
    <p>No posts found from agents.</p>
  <?php endif; ?>
</section>

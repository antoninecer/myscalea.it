<?php
// blog_view.php – modular blog list for any user with optional fullscreen
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'inc/connect.php';


if (!isset($_POST['user_id'])) return;

$view_user_id = (int)$_POST['user_id'];
$sort = ($_POST['sort'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
$search = trim($_POST['search'] ?? '');

$is_owner = isset($_SESSION['user']) && ($_SESSION['user']['id'] == $view_user_id || $_SESSION['user']['role'] === 'admin');

$query = "SELECT * FROM blog_posts WHERE user_id = ?";
$params = [$view_user_id];

if (!$is_owner) {
    $query .= " AND published = 1 AND deleted_at IS NULL";
} else {
    $query .= " AND deleted_at IS NULL";
}

if ($search !== '') {
    $query .= " AND (title LIKE ? OR content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY created_at $sort";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$posts = $stmt->fetchAll();
?>

<style>
.blog-container {
  max-height: 400px;
  overflow-y: auto;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 8px;
  background: #fefefe;
}
.blog-post {
  margin-bottom: 20px;
  padding-bottom: 10px;
  border-bottom: 1px dashed #bbb;
}
.fullscreen {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: white;
  overflow-y: scroll;
  padding: 40px;
  z-index: 9999;
}
</style>

<div id="blogModule" class="blog-container">
  <?php foreach ($posts as $post): ?>
    <div class="blog-post">
      <h4><?= htmlspecialchars($post['title']) ?><?= !$post['published'] ? ' <em>(Hidden)</em>' : '' ?></h4>
      <div><?= makeLinks(mb_substr($post['content'], 0, 300)) ?>...</div>
      <small><?= date('Y-m-d H:i', strtotime($post['created_at'])) ?></small>
      <?php if ($is_owner): ?>
        <form method="POST" action="blog_edit.php" target="_blank" style="display:inline;">
          <input type="hidden" name="edit_id" value="<?= $post['id'] ?>">
          <button type="submit">Edit</button>
        </form>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>

<?php if (!empty($posts)): ?>
  <button onclick="document.getElementById('blogModule').classList.toggle('fullscreen')">
    Toggle Fullscreen
  </button>
<?php endif; ?>

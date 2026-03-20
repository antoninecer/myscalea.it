<?php
session_start();
require_once 'inc/connect.php';


if (!isset($_SESSION['user'])) {
    echo "<p style='color:red;'>Access denied. Please log in.</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['edit_id'])) {
    echo "<p style='color:red;'>Invalid access.</p>";
    exit;
}

$post_id = (int)$_POST['edit_id'];

$stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ? LIMIT 1");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post) {
    echo "<p style='color:red;'>Post not found.</p>";
    exit;
}

$user_id = $_SESSION['user']['id'];
$user_role = $_SESSION['user']['role'];

if ($post['user_id'] != $user_id && $user_role !== 'admin') {
    echo "<p style='color:red;'>You are not authorized to edit this post.</p>";
    exit;
}

// Handle update
if (isset($_POST['save_post'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $published = isset($_POST['published']) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE blog_posts SET title = ?, content = ?, published = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$title, $content, $published, $post_id]);
    echo "<p style='color:green;'>✅ Post updated successfully.</p>";
}

// Handle soft delete
if (isset($_POST['delete_post'])) {
    $stmt = $pdo->prepare("UPDATE blog_posts SET deleted_at = NOW() WHERE id = ?");
    $stmt->execute([$post_id]);
    echo "<p style='color:red;'>🗑️ Post marked as deleted.</p>";
    exit;
}

// For preview
$title = htmlspecialchars($post['title']);
$content = $post['content'];
$published = (bool)$post['published'];
?>

<?php include 'header.php'; ?>
<?php include 'menu.php'; ?>

<header class="main-header">
  <h1>✏️ Edit Blog Post</h1>
</header>

<section class="section" style="max-width:700px;margin:auto;">
  <form method="POST" style="border:1px solid #ccc;padding:20px;border-radius:10px;">
    <input type="hidden" name="edit_id" value="<?= $post_id ?>">

    <label for="title">Title:</label><br>
    <input type="text" name="title" id="title" required style="width:100%;" value="<?= $title ?>"><br><br>

    <label for="content">Content:</label><br>
    <textarea name="content" id="content" rows="8" required style="width:100%;"><?= htmlspecialchars($content) ?></textarea><br><br>

    <label>
      <input type="checkbox" name="published" <?= $published ? 'checked' : '' ?>>
      ✅ Visible to public
    </label><br><br>

    <button type="submit" name="save_post" class="cta-button">💾 Save Changes</button>
    <button type="submit" name="delete_post" class="cta-button" style="background:#dc3545;" onclick="return confirm('Really delete this post?')">🗑️ Delete</button>
  </form>

  <?php if (isset($_POST['save_post'])): ?>
    <div style="margin-top:30px;padding:15px;border-left:4px solid #007bff;background:#f9f9f9;">
      <h4>🔍 Preview:</h4>
      <?= makeLinks($content) ?>
    </div>
  <?php endif; ?>
</section>

<?php include 'footer.php'; ?>

<?php
session_start();
require_once 'inc/connect.php';

?>

<?php include 'header.php'; ?>
<?php include 'menu.php'; ?>

<header class="main-header">
  <h1>Create a New Post</h1>
  <p>Share your tips, experiences or suggestions with the community!</p>
</header>

<section class="section">
<?php
if (!isset($_SESSION['user'])) {
    echo "<p style='color:red;'>You must be logged in to create a post.</p>";
    echo "<div style='text-align:center;'><a href='/login.php' class='cta-button'>🔑 Login</a></div>";
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_post'])) {
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);

        if ($title && $content) {
            $stmt = $pdo->prepare("INSERT INTO blog_posts (user_id, title, content) VALUES (?, ?, ?)");
            $stmt->execute([
                $_SESSION['user']['id'],
                $title,
                $content
            ]);
            echo "<p style='color:green;'>✅ Post submitted successfully!</p>";
        } else {
            echo "<p style='color:red;'>❌ Please fill in both the title and content.</p>";
        }
    }
?>

<form method="POST" style="max-width:600px;margin:auto;">
  <h2 style="text-align:center;">✏️ Create New Blog Post</h2>

  <label for="title">Title:</label><br>
  <input type="text" name="title" id="title" required style="width:100%; padding:8px; margin-bottom:15px;"><br>

  <label for="content">Content:</label><br>
  <textarea name="content" id="content" rows="8" required style="width:100%; padding:8px; margin-bottom:15px;"></textarea><br>

  <div style="text-align:center;">
    <button type="submit" name="submit_post" class="cta-button" style="font-size:1.1em;padding:10px 20px;">🚀 Publish</button>
  </div>
</form>
<?php } ?>
</section>

<section class="section">
  <h2>📰 Latest Community Posts</h2>
  <?php
  $stmt = $pdo->query("SELECT blog_posts.*, users.username FROM blog_posts JOIN users ON blog_posts.user_id = users.id ORDER BY blog_posts.created_at DESC LIMIT 10");
  $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

  if ($posts):
      foreach ($posts as $post):
  ?>
      <div style="border:1px solid #ccc; padding:15px; margin-bottom:15px; border-radius:8px;">
        <h3><?= htmlspecialchars($post['title']) ?></h3>
        <small>By <strong><?= htmlspecialchars($post['username']) ?></strong> on <?= date('d.m.Y H:i', strtotime($post['created_at'])) ?></small>
        <p style="margin-top:10px;">
          <?= makeLinks(htmlspecialchars($post['content'])) ?>
        </p>
      </div>
  <?php
      endforeach;
  else:
      echo "<p>No posts yet. Be the first to share!</p>";
  endif;
  ?>
</section>

<?php include 'footer.php'; ?>

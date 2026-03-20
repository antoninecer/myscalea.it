<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>

<section class="section">
  <h2>🜏 Our Verified Map of Scalea</h2>
  <p>
    We are creating a <strong>community map</strong> of recommended places in Scalea and nearby – restaurants, beaches, services, attractions – everything that can make your stay better and easier.
  </p>
  <p>
    Every place listed has been personally checked and verified. We believe that real experiences are more valuable than paid advertisements.
  </p>

  <div style="text-align: center; margin: 20px 0;">
    <a href="/map/" class="cta-button" style="font-size: 1.2em; padding: 12px 24px;">🜏 Explore the Scalea Map</a>
  </div>

  <h3>🤝 How you can help:</h3>
  <p>
    If you know a great place that should be included, you can suggest it! We welcome tips from our community.
  </p>
  <p>
    To submit a recommendation, please log in first using the "Log In" button in the menu. If you don't have an account yet, you can register below.
  </p>

  <ul>
    <li>📍 Exact address or Google Maps link (with GPS coordinates)</li>
    <li>⏰ Opening hours (if available)</li>
    <li>🌐 Website or contact information</li>
    <li>📝 A short description of why you recommend the place</li>
  </ul>

  <div style="text-align: center; margin: 30px 0;">
    <?php if (!isset($_SESSION['user'])): ?>
      <button onclick="document.getElementById('registerModal').style.display = 'block'; setTimeout(function(){ document.getElementById('registerUsername').focus(); }, 100);" class="cta-button" style="font-size: 1.2em; padding: 12px 24px;">👉 Register to suggest a place</button>
    <?php else: ?>
      <a href="blog_post.php" class="cta-button" style="font-size: 1.2em; padding: 12px 24px;">📝 Add a new place</a>
    <?php endif; ?>
  </div>
</section>

<!-- Modální okno pro registraci -->
<div id="registerModal" style="display:none; position:fixed; top:15%; left:50%; transform:translateX(-50%); background:#fff; border:1px solid #ccc; padding:20px; z-index:1000; box-shadow:0 0 10px rgba(0,0,0,0.5); max-width:400px;">
  <form method="POST" action="register_submit.php">
    <h3>Register</h3>
    <p>Please check your email and confirm that you want to activate your account.</p>

    <?php if (isset($_SESSION['register_success'])): ?>
      <p style="color:green;"> <?= $_SESSION['register_success'] ?> </p>
      <?php unset($_SESSION['register_success']); ?>
    <?php elseif (isset($_SESSION['register_error'])): ?>
      <p style="color:red;"> <?= $_SESSION['register_error'] ?> </p>
      <?php unset($_SESSION['register_error']); ?>
    <?php endif; ?>

    <input type="text" id="registerUsername" name="username" placeholder="Username" required><br><br>
    <input type="email" name="email" placeholder="Email" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <button type="submit" class="cta-button">Register</button>
    <button type="button" onclick="document.getElementById('registerModal').style.display='none'" style="margin-left:10px;">Cancel</button>
  </form>
</div>

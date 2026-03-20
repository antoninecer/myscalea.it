<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>

<!-- Spouštěcí tlačítko -->
<button onclick="document.getElementById('registerModal').style.display = 'block'; setTimeout(function(){ document.getElementById('registerUsername').focus(); }, 100);">Register</button>

<!-- Modální okno -->
<div id="registerModal" style="display:none; position:fixed; top:15%; left:50%; transform:translateX(-50%); background:#fff; border:1px solid #ccc; padding:20px; z-index:1000; box-shadow:0 0 10px rgba(0,0,0,0.5);">
  <form method="POST" action="register_submit.php">
    <h3>Register</h3>
    <p>Please check your email and confirm, that you want activate your account</p>
    <?php if (isset($_SESSION['register_success'])): ?>
      <p style="color:green;"><?= $_SESSION['register_success'] ?></p>
      <?php unset($_SESSION['register_success']); ?>
    <?php elseif (isset($_SESSION['register_error'])): ?>
      <p style="color:red;"><?= $_SESSION['register_error'] ?></p>
      <?php unset($_SESSION['register_error']); ?>
    <?php endif; ?>

    <input type="text" id="registerUsername" name="username" placeholder="Username" required><br><br>
    <input type="email" name="email" placeholder="Email" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <button type="submit">Register</button>
    <button type="button" onclick="document.getElementById('registerModal').style.display='none'">Cancel</button>
  </form>
</div>


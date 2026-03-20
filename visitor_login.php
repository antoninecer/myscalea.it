<?php
include 'header.php';
include 'menu.php';
?>

<section class="section">
  <h1>Visitor Login</h1>
  <form method="POST" action="visitor_login_submit.php" style="max-width: 400px;">
    <label for="email">Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label for="password">Password:</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Log In</button>
  </form>
</section>

<?php include 'footer.php'; ?>


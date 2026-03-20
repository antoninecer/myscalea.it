<?php
session_start();
?>

<form method="POST" action="client_login_submit.php" style="max-width:400px;margin:auto">
  <h3>Přihlášení klienta</h3>
  <input type="email" name="email" placeholder="Email" required><br><br>
  <input type="password" name="password" placeholder="Heslo" required><br><br>
  <button type="submit">Přihlásit</button>
</form>

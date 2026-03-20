<?php
include 'header.php';
include 'menu.php';
?>

<section class="section">
  <h1>Visitor Registration</h1>
  <p>Please fill in your details to create an account and enjoy loyalty rewards on future bookings.</p>

  <form method="POST" action="visitor_register_submit.php" style="max-width: 500px;">
    <label for="name">Full Name:</label><br>
    <input type="text" name="name" required><br><br>

    <label for="email">Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label for="phone">Phone:</label><br>
    <input type="text" name="phone"><br><br>

    <label for="password">Password:</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Register</button>
  </form>
</section>

<?php include 'footer.php'; ?>

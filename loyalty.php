<?php
include 'header.php';
include 'menu.php';
?>

<section class="section">
  <h1>Loyalty Program</h1>
  <p>Our loyalty program rewards visitors based on their stay history and positive reviews. The more you visit and the better your conduct, the greater your benefits!</p>

  <table border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse; margin-top: 20px;">
    <thead style="background-color: #f0f0f0;">
      <tr>
        <th>Status</th>
        <th>Completed Visits</th>
        <th>Positive Reviews</th>
        <th>Discount per Night</th>
        <th>Perks</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>New</td>
        <td>0–4</td>
        <td>—</td>
        <td>0 €</td>
        <td>Standard booking</td>
      </tr>
      <tr>
        <td>Silver</td>
        <td>5–14</td>
        <td>at least 3</td>
        <td>5 €</td>
        <td>Priority communication</td>
      </tr>
      <tr>
        <td>Gold</td>
        <td>15–29</td>
        <td>at least 5</td>
        <td>8 €</td>
        <td>Late checkout on request</td>
      </tr>
      <tr>
        <td>Platinum</td>
        <td>30+</td>
        <td>at least 10</td>
        <td>12 €</td>
        <td>Exclusive offers and gifts</td>
      </tr>
      <tr style="background-color: #ffe0e0;">
        <td>Banned</td>
        <td>—</td>
        <td>—</td>
        <td>0 €</td>
        <td>No bookings allowed</td>
      </tr>
    </tbody>
  </table>

  <p style="margin-top: 20px;">Statuses are upgraded automatically based on your reservation and review history. We appreciate respectful guests and reward loyalty generously!</p>
</section>

<?php include 'footer.php'; ?>

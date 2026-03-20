<?php
session_start();
require_once 'inc/connect.php';
?>

<?php include 'header.php'; ?>
<?php include 'menu.php'; ?>

<header class="main-header">
  <h1>Property Preferences</h1>
  <p>Share your vision and we'll help you find your ideal property in Scalea and its surroundings.</p>
</header>

<section class="section">
  <form id="clientForm" method="POST" action="client_request_submit.php" style="max-width:500px;margin:2rem auto;padding:1rem;background:#f9f9f9;border-radius:10px;box-shadow:0 0 10px rgba(0,0,0,0.1);font-family:sans-serif">
    <h2 style="text-align:center;margin-bottom:1rem;">Your Property Profile</h2>

    <label for="name">Name *</label>
    <input type="text" name="name" id="name" required style="width:100%;padding:0.5rem;margin-bottom:1rem;">

    <label for="email">Email *</label>
    <input type="email" name="email" id="email" required style="width:100%;padding:0.5rem;margin-bottom:1rem;">

    <label for="password">Password *</label>
    <input type="password" name="password" id="password" required style="width:100%;padding:0.5rem;margin-bottom:1rem;">

    <label for="phone">Phone *</label>
    <input type="tel" name="phone" id="phone" required pattern="^\+?[0-9\s\-\(\)]{7,}$" title="Enter a valid phone number (digits, spaces, +, -, ())." style="width:100%;padding:0.5rem;margin-bottom:1rem;">

    <label for="rooms">Number of Rooms *</label>
    <select name="rooms" id="rooms" style="width:100%;padding:0.5rem;margin-bottom:1rem;">
      <option value="1+">1+</option>
      <option value="2+">2+</option>
      <option value="3+">3+</option>
      <option value="4+">4+</option>
    </select>

    <label for="area">Minimum Area (m²) *</label>
    <input type="number" name="area" id="area" min="10" max="300" required style="width:100%;padding:0.5rem;margin-bottom:0.5rem;">
    <p style="font-size:0.9rem;color:#555;margin-bottom:1rem;">Typical market price is around 1,000&nbsp;EUR/m². Below we estimate based on 1,250&nbsp;EUR/m².</p>

    <label for="location">Preferred Location *</label>
    <select name="location" id="location" style="width:100%;padding:0.5rem;margin-bottom:1rem;">
      <option value="center">Center</option>
      <option value="outskirts">Outskirts</option>
      <option value="sea_front">Sea Front</option>
    </select>

    <label for="budget">Estimated Budget</label>
    <input type="text" name="budget_display" id="budget" readonly style="width:100%;padding:0.5rem;margin-bottom:1rem;background:#e9ecef;">

    <input type="hidden" name="requirements" id="requirements">

    <button type="submit" style="width:100%;padding:0.7rem;background:#007bff;color:white;border:none;border-radius:5px;font-size:1rem;cursor:pointer;">
      Submit Preferences
    </button>
  </form>
</section>

<?php include 'footer.php'; ?>

<script>
(function() {
  const areaInput = document.getElementById('area');
  const budgetInput = document.getElementById('budget');
  const requirementsField = document.getElementById('requirements');
  const form = document.getElementById('clientForm');
  const marketRate = 1000; // EUR/m² typical market rate
  const estimateRate = 1250; // EUR/m² estimated cost

  function updateBudget() {
    const area = parseFloat(areaInput.value);
    if (!isNaN(area)) {
      const estimated = Math.round(area * estimateRate);
      const marketMin = Math.round(area * marketRate);
      budgetInput.value = `${estimated} EUR (market ~${marketMin} EUR)`;
    } else {
      budgetInput.value = '';
    }
  }

  areaInput.addEventListener('input', updateBudget);

  form.addEventListener('submit', function(e) {
    const rooms = document.getElementById('rooms').value;
    const area = parseFloat(areaInput.value);
    const location = document.getElementById('location').value;

    // Use the estimated calculation directly instead of parsing the display
    const budget = Math.round(area * estimateRate);

    const note = area && budget < area * marketRate
      ? 'budget below typical market rate'
      : '';

    const req = { rooms, area, location, budget, note };
    requirementsField.value = JSON.stringify(req);
  });
})();
</script>

<?php
session_start();
require 'inc/connect.php';

$propertyId = intval($_POST['property_id'] ?? $_GET['id'] ?? 0);
if (!$propertyId) {
    exit('Missing property ID');
}

$stmt = $pdo->prepare("SELECT * FROM properties WHERE property_id = ?");
$stmt->execute([$propertyId]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$property) {
    exit('Property not found');
}

$calendarId = $property['calendar_id'] ?? '';
$icalUrl = 'https://calendar.google.com/calendar/ical/' . urlencode($calendarId) . '/public/basic.ics';
$icalData = @file_get_contents($icalUrl);

$disabled = [];
if ($icalData !== false) {
    preg_match_all('/DTSTART(?:;VALUE=DATE)?:(\d{8})\s*DTEND(?:;VALUE=DATE)?:(\d{8})/', $icalData, $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
        $start = DateTime::createFromFormat('Ymd', $match[1]);
        $end = DateTime::createFromFormat('Ymd', $match[2]);
        $end->modify('-1 day');
        while ($start <= $end) {
            $disabled[] = $start->format('Y-m-d');
            $start->modify('+1 day');
        }
    }
}
$disabled = array_unique($disabled);
$disabledJson = json_encode(array_values($disabled));

$visitorLoggedIn = isset($_SESSION['visitor_id']);
include 'header.php';
include 'menu.php';
?>

<section class="section">
  <h1><?= htmlspecialchars($property['name']) ?></h1>
  <p><?= htmlspecialchars($property['address']) ?></p>
  <h2>📅 Kalendář dostupnosti s cenami</h2>
  <p>Zelená = k dispozici, červená = obsazeno. Ceny jsou orientační za noc (standardní sazba). Pro slevy se přihlaste jako návštěvník.</p>

  <div style="display: flex; gap: 2em; align-items: flex-start;">
    <div>
      <input type="text" id="calendar-view" placeholder="Vyberte datum" readonly style="width: 100%; padding: 10px; font-size: 1.2em;">
      <input type="number" id="guests" placeholder="Počet osob" value="2" min="1" max="<?= (int)$property['max_occupancy'] ?>" style="margin-top:10px; padding: 10px; width: 100%;">

      <button onclick="calculatePrice()" style="margin-top:10px; padding: 10px; width: 100%;">Spočítat cenu</button>
    </div>
    <div id="price-result" style="min-width: 300px; max-width: 400px; font-family: monospace;"></div>
  </div>

  <?php if ($visitorLoggedIn): ?>
  <div id="reservation-form" style="margin-top: 20px; display: none;">
    <h3>Complete your reservation</h3>
    <form method="POST" action="submit_reservation.php">
      <input type="hidden" name="range" id="res-range">
      <input type="hidden" name="guests" id="res-guests">
      <input type="hidden" name="property_id" value="<?= $propertyId ?>">
      <input type="hidden" name="total" id="res-total">

      <button type="submit">Submit Reservation</button>
    </form>
  </div>
  <?php else: ?>
  <div style="margin-top: 20px;">
    <p><strong>Pro dokončení rezervace se prosím <a href="visitor_login.php">přihlaste</a> nebo <a href="visitor_register.php">zaregistrujte</a>.</strong></p>
  </div>
  <?php endif; ?>
</section>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<style>
  .flatpickr-calendar {
    font-size: 16px;
  }
  .flatpickr-day {
    border-radius: 0 !important;
    height: 70px !important;
    width: 70px !important;
    font-size: 16px;
    line-height: 1.2;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 2px;
    position: relative;
  }
  .flatpickr-day.busy-day {
    background-color: #ffdddd !important;
    color: #a00 !important;
    cursor: not-allowed;
  }
  .flatpickr-day.free-day {
    background-color: #ddffdd !important;
    color: #060 !important;
  }
  .day-date {
    font-weight: bold;
    font-size: 16px;
  }
  .day-price {
    font-size: 12px;
    color: #555;
  }
</style>

<script>
const disabledDates = <?= $disabledJson ?>;
let priceMap = {};

fetch('get_prices.php')
  .then(response => response.json())
  .then(data => {
    priceMap = data;
    initCalendar();
  });

  function initCalendar() {
  flatpickr("#calendar-view", {
    inline: true,
    mode: 'range',
    dateFormat: 'Y-m-d',
    disable: disabledDates,
    minDate: new Date(), // ⬅️ nastaví dynamicky dnešní datum
    onDayCreate: function(dObj, dStr, fp, dayElem) {
      const d = dayElem.dateObj;
      const date = d.toISOString().split('T')[0];
      dayElem.innerHTML = '';

      const dateDiv = document.createElement('div');
      dateDiv.className = 'day-date';
      dateDiv.innerText = d.getDate();
      dateDiv.setAttribute('translate', 'no');

      const price = priceMap[date];
      const priceDiv = document.createElement('div');
      priceDiv.className = 'day-price';
      priceDiv.innerText = price ? '€' + Math.round(price) : '';
      priceDiv.setAttribute('translate', 'no');

      dayElem.appendChild(dateDiv);
      dayElem.appendChild(priceDiv);

      if (disabledDates.includes(date)) {
        dayElem.classList.add("busy-day");
      } else {
        dayElem.classList.add("free-day");
      }
    }
  });
}

function calculatePrice() {
  const range = document.querySelector("#calendar-view").value;
  const guests = parseInt(document.querySelector("#guests").value) || 1;

  if (!range.includes(" to ")) {
    alert("Vyberte celý rozsah dat (od - do)");
    return;
  }

  fetch(`calculate_price.php?range=${encodeURIComponent(range)}&guests=${guests}&property_id=<?= $propertyId ?>`)
    .then(res => res.text())
    .then(html => {
      document.getElementById("price-result").innerHTML = html;
      <?php if ($visitorLoggedIn): ?>
      document.getElementById("reservation-form").style.display = 'block';
      document.getElementById("res-range").value = range;
      document.getElementById("res-guests").value = guests;
      const totalMatch = html.match(/Total amount:\s*([\d,.]+)/);
      if (totalMatch) {
        document.getElementById("res-total").value = totalMatch[1].replace(',', '.');
      }
      <?php endif; ?>
    });
}
</script>

<?php include 'footer.php'; ?>
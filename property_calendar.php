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
$icalUrl = '';
$icalData = false;

if (!empty($calendarId)) {
    $icalUrl = 'https://calendar.google.com/calendar/ical/' . urlencode($calendarId) . '/public/basic.ics';
    $icalData = @file_get_contents($icalUrl);
}

$busyDates = [];
$pendingDates = [];

/**
 * 1) Obsazené termíny z Google kalendáře
 */
if ($icalData !== false) {
    preg_match_all('/DTSTART(?:;VALUE=DATE)?:(\d{8})\s*DTEND(?:;VALUE=DATE)?:(\d{8})/', $icalData, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        $start = DateTime::createFromFormat('Ymd', $match[1]);
        $end = DateTime::createFromFormat('Ymd', $match[2]);

        if ($start && $end) {
            $end->modify('-1 day'); // checkout den neblokujeme

            while ($start <= $end) {
                $busyDates[] = $start->format('Y-m-d');
                $start->modify('+1 day');
            }
        }
    }
}

/**
 * 2) Rezervace z DB
 * pending = čeká na potvrzení/platbu
 * confirmed = potvrzená rezervace
 */
$stmt = $pdo->prepare("
    SELECT date_from, date_to, status_code
    FROM reservations
    WHERE property_id = ?
      AND status_code IN ('pending', 'confirmed')
");
$stmt->execute([$propertyId]);
$dbReservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($dbReservations as $res) {
    $start = DateTime::createFromFormat('Y-m-d', $res['date_from']);
    $end = DateTime::createFromFormat('Y-m-d', $res['date_to']);

    if ($start && $end) {
        $end->modify('-1 day'); // checkout den neblokujeme

        while ($start <= $end) {
            $day = $start->format('Y-m-d');

            if ($res['status_code'] === 'pending') {
                $pendingDates[] = $day;
            } else {
                $busyDates[] = $day;
            }

            $start->modify('+1 day');
        }
    }
}

$busyDates = array_values(array_unique($busyDates));
$pendingDates = array_values(array_unique($pendingDates));
$disabled = array_values(array_unique(array_merge($busyDates, $pendingDates)));

$busyJson = json_encode($busyDates);
$pendingJson = json_encode($pendingDates);
$disabledJson = json_encode($disabled);

$visitorLoggedIn = isset($_SESSION['visitor_id']);

include 'header.php';
include 'menu.php';
?>

<section class="section">
  <h1><?= htmlspecialchars($property['name']) ?></h1>
  <p><?= htmlspecialchars($property['address']) ?></p>
  <h2>📅 Kalendář dostupnosti s cenami</h2>
  <p>
    Zelená = k dispozici,
    žlutá = čeká na potvrzení,
    červená = obsazeno.
    Ceny jsou orientační za noc (standardní sazba). Pro slevy se přihlaste jako návštěvník.
  </p>

  <div style="display: flex; gap: 2em; align-items: flex-start; flex-wrap: wrap;">
    <div>
      <input
        type="text"
        id="calendar-view"
        placeholder="Vyberte datum"
        readonly
        style="width: 100%; padding: 10px; font-size: 1.2em;"
      >

      <label for="guests" style="display:block; margin-top:10px; font-weight:bold;">
  Počet osob
</label>
<input
  type="number"
  id="guests"
  placeholder="Počet osob"
  value="2"
  min="1"
  max="<?= (int)$property['max_occupancy'] ?>"
  style="padding: 10px; width: 100%;"
>

      <button onclick="calculatePrice()" style="margin-top:10px; padding: 10px; width: 100%;">
        Spočítat cenu
      </button>
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

  .flatpickr-day.pending-day {
    background-color: #fff3b0 !important;
    color: #8a6d00 !important;
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
const busyDates = <?= $busyJson ?>;
const pendingDates = <?= $pendingJson ?>;
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
    minDate: new Date(),
    onDayCreate: function(dObj, dStr, fp, dayElem) {
      const d = dayElem.dateObj;
      //const date = d.toISOString().split('T')[0];
      const date =
  d.getFullYear() + '-' +
  String(d.getMonth() + 1).padStart(2, '0') + '-' +
  String(d.getDate()).padStart(2, '0');
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

      if (pendingDates.includes(date)) {
        dayElem.classList.add("pending-day");
      } else if (busyDates.includes(date)) {
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
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
$visitor = null;

if ($visitorLoggedIn) {
    $visitorStmt = $pdo->prepare("SELECT name, email, phone FROM visitors WHERE id = ?");
    $visitorStmt->execute([$_SESSION['visitor_id']]);
    $visitor = $visitorStmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

$maxOccupancy = max(1, (int)($property['max_occupancy'] ?? 6));

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
    Cena se počítá po jednotlivých nocích podle sezóny a počtu osob.
  </p>

  <div class="booking-layout">
    <div class="booking-calendar-panel">
      <input
        type="text"
        id="calendar-view"
        placeholder="Vyberte datum"
        readonly
        class="booking-input"
      >

      <label for="guests" class="booking-label">
        Počet osob
      </label>
      <input
        type="number"
        id="guests"
        placeholder="Počet osob"
        value="2"
        min="1"
        max="<?= $maxOccupancy ?>"
        class="booking-input"
      >

      <button type="button" onclick="calculatePrice()" class="booking-button">
        Spočítat cenu
      </button>
    </div>

    <div id="price-result" class="booking-price-result"></div>
  </div>

  <div id="reservation-form" class="booking-reservation-box" style="display: none;">
    <h3>Rezervace bez registrace</h3>
    <p>
      Vyplňte kontaktní údaje. Termín se uloží jako předběžná rezervace a platební instrukce dorazí e-mailem.
    </p>

    <?php if ($visitorLoggedIn && $visitor): ?>
      <p class="booking-note">
        Jste přihlášen(a) jako <?= htmlspecialchars($visitor['name']) ?>.
        Údaje můžete upravit jen pro tuto rezervaci.
      </p>
    <?php endif; ?>

    <form method="POST" action="submit_reservation.php" class="booking-form">
      <input type="hidden" name="range" id="res-range">
      <input type="hidden" name="guests" id="res-guests">
      <input type="hidden" name="property_id" value="<?= $propertyId ?>">

      <label for="guest_name">Jméno a příjmení</label>
      <input
        type="text"
        name="guest_name"
        id="guest_name"
        required
        value="<?= htmlspecialchars($visitor['name'] ?? '') ?>"
      >

      <label for="guest_email">E-mail</label>
      <input
        type="email"
        name="guest_email"
        id="guest_email"
        required
        value="<?= htmlspecialchars($visitor['email'] ?? '') ?>"
      >

      <label for="guest_phone">Telefon / WhatsApp</label>
      <input
        type="text"
        name="guest_phone"
        id="guest_phone"
        value="<?= htmlspecialchars($visitor['phone'] ?? '') ?>"
      >

      <label for="guest_note">Poznámka</label>
      <textarea
        name="guest_note"
        id="guest_note"
        rows="4"
        placeholder="Čas příjezdu, počet dětí, dotaz k apartmánu..."
      ></textarea>

      <label class="booking-checkbox">
        <input type="checkbox" name="privacy_agree" value="1" required>
        Souhlasím se zpracováním údajů pro vyřízení rezervace.
      </label>

      <button type="submit" class="booking-button booking-button-primary">
        Odeslat předběžnou rezervaci
      </button>
    </form>
  </div>
</section>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<style>
  .booking-layout {
    display: flex;
    gap: 2em;
    align-items: flex-start;
    flex-wrap: wrap;
  }

  .booking-calendar-panel {
    min-width: 280px;
    max-width: 100%;
  }

  .booking-input {
    width: 100%;
    padding: 10px;
    font-size: 1.05em;
    box-sizing: border-box;
  }

  .booking-label {
    display:block;
    margin-top:10px;
    font-weight:bold;
  }

  .booking-button {
    margin-top:10px;
    padding: 11px 16px;
    width: 100%;
    border: 0;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 700;
  }

  .booking-button-primary {
    background: #0f766e;
    color: white;
  }

  .booking-price-result {
    min-width: 300px;
    max-width: 460px;
    font-family: monospace;
    overflow-x: auto;
  }

  .booking-reservation-box {
    margin-top: 24px;
    padding: 18px;
    border: 1px solid #d7e5e2;
    border-radius: 16px;
    background: #f7fffd;
    max-width: 720px;
  }

  .booking-form label {
    display: block;
    margin-top: 12px;
    font-weight: 700;
  }

  .booking-form input[type="text"],
  .booking-form input[type="email"],
  .booking-form textarea {
    width: 100%;
    padding: 10px;
    box-sizing: border-box;
    border: 1px solid #cbd5d1;
    border-radius: 10px;
  }

  .booking-checkbox {
    display: flex !important;
    gap: 8px;
    align-items: flex-start;
    font-weight: 400 !important;
  }

  .booking-note {
    padding: 10px;
    background: #eef9f6;
    border-radius: 10px;
  }

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

  @media (max-width: 760px) {
    .booking-layout {
      display: block;
    }

    .booking-price-result {
      min-width: 0;
      max-width: 100%;
      margin-top: 16px;
    }

    .flatpickr-day {
      height: 52px !important;
      width: 52px !important;
      font-size: 14px;
    }

    .day-price {
      font-size: 10px;
    }
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
      document.getElementById("reservation-form").style.display = 'block';
      document.getElementById("res-range").value = range;
      document.getElementById("res-guests").value = guests;
      document.getElementById("reservation-form").scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
}
</script>

<?php include 'footer.php'; ?>

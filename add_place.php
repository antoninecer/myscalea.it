<?php
session_start();
require_once 'inc/connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: /");
    exit;
}

// Načtení kategorií z databáze
$stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add New Place</title>
  <link rel="stylesheet" href="/styles.css">
</head>
<body style="font-family:sans-serif; padding:20px; max-width:800px; margin:auto;">

<a href="/" class="cta-button">← Back to main page</a>

<h2>Add New Place</h2>
<form method="POST" action="save_place.php" enctype="multipart/form-data" onsubmit="return serializeOpeningHours()">

  <label>Name:</label>
  <input type="text" name="name" required><br>

  <label>Category:</label>
  <select name="category_id" required>
    <option value="">-- Select Category --</option>
    <?php foreach ($categories as $cat): ?>
      <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
    <?php endforeach; ?>
  </select><br>

  <label>Description:</label>
  <textarea name="description" rows="4" style="width:100%;"></textarea><br>

  <label>Address:</label>
  <input type="text" id="address" placeholder="e.g. Via Roma, Scalea" style="width:70%;">
  <button type="button" onclick="getCoords()">📍 Get</button><br>
  <input type="text" id="latlon" placeholder="Paste coordinates (e.g. 39.80, 15.80)" onblur="parseLatLon()" style="width:100%;"><br>
  <input type="text" name="latitude" id="latitude" placeholder="Lat">
  <input type="text" name="longitude" id="longitude" placeholder="Lon"><br>

  <label>Website:</label>
  <input type="url" name="website"><br>

  <label>Phone:</label>
  <input type="text" name="phone"><br>

  <label>Email:</label>
  <input type="email" name="email"><br>

  <label>Upload Image:</label>
  <input type="file" name="photo" accept="image/*"><br>

  <label>Opening Hours:</label><br>
  <?php foreach (["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"] as $day): ?>
    <label><input type="checkbox" class="day-check" data-day="<?= $day ?>"> <?= substr($day, 0, 3) ?></label>
  <?php endforeach; ?>
  <div style="margin-top:10px;">
    <label>From: <input type="time" id="time-from"></label>
    <label>To: <input type="time" id="time-to"></label><br>
    <label>Split From: <input type="time" id="split-from"></label>
    <label>To: <input type="time" id="split-to"></label>
    <button type="button" onclick="applyToSelectedDays()">Apply to selected days</button>
  </div>

  <div id="hoursPreview" style="margin-top:10px; padding:10px; border:1px solid #ccc;"></div>

  <input type="hidden" name="opening_hours" id="opening_hours_json">

  <br>
  <button type="submit">Save Place</button>
</form>
<script>
const hoursData = {};

function applyToSelectedDays() {
  const from = document.getElementById('time-from').value;
  const to = document.getElementById('time-to').value;
  const splitFrom = document.getElementById('split-from').value;
  const splitTo = document.getElementById('split-to').value;
  const preview = document.getElementById('hoursPreview');

  document.querySelectorAll('.day-check:checked').forEach(cb => {
    const day = cb.dataset.day;
    hoursData[day] = []; // Přepisujeme původní hodnoty

    if (from && to) {
      hoursData[day].push({ from, to });
    }
    if (splitFrom && splitTo) {
      hoursData[day].push({ from: splitFrom, to: splitTo });
    }
  });

  // Aktualizace náhledu
  preview.innerHTML = '';
  Object.keys(hoursData).forEach(day => {
    const times = hoursData[day].map(t => `${t.from}–${t.to}`).join(' + ');
    preview.innerHTML += `<div><b>${day}:</b> ${times}</div>`;
  });
}

function serializeOpeningHours() {
  document.getElementById('opening_hours_json').value = JSON.stringify(hoursData);
  return true;
}

function getCoords() {
  const addr = document.getElementById('address').value;
  if (!addr) return alert("Please enter an address first");
  fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(addr)}`)
    .then(res => res.json())
    .then(data => {
      if (data.length > 0) {
        document.getElementById('latitude').value = data[0].lat;
        document.getElementById('longitude').value = data[0].lon;
        document.getElementById('latlon').value = `${data[0].lat}, ${data[0].lon}`;
      } else {
        alert("Location not found");
      }
    });
}

function parseLatLon() {
  const input = document.getElementById('latlon').value;
  const parts = input.split(',');
  if (parts.length === 2) {
    const lat = parseFloat(parts[0]);
    const lon = parseFloat(parts[1]);
    if (!isNaN(lat) && !isNaN(lon)) {
      document.getElementById('latitude').value = lat;
      document.getElementById('longitude').value = lon;
    }
  }
}
</script>

</body>
</html>


<?php
require_once 'inc/connect.php';

// Získání kategorií z databáze
$stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div id="addPlaceModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:1000;">
  
  <!--<div style="background:#fff; margin:5% auto; padding:20px; max-width:750px; border-radius:10px; position:relative; max-height:90vh; overflow-y:auto; font-family:sans-serif;">
-->
  <div style="background:#fff; margin:5% auto; padding:20px; max-width:750px; border-radius:10px; position:relative; height:90vh; overflow-y:auto; font-family:sans-serif; touch-action:manipulation;">

    <button onclick="document.getElementById('addPlaceModal').style.display='none'" style="position:absolute;top:10px;right:10px;font-size:20px;">&times;</button>
    <form method="POST" action="save_place.php" enctype="multipart/form-data" onsubmit="return serializeOpeningHours()">
      <h2 style="margin-top:0; margin-bottom:15px;">Add New Place</h2>

      <label>Name:</label>
      <input type="text" name="name" required>

      <label>Category:</label>
      <select name="category_id" required>
        <option value="">-- Select Category --</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>

      <label>Description:</label>
      <textarea name="description"></textarea>

      <label>Address & Coordinates:</label>
      <div style="display:flex; gap:10px; margin-bottom:5px;">
        <input type="text" id="address" placeholder="e.g. Via Roma, Scalea" style="flex:2;">
        <button type="button" onclick="getCoords()">📍 Get</button>
      </div>
      <input type="text" id="latlon" placeholder="Paste coordinates (e.g. 39.80, 15.80) or fill automatically" onblur="parseLatLon()">
      <div style="display:flex; gap:10px;">
        <input type="text" name="latitude" id="latitude" placeholder="Lat" style="flex:1;">
        <input type="text" name="longitude" id="longitude" placeholder="Lon" style="flex:1;">
      </div>

      <label>Website:</label>
      <input type="url" name="website">

      <label>Phone:</label>
      <input type="text" name="phone">

      <label>Email:</label>
      <input type="email" name="email">

      <label>Upload Image:</label>
      <input type="file" name="photo" accept="image/*">

      <label>Opening Hours:</label>
      <div style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:10px;">
        <?php foreach (["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"] as $day): ?>
          <label style="width: 90px;"><input type="checkbox" class="day-check" data-day="<?= $day ?>"> <?= substr($day, 0, 3) ?></label>
        <?php endforeach; ?>
      </div>
      <div style="display:flex; flex-direction:column; gap:10px; margin-bottom:10px;">
        <div style="display:flex; gap:10px; align-items:center;">
          <label>From: <input type="time" id="time-from"></label>
          <label>To: <input type="time" id="time-to"></label>
        </div>
        <div style="display:flex; gap:10px; align-items:center;">
          <label>Split From: <input type="time" id="split-from"></label>
          <label>To: <input type="time" id="split-to"></label>
          <button type="button" onclick="applyToSelectedDays()">Apply to selected days</button>
        </div>
        <div id="hoursPreview" style="background:#f8f8f8; padding:10px; border:1px solid #ccc;"></div>
      </div>

      <input type="hidden" name="opening_hours" id="opening_hours_json">

      <div style="display:flex; justify-content:space-between; margin-top:20px;">
        <button type="submit">Save Place</button>
        <button type="button" onclick="document.getElementById('addPlaceModal').style.display='none'">Cancel</button>
      </div>
    </form>
  </div>
</div>

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
    if (!hoursData[day]) hoursData[day] = [];

    if (from && to) {
      hoursData[day].push({ from, to });
    }
    if (splitFrom && splitTo) {
      hoursData[day].push({ from: splitFrom, to: splitTo });
    }
  });

  // Přegeneruj náhled
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


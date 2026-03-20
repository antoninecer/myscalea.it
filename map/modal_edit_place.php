<?php
require_once '../inc/connect.php';

// Získání kategorií z databáze
$stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div id="editPlaceModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:1000;">
  <div style="background:#fff; margin:5% auto; padding:20px; max-width:750px; border-radius:10px; position:relative; height:90vh; overflow-y:auto; font-family:sans-serif; touch-action:manipulation;">
  
    <button onclick="document.getElementById('editPlaceModal').style.display='none'" style="position:absolute;top:10px;right:10px;font-size:20px;">&times;</button>
    <form method="POST" action="../update_place.php" enctype="multipart/form-data" onsubmit="return serializeOpeningHoursEdit()">
      <h2 style="margin-top:0; margin-bottom:15px;">Edit Place</h2>

      <input type="hidden" name="id" id="edit-id">

      <label>Name:</label>
      <input type="text" name="name" id="edit-name" required>

      <label>Category:</label>
      <select name="category_id" id="edit-category" required>
        <option value="">-- Select Category --</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>

      <label>Description:</label>
      <textarea name="description" id="edit-description" style="height:120px;"></textarea>

      <label>Address & Coordinates:</label>
      <div style="display:flex; gap:10px; margin-bottom:5px;">
        <input type="text" name="address" id="edit-address" placeholder="e.g. Via Roma, Scalea" style="flex:2;">
        <button type="button" onclick="getCoordsEdit()">📍 Get</button>
      </div>
      <input type="text" id="edit-latlon" placeholder="Paste coordinates (e.g. 39.80, 15.80) or fill automatically" onblur="parseLatLonEdit()">
      <div style="display:flex; gap:10px;">
        <input type="text" name="latitude" id="edit-latitude" placeholder="Lat" style="flex:1;">
        <input type="text" name="longitude" id="edit-longitude" placeholder="Lon" style="flex:1;">
      </div>

      <label>Website:</label>
      <input type="url" name="website" id="edit-website">

      <label>Phone:</label>
      <input type="text" name="phone" id="edit-phone">

      <label>Email:</label>
      <input type="email" name="email" id="edit-email">

      <label>Upload New Image:</label>
      <input type="file" name="photo" accept="image/*">

      <label>Opening Hours:</label>
      <div style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:10px;">
        <?php foreach (["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"] as $day): ?>
          <label style="width: 90px;"><input type="checkbox" class="edit-day-check" data-day="<?= $day ?>"> <?= substr($day, 0, 3) ?></label>
        <?php endforeach; ?>
      </div>
      <div style="display:flex; flex-direction:column; gap:10px; margin-bottom:10px;">
        <div style="display:flex; gap:10px; align-items:center;">
          <label>From: <input type="time" id="edit-time-from"></label>
          <label>To: <input type="time" id="edit-time-to"></label>
        </div>
        <div style="display:flex; gap:10px; align-items:center;">
          <label>Split From: <input type="time" id="edit-split-from"></label>
          <label>To: <input type="time" id="edit-split-to"></label>
          <button type="button" onclick="applyToSelectedDaysEdit()">Apply to selected days</button>
        </div>
        <div id="edit-hoursPreview" style="background:#f8f8f8; padding:10px; border:1px solid #ccc; min-height: 60px;"></div>
      </div>

      <input type="hidden" name="opening_hours" id="edit_opening_hours_json">

      <div style="display:flex; justify-content:space-between; margin-top:20px;">
        <button type="submit">Save Changes</button>
        <button type="button" onclick="document.getElementById('editPlaceModal').style.display='none'">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
let hoursData = {};
function applyToSelectedDaysEdit() {
  const from = document.getElementById('edit-time-from').value;
  const to = document.getElementById('edit-time-to').value;
  const splitFrom = document.getElementById('edit-split-from').value;
  const splitTo = document.getElementById('edit-split-to').value;
  const preview = document.getElementById('edit-hoursPreview');

  document.querySelectorAll('.edit-day-check:checked').forEach(cb => {
    const day = cb.dataset.day;
    if (!hoursData[day]) hoursData[day] = [];

    if (from && to) hoursData[day].push({ from, to });
    if (splitFrom && splitTo) hoursData[day].push({ from: splitFrom, to: splitTo });
  });

  // Obnovit náhled
  preview.innerHTML = Object.entries(hoursData).map(([day, times]) => {
    const text = times.map(t => `${t.from}–${t.to}`).join(' + ');
    return `<div><b>${day}:</b> ${text}</div>`;
  }).join('');
}


function serializeOpeningHoursEdit() {
  document.getElementById('edit_opening_hours_json').value = JSON.stringify(hoursData);
  return true;
}

function getCoordsEdit() {
  const addr = document.getElementById('address').value;
  if (!addr) return alert("Please enter an address first");
  fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(addr)}`)
    .then(res => res.json())
    .then(data => {
      if (data.length > 0) {
        document.getElementById('edit-latitude').value = data[0].lat;
        document.getElementById('edit-longitude').value = data[0].lon;
        document.getElementById('edit-latlon').value = `${data[0].lat}, ${data[0].lon}`;
      } else {
        alert("Location not found");
      }
    });
}

function parseLatLonEdit() {
  const input = document.getElementById('edit-latlon').value;
  const parts = input.split(',');
  if (parts.length === 2) {
    const lat = parseFloat(parts[0]);
    const lon = parseFloat(parts[1]);
    if (!isNaN(lat) && !isNaN(lon)) {
      document.getElementById('edit-latitude').value = lat;
      document.getElementById('edit-longitude').value = lon;
    }
  }
}
</script>


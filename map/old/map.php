<?php
require_once '../inc/connect.php';
include '../header.php';

// Načtení míst z tabulky `places`
$sql = "SELECT id, name, description, category_id, latitude, longitude, website, opening_hours FROM places";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$places = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Načtení kategorií, které mají přiřazené alespoň jedno místo
$sql = "SELECT c.id, c.name FROM categories c
        JOIN places p ON p.category_id = c.id
        GROUP BY c.id, c.name
        ORDER BY c.name";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link rel="stylesheet" href="/map/map.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://kit.fontawesome.com/a53c6e5e24.js" crossorigin="anonymous"></script>

<div id="burger"><i class="fas fa-bars"></i></div>
<div id="controls" style="display:none">
  <strong>Filter Categories:</strong>
  <?php foreach ($categories as $cat): ?>
    <label>
      <input type="checkbox" class="category-toggle" value="<?= $cat['id'] ?>" checked>
      <?= ucfirst($cat['name']) ?>
    </label>
  <?php endforeach; ?>
  <hr>
  <label>
    <input type="checkbox" id="onlyOpenToggle">
    Show only currently open
  </label>
</div>

<div id="weather">
  <i class="fas fa-cloud-sun"></i>
  <div id="weather-info">Načítání počasí...</div>
</div>

<div id="map"></div>

<script>
const map = L.map('map', {
  zoomControl: false
}).setView([39.8133, 15.7984], 14);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

L.control.zoom({ position: 'bottomleft' }).addTo(map);

const iconMap = {
  1: 'fa-utensils',
  2: 'fa-train-subway',
  3: 'fa-camera',
  4: 'fa-umbrella-beach',
  5: 'fa-hotel',
  6: 'fa-store',
  7: 'fa-star',
  8: 'fa-bus',
  9: 'fa-anchor',
  10: 'fa-plane',
};

const places = <?= json_encode($places); ?>;
const markers = [];

function isOpenNow(opening_hours) {
  if (!opening_hours) return true;
  try {
    const now = new Date();
    const day = now.toLocaleDateString('en-US', { weekday: 'long' });
    const parsed = JSON.parse(opening_hours);
    const today = parsed[day];
    if (!today) return false;
    const from = today.from;
    const to = today.to;
    const nowTime = now.getHours() + now.getMinutes()/60;
    const fromTime = parseFloat(from.split(':')[0]) + parseFloat(from.split(':')[1])/60;
    const toTime = parseFloat(to.split(':')[0]) + parseFloat(to.split(':')[1])/60;
    return nowTime >= fromTime && nowTime <= toTime;
  } catch (e) {
    return true;
  }
}

function renderMarkers() {
  markers.forEach(m => map.removeLayer(m));
  markers.length = 0;

  const selectedCategories = Array.from(document.querySelectorAll('.category-toggle:checked')).map(el => parseInt(el.value));
  const onlyOpen = document.getElementById('onlyOpenToggle').checked;

  places.forEach(place => {
    if (!selectedCategories.includes(place.category_id)) return;
    const isOpen = isOpenNow(place.opening_hours);
    if (onlyOpen && !isOpen) return;

    const iconClass = iconMap[place.category_id] || 'fa-map-marker-alt';
    const customIcon = L.divIcon({
      html: `<i class="fas ${iconClass} fa-marker"></i>`,
      iconSize: [30, 30],
      className: ''
    });

    const statusLabel = isOpen
      ? '<span style="color: green; font-weight: bold;">\ud83d\udfe2 Open</span>'
      : '<span style="color: red; font-weight: bold;">\ud83d\udd34 Closed</span>';

    let openingHtml = '';
    if (place.opening_hours) {
      try {
        const data = JSON.parse(place.opening_hours);
        const days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
        const today = new Date().toLocaleDateString('en-US', { weekday: 'long' });

        openingHtml += '<div class="opening-hours"><b>\ud83d\udd52 Opening hours:</b><br>';
        for (const day of days) {
          if (data[day]) {
            const line = `${day.padEnd(10)} ${data[day].from} - ${data[day].to}`;
            const highlight = (day === today) ? 'today' : '';
            openingHtml += `<div class="${highlight}">${line}</div>`;
          }
        }
        openingHtml += '</div>';
      } catch (e) {}
    }

    const popup = `
      <div>
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <b>${place.name}</b>
          ${statusLabel}
        </div>
        ${place.description ? `<div>${place.description}</div>` : ''}
        ${place.website ? `<div><a href="${place.website}" target="_blank">\ud83c\udf10 Website</a></div>` : ''}
        <div><a href="https://www.google.com/maps/dir/?api=1&destination=${place.latitude},${place.longitude}" target="_blank">\ud83e\uddfd Navigate</a></div>
        ${openingHtml}
      </div>
    `;

    const marker = L.marker([place.latitude, place.longitude], { icon: customIcon })
                    .addTo(map)
                    .bindPopup(popup);
    markers.push(marker);
  });
}

renderMarkers();
document.querySelectorAll('.category-toggle').forEach(cb => cb.addEventListener('change', renderMarkers));
document.getElementById('onlyOpenToggle').addEventListener('change', renderMarkers);
document.getElementById('burger').addEventListener('click', () => {
  const c = document.getElementById('controls');
  c.style.display = c.style.display === 'none' ? 'block' : 'none';
});

async function fetchWeather() {
  try {
    const response = await fetch('https://wttr.in/Scalea?format=j1');
    const data = await response.json();
    const current = data.current_condition[0];
    const weatherDesc = current.weatherDesc[0].value;
    const tempC = current.temp_C;
    const windSpeed = current.windspeedKmph;
    const time = new Date().toLocaleTimeString('cs-CZ', { hour: '2-digit', minute: '2-digit' });

    document.getElementById('weather-info').innerHTML = `
      ${time} – ${weatherDesc}, ${tempC}°C, wind ${windSpeed} km/h
    `;
  } catch (error) {
    document.getElementById('weather-info').textContent = 'Počasí se nepodařilo načíst.';
  }
}

fetchWeather();
setInterval(fetchWeather, 600000);
</script>

<?php include 'footer.php'; ?>


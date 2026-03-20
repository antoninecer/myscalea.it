<?php
require_once 'header.php';
// Načtení míst z tabulky `places`
$sql = "SELECT id, name, description, category_id, latitude, longitude, website, opening_hours, phone, email, address, property_id FROM places";
//$sql = "SELECT id, name, description, category_id, latitude, longitude, website, opening_hours, phone, email, address FROM places";
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

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>MyScalea Map</title>

  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <link rel="stylesheet" href="map.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://kit.fontawesome.com/a53c6e5e24.js" crossorigin="anonymous"></script>
  <style>
    body.modal-open {
      overflow: hidden;
    }
    #burger {
      position: absolute;
      top: 10px;
      left: 10px;
      font-size: 24px;
      background: white;
      padding: 6px 10px;
      border-radius: 5px;
      cursor: pointer;
      z-index: 1001;
    }
#sideMenu {
  position: fixed;
  top: 0;
  left: 0;
  width: 260px;
  height: 100%;
  background: #fff;
  box-shadow: 2px 0 10px rgba(0,0,0,0.2);
  padding: 20px;
  transition: transform 0.3s ease;
  z-index: 1000;
  overflow-y: auto;
  transform: translateX(-100%); /* defaultně schované */
}

#sideMenu.open {
  transform: translateX(0); /* vysunuté */
}
    #sideMenu h3 {
      margin-top: 0;
    }
    #weather-toggle {
      font-size: 14px;
      margin-top: 10px;
      display: block;
    }
    #map { position: absolute; top: 0; bottom: 0; right: 0; left: 0; }
  </style>
</head>

<body>
<div id="burger"><i class="fas fa-bars"></i></div>
<div id="sideMenu">
  <h3>MyScalea</h3>
  <a href="/">🏠 Back to Main Page</a>
  <hr>
<input type="text" id="searchInput" placeholder="Search places..." style="width: 100%; padding: 5px; margin: 10px 0;">
<p class="search-hint">
    ⚠️ Search works only on original English names/descriptions.
  </p>

  <strong>📂 Categories:</strong><br>
  <?php foreach ($categories as $cat): ?>
    <label>
      <input type="checkbox" class="category-toggle" value="<?= $cat['id'] ?>" checked>
      <?= ucfirst($cat['name']) ?>
    </label><br>
  <?php endforeach; ?>
  <hr>
  <label>
    <input type="checkbox" id="onlyOpenToggle">
    Show only currently open
  </label><br>
  <label id="weather-toggle">
    <input type="checkbox" id="showWeatherCheckbox"> Show Weather
  </label>
</div>

<div id="weather">
  <i class="fas fa-cloud-sun"></i>
  <div id="weather-info">Načítání počasí...</div>
</div>

<div id="map"></div>

<?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') include 'modal_edit_place.php'; ?>

<script>

const isAdmin = <?= isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin' ? 'true' : 'false' ?>;

function setCookie(name, value, days) {
  let expires = "";
  if (days) {
    const date = new Date();
    date.setTime(date.getTime() + (days*24*60*60*1000));
    expires = "; expires=" + date.toUTCString();
  }
  document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}
function getCookie(name) {
  const nameEQ = name + "=";
  const ca = document.cookie.split(';');
  for(let i=0;i < ca.length;i++) {
    let c = ca[i];
    while (c.charAt(0)==' ') c = c.substring(1,c.length);
    if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
  }
  return null;
}

// Burger menu toggle
const burger = document.getElementById('burger');
const sideMenu = document.getElementById('sideMenu');
burger.addEventListener('click', () => {
  sideMenu.classList.toggle('open');
});

// Weather toggle from cookie
const weatherBox = document.getElementById('weather');
const weatherCheckbox = document.getElementById('showWeatherCheckbox');
weatherCheckbox.checked = getCookie('showWeather') !== '0';
weatherBox.style.display = weatherCheckbox.checked ? 'block' : 'none';
weatherCheckbox.addEventListener('change', () => {
  const show = weatherCheckbox.checked;
  weatherBox.style.display = show ? 'block' : 'none';
  setCookie('showWeather', show ? '1' : '0', 365);
});

const map = L.map('map', {
  zoomControl: false
}).setView([39.8133, 15.7984], 15);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

L.control.zoom({ position: 'bottomleft' }).addTo(map);

const iconMap = {
  1: 'fa-utensils',
  2: 'fa-train-subway',
  3: 'fa-camera',
  4: 'fa-umbrella-beach',
  5: 'fa-bed',
  6: 'fa-store',
  7: 'fa-star',
  8: 'fa-bus',
  9: 'fa-anchor',
  10: 'fa-plane',
  11: 'fa-building-columns',  // public_service
  12: 'fa-briefcase-medical', // health_care 
  13: 'fa-tower-observation', // landmark
  14: 'fa-hand-scissors', // hair_salon 
  15: 'fa-home-lg-alt' ,
  16: 'fa-credit-card',
  17: 'fa-hourglass',
  18: 'fa-car',
};

const places = <?= json_encode($places); ?>;
const markers = [];

function isOpenNow(opening_hours) {
  if (!opening_hours) return true;
  try {
    const now = new Date();
    const day = now.toLocaleDateString('en-US', { weekday: 'long' });
    const prevDay = new Date(now);
    prevDay.setDate(now.getDate() - 1);
    const prevDayName = prevDay.toLocaleDateString('en-US', { weekday: 'long' });
    const parsed = JSON.parse(opening_hours);
    const nowTime = now.getHours() + now.getMinutes() / 60;
    const todayHours = Array.isArray(parsed[day]) ? parsed[day] : (parsed[day] ? [parsed[day]] : []);
    for (const p of todayHours) {
      const fromTime = parseFloat(p.from.split(':')[0]) + parseFloat(p.from.split(':')[1]) / 60;
      const toTime = parseFloat(p.to.split(':')[0]) + parseFloat(p.to.split(':')[1]) / 60;
      if (fromTime <= toTime) {
        if (nowTime >= fromTime && nowTime <= toTime) return true;
      } else {
        if (nowTime >= fromTime || nowTime <= toTime) return true;
      }
    }
    const ydayHours = Array.isArray(parsed[prevDayName]) ? parsed[prevDayName] : (parsed[prevDayName] ? [parsed[prevDayName]] : []);
    for (const p of ydayHours) {
      const fromTime = parseFloat(p.from.split(':')[0]) + parseFloat(p.from.split(':')[1]) / 60;
      const toTime = parseFloat(p.to.split(':')[0]) + parseFloat(p.to.split(':')[1]) / 60;
      if (fromTime > toTime && nowTime <= toTime) return true;
    }
    return false;
  } catch (e) {
    return true;
  }
}
function openEditModal(place) {
  const modal = document.getElementById('editPlaceModal');
  modal.style.display = 'block';
  modal.querySelector('h2').textContent = 'Edit Place';
  modal.querySelector('form').action = 'update_place.php';

  // Reset formuláře
  //modal.querySelector('form').reset();
  //hoursData = {}; // <== DŮLEŽITÉ: smažeme předchozí data
// místo reset():
modal.querySelectorAll('input[type="text"], input[type="url"], input[type="email"], textarea').forEach(el => el.value = '');
modal.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);

  // Naplnění polí
  modal.querySelector('[name="name"]').value = place.name;
  modal.querySelector('[name="category_id"]').value = place.category_id;
  modal.querySelector('[name="description"]').value = place.description || '';
  modal.querySelector('#edit-address').value = place.address || '';
  modal.querySelector('#edit-latitude').value = place.latitude;
  modal.querySelector('#edit-longitude').value = place.longitude;
  modal.querySelector('#edit-latlon').value = `${place.latitude}, ${place.longitude}`;
  modal.querySelector('[name="website"]').value = place.website || '';
  modal.querySelector('[name="phone"]').value = place.phone || '';
  modal.querySelector('[name="email"]').value = place.email || '';

  // Otevírací doba
  try {
    const data = JSON.parse(place.opening_hours || '{}');
    Object.entries(data).forEach(([day, periods]) => {
      modal.querySelector(`.edit-day-check[data-day='${day}']`).checked = true;
      const times = Array.isArray(periods) ? periods : [periods];
      hoursData[day] = times;
    });

    // Vykreslit náhled do preview
    const preview = modal.querySelector('#edit-hoursPreview');
    preview.innerHTML = Object.entries(hoursData).map(([d, times]) => {
      const text = times.map(t => `${t.from}–${t.to}`).join(' + ');
      return `<div><b>${d}:</b> ${text}</div>`;
    }).join('');
  } catch (e) {
    console.log('Neplatný JSON opening_hours', e);
  }

  // Skryté ID
  if (!modal.querySelector('[name="id"]')) {
    const hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.name = 'id';
    modal.querySelector('form').appendChild(hidden);
  }
  modal.querySelector('[name="id"]').value = place.id;
}
function renderMarkers(filteredPlaces = null) {
  const placesToRender = Array.isArray(filteredPlaces) ? filteredPlaces : places;

  markers.forEach(m => map.removeLayer(m));
  markers.length = 0;

  const selectedCategories = Array.from(document.querySelectorAll('.category-toggle:checked')).map(el => parseInt(el.value));
  const onlyOpen = document.getElementById('onlyOpenToggle').checked;

  placesToRender.forEach(place => {

  
    const isOpen = isOpenNow(place.opening_hours);

// Accomodation vždy zobrazíme, nezávisle na otevírací době
if (place.category_id !== 15 && onlyOpen && !isOpen) return;

const iconClass = iconMap[place.category_id] || 'fa-map-marker-alt';
const customIcon = L.divIcon({ html: `<i class="fas ${iconClass} fa-marker"></i>`, iconSize: [30, 30], className: '' });

let statusLabel = '';
let openingHtml = '';

if (place.category_id === 15 && place.property_id) {
  openingHtml = `<div style="margin-top: 10px;">
    <form method="POST" action="/property_calendar.php" target="_blank" style="margin:0;">
      <input type="hidden" name="property_id" value="${place.property_id}">
      <button type="submit" class="cta-button">📅 View Availability</button>
    </form>
  </div>`;
  //openingHtml = `<div style="margin-top: 10px;"><a href="/property_calendar.php?id=${place.property_id}" target="_blank" class="cta-button">📅 View Availability</a></div>`;
} else {
  statusLabel = isOpen ? '<span style="color: green; font-weight: bold;">🟢 Open</span>' : '<span style="color: red; font-weight: bold;">🔴 Closed</span>';

  if (place.opening_hours) {
    try {
      const data = JSON.parse(place.opening_hours);
      const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
      const today = new Date().toLocaleDateString('en-US', { weekday: 'long' });
      openingHtml += '<div class="opening-hours"><b>🕒 Opening hours:</b><br>';
      for (const day of days) {
        if (data[day]) {
          const intervals = Array.isArray(data[day]) ? data[day] : [data[day]];
          const times = intervals.map(p => `${p.from} - ${p.to}`).join(', ');
          const highlight = (day === today) ? 'today' : '';
          openingHtml += `<div class="${highlight}">${day.padEnd(10)} ${times}</div>`;
        }
      }
      openingHtml += '</div>';
    } catch (e) {}
  }
}


const popup = document.createElement('div');
popup.innerHTML = `
  <div>
    <div style="display: flex; justify-content: space-between; align-items: center;">
      <b>${place.name}</b>
      ${statusLabel}
    </div>
    ${place.description ? `<div>${place.description}</div>` : ''}
    ${place.address ? `<div>📍 ${place.address}</div>` : ''}
    ${place.phone ? `<div> ${place.phone}</div>` : ''}

    ${place.website ? `<div><a href="${place.website}" target="_blank">🌐 Website</a></div>` : ''}
    <div><a href="https://www.google.com/maps/dir/?api=1&destination=${place.latitude},${place.longitude}" target="_blank">🧭 Navigate</a></div>
    ${openingHtml}
    ${isAdmin ? `<div style='margin-top:10px;'><button class='edit-btn' data-id="${place.id}">✏️ Edit</button></div>` : ''}
  </div>
`;

    const statusDot = isOpen ? '🟢' : '🔴';
    const marker = L.marker([place.latitude, place.longitude], { icon: customIcon })
      .addTo(map)
      .bindPopup(popup)
      .bindTooltip(`${place.name} ${statusDot}`, { direction: 'top', offset: [0, -10], opacity: 0.9 });

    markers.push(marker);
  });

}

renderMarkers();
document.addEventListener('click', function(e) {
  const btn = e.target.closest('.edit-btn');
  if (btn) {
    const id = btn.getAttribute('data-id');
    const place = places.find(p => p.id == id);
    if (place) openEditModal(place);
  }
});

document.querySelectorAll('.category-toggle').forEach(cb => cb.addEventListener('change', renderMarkers));
document.getElementById('onlyOpenToggle').addEventListener('change', renderMarkers);

document.getElementById('burger').addEventListener('click', () => {
  const c = document.getElementById('controls');
  if (c) {
    c.style.display = c.style.display === 'none' ? 'block' : 'none';
  }
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
document.getElementById('searchInput').addEventListener('input', function() {
  const query = this.value.toLowerCase();
  const filtered = places.filter(p =>
    (p.name && p.name.toLowerCase().includes(query)) ||
    (p.description && p.description.toLowerCase().includes(query))
  );
  renderMarkers(filtered);
});

</script>
</body>
</html>


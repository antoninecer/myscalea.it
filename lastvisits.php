<?php
// stats.php - Přehled návštěv s mapovým modálem OpenStreetMap přes Leaflet

session_start();
require_once __DIR__ . '/inc/connect.php';

// Funkce pro bezpečný escape
function e($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

// Query 1: Přehled unikátních návštěvníků s geolokací
$sql1 = "
    SELECT
        INET6_NTOA(ip_address) AS ip,
        device_type,
        os,
        browser,
        country,
        latitude,
        longitude,
        COUNT(*) AS visits
    FROM visits
    GROUP BY ip_address, device_type, os, browser, country, latitude, longitude
    ORDER BY visits DESC
";
$visitors = $pdo->query($sql1)->fetchAll(PDO::FETCH_ASSOC);

// Query 2: Přehled stránek (bez GET) a počet unikátních IP
$sql2 = "
    SELECT
        SUBSTRING_INDEX(page_url, '?', 1) AS path,
        COUNT(DISTINCT ip_address) AS uniq_ips,
        COUNT(*) AS hits
    FROM visits
    GROUP BY path
    ORDER BY uniq_ips DESC
";
$pages = $pdo->query($sql2)->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'header.php'; ?>
<?php include 'menu.php'; ?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Statistika návštěv</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        table { border-collapse: collapse; width: 100%; margin-bottom: 2em; }
        th, td { padding: 8px; border: 1px solid #ccc; }
        th { background-color: #f4f4f4; text-align: left; }
        h2 { margin-top: 1.5em; }
        /* Modal */
        #mapModal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        #mapModal .modal-content { position: relative; width: 90%; max-width: 600px; height: 400px; background: #fff; border-radius: 8px; overflow: hidden; }
        #mapModal .close { position: absolute; top: 10px; right: 10px; cursor: pointer; font-size: 18px; z-index: 1001; }
        #map { width: 100%; height: 100%; }
        .globe-icon { text-decoration: none; font-size: 1.2em; }
    </style>
</head>
<body>
    <h1>Statistika návštěv</h1>

    <h2>1️⃣ Unikátní návštěvníci</h2>
    <table>
        <thead>
            <tr>
                <th>IP</th><th>Device</th><th>OS</th><th>Browser</th><th>Country</th>
                <th>Lat</th><th>Lng</th><th>Visits</th><th>Mapa</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($visitors as $v): ?>
            <tr>
                <td><?= e($v['ip']) ?></td>
                <td><?= e($v['device_type']) ?></td>
                <td><?= e($v['os']) ?></td>
                <td><?= e($v['browser']) ?></td>
                <td><?= e($v['country']) ?></td>
                <td><?= e($v['latitude']) ?></td>
                <td><?= e($v['longitude']) ?></td>
                <td><?= e($v['visits']) ?></td>
                <td>
                    <?php if ($v['latitude'] !== null && $v['longitude'] !== null): ?>
                        <a href="#" class="globe-icon showMap"
                           data-lat="<?= e($v['latitude']) ?>"
                           data-lng="<?= e($v['longitude']) ?>">🌐</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h2>2️⃣ Přehled stránek bez GET parametrů</h2>
    <table>
        <thead>
            <tr><th>Page Path</th><th>Unique IPs</th><th>Total Hits</th></tr>
        </thead>
        <tbody>
        <?php foreach ($pages as $p): ?>
            <tr>
                <td><?= e($p['path']) ?></td>
                <td><?= e($p['uniq_ips']) ?></td>
                <td><?= e($p['hits']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Map Modal -->
    <div id="mapModal">
        <div class="modal-content">
            <span class="close">✖</span>
            <div id="map"></div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
    // Globální reference na instanci mapy
    let mapInstance;
    // Zobraz modal a init mapu
    document.querySelectorAll('.showMap').forEach(el => {
        el.addEventListener('click', e => {
            e.preventDefault();
            const lat = parseFloat(el.dataset.lat);
            const lng = parseFloat(el.dataset.lng);
            const modal = document.getElementById('mapModal');
            modal.style.display = 'flex';
            // pokud existuje stará mapa, smažeme ji
            if (mapInstance) {
                mapInstance.remove();
            }
            // inicializace nové mapy
            mapInstance = L.map('map').setView([lat, lng], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19, attribution: '© OpenStreetMap'
            }).addTo(mapInstance);
            L.marker([lat, lng]).addTo(mapInstance);
        });
    });
    // Zavření modalu (křížek)
    document.querySelector('#mapModal .close').addEventListener('click', () => {
        const modal = document.getElementById('mapModal');
        modal.style.display = 'none';
        if (mapInstance) {
            mapInstance.remove();
            mapInstance = null;
        }
    });
    // Zavření modalu klikem mimo obsah
    document.getElementById('mapModal').addEventListener('click', e => {
        if (e.target.id === 'mapModal') {
            const modal = document.getElementById('mapModal');
            modal.style.display = 'none';
            if (mapInstance) {
                mapInstance.remove();
                mapInstance = null;
            }
        }
    });
    </script>

<?php include 'footer.php'; ?>
</body>
</html>
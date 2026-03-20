<?php
// stats.php - Statistika návštěv s filtrem období a mapovým modálem

session_start();
require_once __DIR__ . '/inc/connect.php';

// Funkce pro escape
function e($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

// Rozsah dat: defaultní poslední den
$today = new DateTime('today');
$yesterday = (new DateTime('today'))->modify('-1 day');

// Parametry z GET
$from = !empty($_GET['from']) ? DateTime::createFromFormat('Y-m-d', $_GET['from']) : $yesterday;
$to   = !empty($_GET['to'])   ? DateTime::createFromFormat('Y-m-d', $_GET['to'])   : $today;
// upravíme $to na konec dne
$to->setTime(23,59,59);

// Převod na formát pro MySQL
$fromSQL = $from->format('Y-m-d H:i:s');
$toSQL   = $to->format('Y-m-d H:i:s');

// Query 1: Unikátní návštěvníci (celkově)
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

// Query 2: Stránky celkem bez GET
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

// Query 3: Detail návštěv v období
$sql3 = "
    SELECT
        SUBSTRING_INDEX(page_url, '?', 1) AS path,
        user_id,
        country,
        latitude,
        longitude,
        language,
        visit_start
    FROM visits
    WHERE visit_start BETWEEN :from AND :to
    ORDER BY visit_start DESC
";
$stmt3 = $pdo->prepare($sql3);
$stmt3->execute([':from'=>$fromSQL, ':to'=>$toSQL]);
$detail = $stmt3->fetchAll(PDO::FETCH_ASSOC);
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
        body { padding: 20px; }
        form.date-filter { margin-bottom: 20px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 2em; font-family: sans-serif; }
        th, td { padding: 6px; border: 1px solid #ccc; }
        th { background-color: #f4f4f4; }
        .globe-icon { font-size: 1.2em; text-decoration: none; }
        /* Modal */
        #mapModal { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:1000; }
        #mapModal .modal-content { position: relative; width: 90%; max-width: 600px; height: 400px; background:#fff; border-radius:8px; overflow:hidden; }
        #mapModal .close { position:absolute; top:10px; right:10px; cursor:pointer; font-size:18px; z-index:1001; }
        #map { width:100%; height:100%; }
    </style>
</head>
<body>
    <h1>Statistika návštěv</h1>

    <form method="get" class="date-filter">
        <label>Od: <input type="date" name="from" value="<?= e($from->format('Y-m-d')) ?>"></label>
        <label>Do: <input type="date" name="to"   value="<?= e((new DateTime($toSQL))->format('Y-m-d')) ?>"></label>
        <button type="submit">Zobrazit</button>
    </form>

    <h2>1️⃣ Unikátní návštěvníci</h2>
    <table>
        <thead><tr><th>IP</th><th>Zařízení</th><th>OS</th><th>Prohlížeč</th><th>Stát</th><th>Lat</th><th>Lng</th><th>Počet</th><th>Mapa</th></tr></thead>
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
                    <?php if($v['latitude'] && $v['longitude']): ?>
                        <a href="#" class="globe-icon showMap" data-lat="<?= e($v['latitude']) ?>" data-lng="<?= e($v['longitude']) ?>">🌐</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h2>2️⃣ Přehled stránek bez GET</h2>
    <table>
        <thead><tr><th>Stránka</th><th>Unikátní IP</th><th>Celkem</th></tr></thead>
        <tbody>
        <?php foreach($pages as $p): ?>
            <tr>
                <td><?= e($p['path']) ?></td>
                <td><?= e($p['uniq_ips']) ?></td>
                <td><?= e($p['hits']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h2>3️⃣ Detail návštěv (<?= e($from->format('Y-m-d')) ?> – <?= e((new DateTime($toSQL))->format('Y-m-d')) ?>)</h2>
    <table>
        <thead><tr><th>Čas</th><th>Stránka</th><th>User ID</th><th>Stát</th><th>Lat</th><th>Lng</th><th>Jazyk</th><th>Mapa</th></tr></thead>
        <tbody>
        <?php foreach($detail as $d): ?>
            <tr>
                <td><?= e($d['visit_start']) ?></td>
                <td><?= e($d['path']) ?></td>
                <td><?= e($d['user_id']) ?></td>
                <td><?= e($d['country']) ?></td>
                <td><?= e($d['latitude']) ?></td>
                <td><?= e($d['longitude']) ?></td>
                <td><?= e($d['language']) ?></td>
                <td>
                    <?php if($d['latitude'] && $d['longitude']): ?>
                        <a href="#" class="globe-icon showMap" data-lat="<?= e($d['latitude']) ?>" data-lng="<?= e($d['longitude']) ?>">🌐</a>
                    <?php endif; ?>
                </td>
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
    let mapInstance;
    document.querySelectorAll('.showMap').forEach(el => {
        el.addEventListener('click', e => {
            e.preventDefault();
            const lat = parseFloat(el.dataset.lat);
            const lng = parseFloat(el.dataset.lng);
            const modal = document.getElementById('mapModal'); modal.style.display = 'flex';
            if (mapInstance) { mapInstance.remove(); }
            mapInstance = L.map('map').setView([lat, lng], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom:19, attribution:'© OpenStreetMap' }).addTo(mapInstance);
            L.marker([lat, lng]).addTo(mapInstance);
        });
    });
    const closeModal = () => { const modal = document.getElementById('mapModal'); modal.style.display='none'; if(mapInstance){ mapInstance.remove(); mapInstance=null; } };
    document.querySelector('#mapModal .close').addEventListener('click',closeModal);
    document.getElementById('mapModal').addEventListener('click', e => { if(e.target.id==='mapModal') closeModal(); });
    </script>

<?php include 'footer.php'; ?>
</body>
</html>


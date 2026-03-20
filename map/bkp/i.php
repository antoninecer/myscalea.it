<?php include('translate.php');
require_once(dirname(__FILE__) . '/../oc-load.php');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ... (zbytek tvého PHP kódu) ...
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Map - MyScalea</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="map.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
    <?php include('menu.php'); ?>
    <div id="map"></div>
    <div id="weather-info">Loading weather...</div>

<script>
    var map = L.map('map').setView([39.8133, 15.7984], 14);
    map.zoomControl.setPosition('bottomleft');

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    var points = <?php echo json_encode($points, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE); ?>;
    var markers = {};

    points.forEach(point => {
        // ... (zbytek tvého kódu pro markery) ...
    });

    function updateWeather(lat, lon) {
        fetch(`https://wttr.in/${lat},${lon}?format=%C+%t`)
            .then(response => response.text())
            .then(weather => {
                fetch(`https://api.timezonedb.com/v2.1/get-time-zone?key=LN2W6QBNBO3T&format=json&by=position&lat=${lat}&lng=${lon}`)
                    .then(response => response.json())
                    .then(timeData => {
                        if (timeData.status === 'OK') {
                            const localTime = new Date(timeData.formatted);
                            document.getElementById('weather-info').innerHTML = `
                                <b>Local Time:</b> ${localTime.toLocaleTimeString()}<br>
                                <b>Weather:</b> ${weather}`;
                        } else {
                            document.getElementById('weather-info').innerHTML = `
                                <b>Weather:</b> ${weather}<br>
                                Time data unavailable`;
                        }
                    })
                    .catch(error => {
                        document.getElementById('weather-info').innerHTML = `
                            <b>Weather:</b> ${weather}<br>
                            Time data unavailable`;
                        console.error('Error fetching time:', error);
                    });
            })
            .catch(error => {
                document.getElementById('weather-info').innerHTML = 'Weather data unavailable';
                console.error('Error fetching weather:', error);
            });
    }

    function getMapCenterWeather() {
        var center = map.getCenter();
        updateWeather(center.lat, center.lng);
    }

    map.on('moveend', getMapCenterWeather);
    getMapCenterWeather();
</script>

<style>
    #weather-info {
        position: absolute;
        bottom: 10px;
        right: 10px;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 8px;
        border-radius: 5px;
        font-size: 14px;
        z-index: 1000;
    }
</style>

</body>
</html>

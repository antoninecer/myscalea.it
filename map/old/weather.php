<div id="weather-info">Loading weather...</div>

<script>
    function updateWeather(lat, lon) {
        // Získání času a počasí podle souřadnic
        fetch(`https://worldtimeapi.org/api/timezone/Etc/GMT`)
            .then(response => response.json())
            .then(timeData => {
                return fetch(`https://wttr.in/${lat},${lon}?format=%C+%t`)
                    .then(response => response.text())
                    .then(weather => {
                        document.getElementById('weather-info').innerHTML = `
                            <b>Local Time:</b> ${new Date(timeData.datetime).toLocaleTimeString()}<br>
                            <b>Weather:</b> ${weather}
                        `;
                    });
            })
            .catch(error => {
                document.getElementById('weather-info').innerHTML = 'Weather data unavailable';
            });
    }

    // Získání středu mapy a aktualizace počasí
    function getMapCenterWeather() {
        var center = map.getCenter();
        updateWeather(center.lat, center.lng);
    }

    // Po načtení mapy zobrazit počasí
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


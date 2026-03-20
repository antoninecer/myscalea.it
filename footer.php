<footer>
  &copy; 2025 MyScalea.it <a href="https://x.com/RightDoneEU" target="_blank">Sledujte nás na X: @RightDoneEU</a>
</footer>
<script>
  document.getElementById('burger').addEventListener('click', function () {
    document.getElementById('sideMenu').classList.toggle('open');
  });

  function updateClock() {
    const now = new Date();
    document.getElementById('clock').textContent = now.toLocaleTimeString('cs-CZ');
  }
  setInterval(updateClock, 1000);
  updateClock();

  async function fetchWeather() {
    try {
      const res = await fetch('https://wttr.in/Scalea?format=j1');
      const data = await res.json();
      const current = data.current_condition[0];
      const desc = current.weatherDesc[0].value;
      const temp = current.temp_C;
      const wind = current.windspeedKmph;
      document.getElementById('weather').textContent = `${desc}, ${temp}°C, wind ${wind} km/h`;
    } catch (e) {
      document.getElementById('weather').textContent = 'Weather unavailable.';
    }
  }
  fetchWeather();
</script>
</body>
</html>


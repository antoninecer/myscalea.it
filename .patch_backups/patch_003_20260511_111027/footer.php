</main>

<footer class="site-footer">
  <div class="footer-inner">
    <div>&copy; <?= date('Y') ?> MyScalea.it</div>
    <a href="https://x.com/RightDoneEU" target="_blank" rel="noopener noreferrer">Sledujte nás na X: @RightDoneEU</a>
  </div>
</footer>

<script>
(function () {
  const burger = document.getElementById('burger');
  const sideMenu = document.getElementById('sideMenu');
  const overlay = document.getElementById('menuOverlay');
  const closeBtn = document.getElementById('menuClose');

  function setMenu(open) {
    if (!burger || !sideMenu || !overlay) return;

    sideMenu.classList.toggle('open', open);
    document.body.classList.toggle('menu-open', open);
    burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    sideMenu.setAttribute('aria-hidden', open ? 'false' : 'true');
    overlay.hidden = !open;

    if (open && closeBtn) {
      closeBtn.focus();
    }
  }

  if (burger && sideMenu && overlay) {
    burger.addEventListener('click', function () {
      setMenu(!sideMenu.classList.contains('open'));
    });

    if (closeBtn) {
      closeBtn.addEventListener('click', function () {
        setMenu(false);
        burger.focus();
      });
    }

    overlay.addEventListener('click', function () {
      setMenu(false);
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        setMenu(false);
      }
    });
  }

  function updateClock() {
    const clock = document.getElementById('clock');
    if (!clock) return;

    const now = new Date();
    clock.textContent = now.toLocaleTimeString('cs-CZ');
  }

  updateClock();
  setInterval(updateClock, 1000);

  async function fetchWeather() {
    const weather = document.getElementById('weather');
    if (!weather) return;

    try {
      const res = await fetch('https://wttr.in/Scalea?format=j1');
      const data = await res.json();
      const current = data.current_condition && data.current_condition[0];

      if (!current) {
        weather.textContent = 'Weather unavailable.';
        return;
      }

      const desc = current.weatherDesc && current.weatherDesc[0] ? current.weatherDesc[0].value : 'Weather';
      const temp = current.temp_C;
      const wind = current.windspeedKmph;
      weather.textContent = `${desc}, ${temp}°C, wind ${wind} km/h`;
    } catch (e) {
      weather.textContent = 'Weather unavailable.';
    }
  }

  fetchWeather();
})();
</script>
</body>
</html>

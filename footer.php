<footer>
  &copy; 2025 MyScalea.it <a href="https://x.com/RightDoneEU" target="_blank" rel="noopener noreferrer">Sledujte nás na X: @RightDoneEU</a>
</footer>

<script>
  (function () {
    const nav = document.getElementById('topNav');
    const toggle = document.getElementById('topNavToggle');
    const menu = document.getElementById('topNavMenu');

    if (toggle && menu) {
      toggle.addEventListener('click', function () {
        const isOpen = menu.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        document.body.classList.toggle('nav-open', isOpen);
      });

      menu.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
          menu.classList.remove('is-open');
          toggle.setAttribute('aria-expanded', 'false');
          document.body.classList.remove('nav-open');
        });
      });

      document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
          menu.classList.remove('is-open');
          toggle.setAttribute('aria-expanded', 'false');
          document.body.classList.remove('nav-open');
        }
      });
    }

    function updateNavStyle() {
      if (!nav) return;
      nav.classList.toggle('is-scrolled', window.scrollY > 12);
    }

    updateNavStyle();
    window.addEventListener('scroll', updateNavStyle, { passive: true });
  })();

  function updateClock() {
    const el = document.getElementById('clock');
    if (!el) return;
    const now = new Date();
    el.textContent = now.toLocaleTimeString('cs-CZ');
  }
  setInterval(updateClock, 1000);
  updateClock();

  async function fetchWeather() {
    const el = document.getElementById('weather');
    if (!el) return;

    try {
      const res = await fetch('https://wttr.in/Scalea?format=j1');
      const data = await res.json();
      const current = data.current_condition[0];
      const desc = current.weatherDesc[0].value;
      const temp = current.temp_C;
      const wind = current.windspeedKmph;
      el.textContent = `${desc}, ${temp}°C, wind ${wind} km/h`;
    } catch (e) {
      el.textContent = 'Weather unavailable.';
    }
  }
  fetchWeather();
</script>


</body>
</html>

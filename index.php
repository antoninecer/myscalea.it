<?php include 'header.php'; ?>
<?php include 'menu.php'; ?>

<header class="main-header">
  <h1>MyScalea.it</h1>
  <p>One roof for your needs 🌊</p>
</header>


<section class="section">
  <?php
  $mf = __DIR__ . '/mareflag.php';
  if (is_file($mf)) {
      include $mf; // POZOR: mareflag.php už nesmí volat header()
  } else {
      echo '<div class="mare-flag">Scalea · Tyrhénské moře • vlny — • stav nezjištěn</div>';
  }
  ?>
  <a href="https://www.3bmeteo.com/previsioni/mare/calabria"
     target="_blank" rel="noopener noreferrer"
     style="margin-left:.75rem">Mare Calabria</a>
</section>

<section class="section">
  <?php include 'mapinfo.php'; ?>
</section>
<section class="section info-bar">
  <div id="clock">🕒</div>
  <div id="weather">☁️ Weather...</div>
</section>
<?php if (isset($_GET['register']) && $_GET['register'] === 'success' && isset($_SESSION['register_success'])): ?>
  <div id="registerSuccessModal" style="position:fixed; top:20%; left:50%; transform:translateX(-50%); background:#fff; padding:20px; border:1px solid #ccc; box-shadow:0 0 10px rgba(0,0,0,0.5); z-index:2000;">
    <h3>🎉 Registration successful</h3>
    <p>We have sent a confirmation email to your address.<br>
    Please check your inbox and click the link to verify your account.</p>
    <button onclick="document.getElementById('registerSuccessModal').style.display='none'">Close</button>
  </div>
  <?php unset($_SESSION['register_success']); ?>
<?php endif; ?>
<section class="section" style="text-align:center;">
  <div style="position:relative; padding-bottom:56.25%; overflow:hidden; max-width:100%; height:auto;">
    <iframe
      src="https://www.youtube.com/embed/videoseries?list=PL2Nr2YaUS6x3kMLg124hkSLNzCRv3SySq&rel=0&autoplay=0&modestbranding=1"
      frameborder="0"
      allow="autoplay; encrypted-media"
      allowfullscreen
      style="position:absolute; top:0; left:0; width:100%; height:100%;">
    </iframe>
  </div>
</section>

<section class="section" style="text-align:center;">
  <div style="position:relative; padding-bottom:56.25%; overflow:hidden; max-width:100%; height:auto;">
    <iframe
      src="https://www.youtube.com/embed/YEkXTZd4ymw?rel=0&autoplay=0&modestbranding=1"
      frameborder="0"
      allow="autoplay; encrypted-media"
      allowfullscreen
      style="position:absolute; top:0; left:0; width:100%; height:100%;">
    </iframe>
  </div>
</section>
<section class="section">
  <h2>🔗 Usefull links</h2>
  <ul>
    <li><a href="https://www.facebook.com/groups/ekovesnicescalea" target="_blank">Ekovesnice Scalea</a></li>
    <li><a href="https://www.facebook.com/groups/2555526471272898" target="_blank">Češi a Slováci ve Scalee</a></li>
    <li><a href="https://www.facebook.com/groups/myscalea" target="_blank">MyScalea – komunita</a></li>
  </ul>
</section>

<?php include 'footer.php'; ?>


<?php include 'header.php'; ?>
<?php include 'menu.php'; ?>

<header class="main-header">
  <h1>MyScalea.it</h1>
  <p>Apartment ownership, rentals and local support in Scalea 🌊</p>
</header>

<section class="section" style="padding: 18px 20px;">
  <div style="
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
  ">

    <div style="
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 12px;
    ">
      <?php
      $mf = __DIR__ . '/mareflag.php';
      if (is_file($mf)) {
          include $mf; // mareflag.php nesmí volat header()
      } else {
          echo '<div class="mare-flag">Scalea · Tyrhénské moře • vlny — • stav nezjištěn</div>';
      }
      ?>

      <a href="https://www.3bmeteo.com/previsioni/mare/calabria"
         target="_blank"
         rel="noopener noreferrer">
        Mare Calabria
      </a>
    </div>

    <div style="
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 12px;
      font-size: 0.95rem;
      color: #334155;
    ">
      <div id="clock">🕒</div>
      <div id="weather">☁️ Weather...</div>
    </div>

  </div>
</section>
<?php if (false): ?>
<section class="section">
  <h2>For apartment owners in Scalea</h2>
  <p>
    Do you own an apartment in Scalea or nearby? MyScalea.it helps you present it,
    manage bookings, keep your calendar clear, organize documents and offer better
    support to your guests.
  </p>

  <div style="
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-top: 24px;
  ">
    <div style="border:1px solid #e9dfd2; border-radius:16px; padding:18px; background:#faf7f2;">
      <h3>🏠 START</h3>
      <p>Presentation page, photos, description, map and contact form for your apartment.</p>
    </div>

    <div style="border:1px solid #e9dfd2; border-radius:16px; padding:18px; background:#faf7f2;">
      <h3>📅 MANAGEMENT</h3>
      <p>Availability calendar, seasonal prices, reservation overview and guest documents.</p>
    </div>

    <div style="border:1px solid #e9dfd2; border-radius:16px; padding:18px; background:#faf7f2;">
      <h3>🤝 FULL SERVICE</h3>
      <p>Local support, guest communication, recommendations and practical help in Scalea.</p>
    </div>
  </div>

  <div style="text-align:center; margin-top:28px;">
    <a href="/owners.php" class="cta-button">I have an apartment in Scalea</a>
  </div>
</section>
<?php endif; ?>
<section class="section">
  <?php include 'mapinfo.php'; ?>
</section>

<?php if (isset($_GET['register']) && $_GET['register'] === 'success' && isset($_SESSION['register_success'])): ?>
  <div id="registerSuccessModal" style="
    position:fixed;
    top:20%;
    left:50%;
    transform:translateX(-50%);
    background:#fff;
    padding:20px;
    border:1px solid #ccc;
    box-shadow:0 0 10px rgba(0,0,0,0.5);
    z-index:2000;
    max-width:420px;
    width:calc(100% - 40px);
    border-radius:12px;
  ">
    <h3>🎉 Registration successful</h3>
    <p>
      We have sent a confirmation email to your address.<br>
      Please check your inbox and click the link to verify your account.
    </p>
    <button onclick="document.getElementById('registerSuccessModal').style.display='none'">Close</button>
  </div>
  <?php unset($_SESSION['register_success']); ?>
<?php endif; ?>

<section class="section" style="text-align:center;">
  <h2>Video guide</h2>
  <div style="position:relative; padding-bottom:56.25%; overflow:hidden; max-width:100%; height:auto; border-radius:16px;">
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
  <h2>Scalea inspiration</h2>
  <div style="position:relative; padding-bottom:56.25%; overflow:hidden; max-width:100%; height:auto; border-radius:16px;">
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
  <h2>🔗 Useful links</h2>
  <ul>
    <li><a href="https://www.facebook.com/groups/ekovesnicescalea" target="_blank" rel="noopener noreferrer">Ekovesnice Scalea</a></li>
    <li><a href="https://www.facebook.com/groups/2555526471272898" target="_blank" rel="noopener noreferrer">Češi a Slováci ve Scalee</a></li>
    <li><a href="https://www.facebook.com/groups/myscalea" target="_blank" rel="noopener noreferrer">MyScalea – komunita</a></li>
  </ul>
</section>

<?php include 'footer.php'; ?>

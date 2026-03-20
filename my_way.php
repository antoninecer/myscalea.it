<?php
session_start();
require_once 'inc/connect.php';
?>

<?php include 'header.php'; ?>
<?php include 'menu.php'; ?>

<header class="main-header">
  <h1>My Way</h1>
  <p>Take the first step towards your dream property in paradise by the sea</p>
</header>

<section class="section">
  <?php if (!isset($_SESSION['client'])): ?>
    <h2>Begin Your Journey to the Perfect Home</h2>
    <p>This page guides you through every stage of securing your ideal property in Scalea and its surroundings. With our expertise, you'll steer clear of common pitfalls that can cost inexperienced buyers unnecessary money.</p>
    <ul>
      <li>Discover where you can save smartly without compromising on quality.</li>
      <li>Get alerted to overpriced or unfavorable offers.</li>
      <li>Receive advice on setting priorities and preparing everything in the right order.</li>
      <li>Specialized support for EU citizens to navigate local regulations and requirements.</li>
    </ul>
    <p>With clear guidance, a fair approach, and personal assistance, you’ll enjoy a smoother and safer path to owning or renting your seaside property.</p>
    <p><a href="client_request.php?register=1" class="cta-button">Registration form</a></p>
    <br>
    <p><a href="client_login.php" class="cta-button">Client's login</a></p>
  <?php else: ?>
    <?php
      $email = $_SESSION['client']['email'];
      $stmt = $pdo->prepare("SELECT * FROM clients WHERE email = ? LIMIT 1");
      $stmt->execute([$email]);
      $client = $stmt->fetch(PDO::FETCH_ASSOC);
    ?>
    <?php if (!$client): ?>
      <h2>Your Journey Hasn't Started Yet</h2>
      <p>You have not submitted any requests yet. <a href="client_request.php" class="cta-button">Submit Request</a></p>
    <?php else: ?>
      <?php
        $req = json_decode($client['requirements'], true);
        $stmt = $pdo->prepare("SELECT * FROM client_logs WHERE client_id = ? ORDER BY created_at DESC");
        $stmt->execute([$client['id']]);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
      ?>

      <section class="section">
        <h2>Initial Requirements</h2>
        <p><strong>Rooms:</strong> <?= htmlspecialchars($req['rooms']) ?> | <strong>Area:</strong> <?= htmlspecialchars($req['area']) ?> m² | <strong>Location:</strong> <?= htmlspecialchars($req['location']) ?> | <strong>Budget:</strong> <?= number_format($req['budget'], 0, '.', ',') ?> EUR</p>
      </section>

      <section class="section">
        <h2>Step by Step Guide</h2>
        <ol>
          <li>Verify Budget and Financing</li>
          <li>Select Location and Analyze Market</li>
          <li>Visit Properties and Evaluate Offers</li>
          <li>Prepare Legal and Administrative Steps</li>
          <li>Sign Contract and Complete Transfer</li>
        </ol>
      </section>

      <section class="section">
        <h2>Current Status</h2>
        <?php if ($logs): ?>
          <?php foreach ($logs as $log): ?>
            <div class="log-entry">
              <time datetime="<?= $log['created_at'] ?>"><?= date('m/d/Y H:i', strtotime($log['created_at'])) ?></time> – <strong><?= htmlspecialchars($log['action']) ?></strong>
              <p><?= nl2br(htmlspecialchars($log['note'])) ?></p>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No steps recorded yet. We’ll be in touch soon!</p>
        <?php endif; ?>
      </section>

      <?php
        // Prepare contract based on first contact
        $firstContact = null;
        foreach ($logs as $log) {
            if ($log['action'] === 'První kontakt') {
                parse_str(strtr($log['note'], ["\n" => "&", ": " => "="]), $firstContact);
                break;
            }
        }
      ?>
      <?php if ($firstContact): ?>
        <section class="section info-bar">
          <p>Based on our initial contact, we’ve prepared a preliminary contract.</p>
          <form method="POST" action="client_future_contract.php" target="_blank" style="display:inline;">
  <!-- client’s name & email -->
  <input type="hidden" name="name" value="<?= htmlspecialchars($_SESSION['client']['name']) ?>">
  <input type="hidden" name="email" value="<?= htmlspecialchars($_SESSION['client']['email']) ?>">

  <!-- now your “first contact” values, mapped back to Czech keys: -->
  <input type="hidden" name="Účel" value="<?= htmlspecialchars($firstData['purpose']) ?>">
  <input type="hidden" name="Pronájem" value="<?= $firstData['rent'] ? 'ano' : 'ne' ?>">
  <input type="hidden" name="Forma_pronájmu" value="<?= htmlspecialchars($firstData['rental_form']) ?>">
  <input type="hidden" name="Ložnice" value="<?= $firstData['bedrooms'] ?>">
  <input type="hidden" name="Koupelny" value="<?= $firstData['bathrooms'] ?>">
  <input type="hidden" name="Balkon_/_terasa" value="<?= $firstData['balcony_terrace'] ?>">
  <input type="hidden" name="Počet_osob_/_dětí" value="<?= htmlspecialchars($firstData['persons_children']) ?>">
  <input type="hidden" name="Parkování" value="<?= $firstData['parking'] ? 'ano' : 'ne' ?>">
  <input type="hidden" name="Rozsah_pater" value="<?= htmlspecialchars($firstData['floors_range']) ?>">
  <input type="hidden" name="Výtah_od_patra" value="<?= htmlspecialchars($firstData['elevator_from_floor']) ?>">
  <input type="hidden" name="Vzdálenost_od_moře" value="<?= $firstData['distance_to_sea_m'] ?>">
  <input type="hidden" name="Vzdálenost_od_centra" value="<?= htmlspecialchars($firstData['distance_to_center']) ?>">
  <input type="hidden" name="Minimální_plocha" value="<?= $firstData['min_area_m2'] ?>">
  <input type="hidden" name="Způsob_financování" value="<?= htmlspecialchars($firstData['financing_method']) ?>">
  <input type="hidden" name="Finanční_poradce" value="<?= $firstData['financial_advisor'] ? 'ano' : 'ne' ?>">
  <input type="hidden" name="Cenový_strop" value="<?= $firstData['price_cap_eur'] ?>">
  <input type="hidden" name="Forma_prohlídek" value="<?= htmlspecialchars($firstData['viewing_type']) ?>">

  <button type="submit" class="cta-button">View Contract</button>
</form>
        </section>
      <?php endif; ?>

    <?php endif; ?>
  <?php endif; ?>
</section>

<?php include 'footer.php'; ?>

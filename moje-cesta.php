<?php
session_start();
require_once 'inc/connect.php';
?>

<?php include 'header.php'; ?>
<?php include 'menu.php'; ?>

<header class="main-header">
  <h1>My Way / Moje cesta</h1>
  <p>Průvodce vaší cestou k vysněné nemovitosti ve Scalee</p>
</header>

<section class="section">
  <?php if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user'): ?>
    <h2>Výprava za vaší vysněnou nemovitostí</h2>
    <p>Na této stránce vás krok za krokem provedeme cestou k získání ideální nemovitosti ve Scalee a nejbližším okolí. Díky našim zkušenostem vám pomůžeme vyhnout se častým nástrahám, které mohou nezkušené zájemce stát zbytečné peníze.</p>
    <ul>
      <li>Ukážeme vám, kde lze chytře ušetřit bez kompromisů na kvalitě.</li>
      <li>Upozorníme vás na možné riziko nadhodnocených nebo nevýhodných nabídek.</li>
      <li>Poradíme vám, jak si správně nastavit priority a připravit vše potřebné v tom správném pořadí.</li>
    </ul>
    <p>Díky jasným informacím, férovému přístupu a osobní asistenci vás čeká hladší a bezpečnější cesta k vlastní nemovitosti u moře.</p>
    <p><a href="client_request.php?register=1" class="cta-button">Zaregistrujte se / Register</a></p>
    <p><a href="client_login.php" class="cta-button">Přihlašte se / Login</a></p>
  <?php else: ?>
    <?php
      $email = $_SESSION['user']['email'];
      $stmt = $pdo->prepare("SELECT * FROM clients WHERE email = ? LIMIT 1");
      $stmt->execute([$email]);
      $client = $stmt->fetch(PDO::FETCH_ASSOC);
    ?>
    <?php if (!$client): ?>
      <h2>Vaše cesta zatím nezačala</h2>
      <p>Nemáte u nás ještě žádný požadavek. <a href="client_request.php" class="cta-button">Zadat přání</a></p>
    <?php else: ?>
      <?php
        $req = json_decode($client['requirements'], true);
        $stmt = $pdo->prepare("SELECT * FROM client_logs WHERE client_id = ? ORDER BY created_at DESC");
        $stmt->execute([$client['id']]);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
      ?>
      <section class="section">
        <h2>Počáteční požadavky</h2>
        <p><strong>Pokojů:</strong> <?= htmlspecialchars($req['rooms']) ?> | <strong>Rozloha:</strong> <?= htmlspecialchars($req['area']) ?> m² | <strong>Lokalita:</strong> <?= htmlspecialchars($req['location']) ?> | <strong>Rozpočet:</strong> <?= number_format($req['budget'],0,',',' ') ?> EUR</p>
      </section>

      <section class="section">
        <h2>Krok za krokem</h2>
        <ol>
          <li>Ověření rozpočtu a financování</li>
          <li>Výběr lokality a analýza trhu</li>
          <li>Prohlídky a hodnocení nabídek</li>
          <li>Právní a administrativní příprava</li>
          <li>Uzavření smlouvy a převod</li>
        </ol>
      </section>

      <section class="section">
        <h2>Aktuální stav</h2>
        <?php if ($logs): ?>
          <?php foreach ($logs as $log): ?>
            <div class="log-entry">
              <time datetime="<?= $log['created_at'] ?>"><?= date('d.m.Y H:i', strtotime($log['created_at'])) ?></time> – <strong><?= htmlspecialchars($log['action']) ?></strong>
              <p><?= nl2br(htmlspecialchars($log['note'])) ?></p>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>Zatím žádné kroky. Ozveme se brzy!</p>
        <?php endif; ?>
      </section>


      <?php
        // Příprava smlouvy (První kontakt)
        $firstContact = null;
        foreach ($logs as $log) {
            if ($log['action'] === 'První kontakt') {
                parse_str(strtr($log['note'], ["\n"=>"&", ": "=>"="]), $firstContact);
                break;
            }
        }
      ?>
      <?php if ($firstContact): ?>
        <section class="section info-bar">
          <p>Na základě prvního kontaktu jsme připravili smlouvu o smlouvě budoucí. <a href="client_future_contract.php" target="_blank" class="cta-button">Zobrazit smlouvu</a></p>
        </section>


      <?php endif; ?>
    <?php endif; ?>
  <?php endif; ?>
</section>

<?php include 'footer.php'; ?>


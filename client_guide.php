<?php
session_start();
require_once 'inc/connect.php'; // Pripojeni k databazi

// Kontrola prihlaseni a prav
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

// Nacteni udaju uzivatele
$email = $_SESSION['user']['email'];
$stmt = $pdo->prepare("SELECT * FROM clients WHERE email = ?");
$stmt->execute([$email]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

// Pristup povolen pokud je admin nebo klient podepsal prvni smlouvu
if ($_SESSION['user']['role'] !== 'admin' && (!$client || !$client['first_contract_signed'])) {
    echo "<h2 style='text-align:center;margin-top:50px;'>Nepovoleny pristup</h2>";
    exit;
}

include 'header.php';
include 'menu.php';
?>

<header class="main-header">
  <h1>Vaše cesta k nemovitosti ve Scalee</h1>
  <p>Užitečné rady a doporučení na základě reálných zkušeností</p>
</header>

<section class="section" style="max-width:800px;margin:2rem auto;font-family:sans-serif;">
  <h2>1. Finanční příprava</h2>
  <ul>
    <li>Mějte vlastní eurový účet.</li>
    <li>Vyjednávejte kurz při převodu vyšších částek.</li>
    <li>Nepoužívejte korunový účet kvůli nevýhodnému kurzu.</li>
  </ul>

  <h2>2. Mentální příprava</h2>
  <p>Obrňte se trpělivostí. Sledujte reálné zkušenosti, např. na YouTube kanále <a href="https://www.youtube.com/@MichaelandJustin" target="_blank">Michael and Justin</a>.</p>

  <h2>3. Sběr informací o lokalitě</h2>
  <ul>
    <li>Prozkoumejte lokalitu osobně nebo pomocí Google Maps / Earth.</li>
    <li>Vyhledejte nabídky mimo sezónu.</li>
    <li>Využijte naši <a href="https://myscalea.it/map/" target="_blank">interaktivní mapu Scalea</a> s ověřenými místy a otevirací dobou.</li>
  </ul>

  <h2>4. Komunikace s realitkami</h2>
  <ul>
    <li>Nepoužívejte kontaktní formuláře na Idealista.it atd. ➔ Frustrace je běžná (sám jsem si tím prošel).</li>
    <li>Upřednostňujte telefonický kontakt v italštině.</li>
  </ul>

  <h2>5. Výběr realitní kanceláře</h2>
  <ul>
    <li>Spolupracujte jen s námi doporučenými kancelářemi.</li>
    <li>Nepřipouštějte neznámé zprostředkovatele mezi vás a kancelář.</li>
    <li>Nikdy nepodepisujte nic, co není pravda nebo čemu nerozumíte.</li>
  </ul>

  <h2>6. Prohlídky nemovitostí</h2>
  <p>Pokud nemůžete přijet osobně, využijte našeho technika. Technik provede kontrolu, odhad nákladů na rekonstrukci a upozorní na vady.</p>

  <h2>7. Výběr typu vlastnictví: Prima casa vs. Seconda casa</h2>
  <ul>
    <li>Prima casa ➔ daňové zvýhodnění, ale povinnost přihlásit se k pobytu do 18 měsíců.</li>
    <li>Seconda casa ➔ volnější režim, ale vyšší daně.</li>
    <li>Nepodvádějte při přiznání účelu nákupu – vymstí se to.</li>
  </ul>

  <h2>8. Pronájem nemovitosti</h2>
  <p>Pokud plánujete krátkodobý pronájem, musíte získat CIN (Codice Identificativo Nazionale).</p>

  <h2>9. Dokončení koupě a poprodejí servis</h2>
  <ul>
    <li>Počítejte s daněmi, poplatky a notářem.</li>
    <li>Po koupi doporučujeme pojistit nemovitost a zajistit správce.</li>
  </ul>

  <h2>10. Shrnutí</h2>
  <p>Pečlivost, trpělivost a správné informace jsou klíčem k úěšěnému nákupu vaší nemovitosti ve Scalee.</p>

</section>

<?php include 'footer.php'; ?>

<style>
body {
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}
</style>

<script>
document.addEventListener('contextmenu', event => event.preventDefault());

document.addEventListener('keydown', function(event) {
  if (event.ctrlKey && (event.key === 'c' || event.key === 'u')) {
    event.preventDefault();
  }
  if (event.key === 'F12') {
    event.preventDefault();
  }
});
</script>

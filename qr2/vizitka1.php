<?php
require_once __DIR__.'/vendor/autoload.php';
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

header('Content-Type: text/html; charset=utf-8');

$u = $_GET['u'] ?? '1';

if($u == '1'){
    $fullName  = 'Jan Peřina';
    $firstName = 'Jan';
    $lastName  = 'Peřina';
    $email     = 'jan.perina@rightdone.eu';
    $mobile    = '+420774125898';
    $phone     = '+420910128749';
} elseif($u == '2'){
    $fullName  = 'Antonín Ečer';
    $firstName = 'Antonín';
    $lastName  = 'Ečer';
    $email     = 'antonin.ecer@rightdone.eu';
    $mobile    = '+420608193335';
    $phone     = '+420910128749';
} else {
    $u = '1'; // fallback
    $fullName  = 'Jan Peřina';
    $firstName = 'Jan';
    $lastName  = 'Peřina';
    $email     = 'jan.perina@rightdone.eu';
    $mobile    = '+420774125898';
    $phone     = '+420910128749';
}

// Společné údaje
$company     = 'Right Done s.r.o.';
$title       = 'Jednatel';
$orgEmail    = 'info@rightdone.eu';
$url         = 'https://rightdone.eu';
$adrStreet   = 'Újezd 58';
$adrCity     = 'okres Znojmo';
$adrRegion   = '';
$adrPost     = '67140';
$adrCountry  = 'Česká republika';
$note        = 'IČ: 23387858, DIČ: CZ23387858 (nejsme plátci DPH). Spisová značka: C 145692 vedená u Krajského soudu v Brně.';

$fnFormatted = "$fullName – $company";

$vcard = <<<VCF
BEGIN:VCARD
VERSION:3.0
N:$lastName;$firstName;;;
FN:$fnFormatted
ORG:$company
TITLE:$title
TEL;TYPE=work,voice:$phone
TEL;TYPE=cell,voice:$mobile
EMAIL;TYPE=work:$email
EMAIL;TYPE=info:$orgEmail
URL:$url
ADR;TYPE=work:;;$adrStreet;$adrCity;$adrRegion;$adrPost;$adrCountry
NOTE:$note
CATEGORIES:Jednatel,RightDone
END:VCARD
VCF;

if(isset($_GET['download']) && $_GET['download'] === 'vcf'){
    header('Content-Type: text/vcard; charset=utf-8');
    header("Content-Disposition: attachment; filename=\"{$lastName}.vcf\"");
    echo $vcard;
    exit;
}

$options = new QROptions([
    'outputType' => QRCode::OUTPUT_IMAGE_PNG,
    'eccLevel'   => QRCode::ECC_Q,
    'scale'      => 5,
    'imageBase64' => true,
]);

$qrImage = (new QRCode($options))->render($vcard);
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <title>QR Vizitka – <?= htmlspecialchars($fullName) ?></title>
</head>
<body style="text-align:center; font-family:sans-serif;">
    <h1>QR Vizitka – <?= htmlspecialchars($fullName) ?></h1>
    <img src="<?= $qrImage ?>" alt="QR vizitka"><br><br>

    <strong>Firma:</strong> <?= $company ?><br>
    <strong>Web:</strong> <a href="<?= $url ?>"><?= $url ?></a><br>
    <strong>E-mail:</strong> <a href="mailto:<?= $email ?>"><?= $email ?></a><br>
    <strong>Telefon:</strong> <a href="tel:<?= $phone ?>"><?= $phone ?></a><br>
    <strong>Mobil:</strong> <a href="tel:<?= $mobile ?>"><?= $mobile ?></a><br>
    <strong>Adresa:</strong> <?= "$adrStreet, $adrPost $adrCity, $adrCountry" ?><br><br>

    <a href="?u=<?= $u ?>&download=vcf">📥 Stáhnout vCard (.vcf)</a><br><br>

    <hr>
    <p><a href="?u=1">Vizitka: Jan Peřina</a> | <a href="?u=2">Vizitka: Antonín Ečer</a></p>
</body>
</html>
